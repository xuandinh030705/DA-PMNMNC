# -*- coding: utf-8 -*-
"""
Script to generate a comprehensive, academic-standard Word Report (.docx)
evaluating the 5 Core Project Criteria shown in the user's rubric image:
1. Xác định đối tượng người dùng, vai trò, dữ liệu cần quản lý, quy trình nghiệp vụ, phạm vi và ràng buộc
2. Thiết kế CSDL hợp lý, có ERD và quan hệ giữa các bảng
3. Sử dụng migration, model và relationship đúng chuẩn Laravel
4. Thiết kế phân quyền, vai trò người dùng nếu hệ thống cần
5. Tổ chức route, controller, view rõ ràng, dễ bảo trì
"""

import os
import docx
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import parse_xml, OxmlElement
from docx.oxml.ns import nsdecls, qn

def set_cell_background(cell, hex_color):
    tcPr = cell._element.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{hex_color}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=100, bottom=100, left=140, right=140):
    tcPr = cell._element.get_or_add_tcPr()
    tcMar = parse_xml(f'''
        <w:tcMar {nsdecls("w")}>
            <w:top w:w="{top}" w:type="dxa"/>
            <w:bottom w:w="{bottom}" w:type="dxa"/>
            <w:left w:w="{left}" w:type="dxa"/>
            <w:right w:w="{right}" w:type="dxa"/>
        </w:tcMar>
    ''')
    tcPr.append(tcMar)

def set_table_borders(table, color="CBD5E1", sz="4", val="single"):
    tblPr = table._element.xpath('w:tblPr')
    if tblPr:
        borders = parse_xml(f'''
            <w:tblBorders {nsdecls("w")}>
                <w:top w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>
                <w:bottom w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>
                <w:left w:val="none"/>
                <w:right w:val="none"/>
                <w:insideH w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>
                <w:insideV w:val="none"/>
            </w:tblBorders>
        ''')
        tblPr[0].append(borders)

def format_cell_text(cell, text, bold=False, italic=False, color_rgb=(30, 41, 59), font_size=9.5, align=WD_ALIGN_PARAGRAPH.LEFT):
    cell.text = ""
    p = cell.paragraphs[0]
    p.alignment = align
    p.paragraph_format.space_before = Pt(2)
    p.paragraph_format.space_after = Pt(2)
    p.paragraph_format.line_spacing = 1.15
    run = p.add_run(text)
    run.font.name = "Arial"
    run.font.size = Pt(font_size)
    run.font.bold = bold
    run.font.italic = italic
    run.font.color.rgb = RGBColor(*color_rgb)

def add_styled_table(doc, headers, data, col_widths=None, alignment=WD_TABLE_ALIGNMENT.CENTER):
    table = doc.add_table(rows=len(data) + 1, cols=len(headers))
    table.alignment = alignment
    set_table_borders(table)

    # Header Row
    hdr_cells = table.rows[0].cells
    for i, title in enumerate(headers):
        format_cell_text(hdr_cells[i], title, bold=True, color_rgb=(255, 255, 255), font_size=10, align=WD_ALIGN_PARAGRAPH.LEFT)
        set_cell_background(hdr_cells[i], "1E3A8A") # Navy Blue
        set_cell_margins(hdr_cells[i], top=120, bottom=120, left=140, right=140)
        hdr_cells[i].vertical_alignment = WD_ALIGN_VERTICAL.CENTER

    # Data Rows
    for r_idx, row in enumerate(data):
        row_cells = table.rows[r_idx + 1].cells
        bg_color = "F8FAFC" if r_idx % 2 == 1 else "FFFFFF"
        for c_idx, val in enumerate(row):
            align = WD_ALIGN_PARAGRAPH.CENTER if c_idx in [0, 2] and len(headers) >= 4 and len(str(val)) < 15 else WD_ALIGN_PARAGRAPH.LEFT
            format_cell_text(row_cells[c_idx], str(val), bold=(c_idx == 0 and len(headers) <= 4), font_size=9.5, align=align)
            set_cell_background(row_cells[c_idx], bg_color)
            set_cell_margins(row_cells[c_idx], top=80, bottom=80, left=140, right=140)
            row_cells[c_idx].vertical_alignment = WD_ALIGN_VERTICAL.CENTER

    # Set Column Widths if provided
    if col_widths:
        for row in table.rows:
            for idx, width in enumerate(col_widths):
                row.cells[idx].width = width

    doc.add_paragraph().paragraph_format.space_after = Pt(6)
    return table

def add_code_snippet(doc, code_str, title=None):
    if title:
        p_title = doc.add_paragraph()
        p_title.paragraph_format.space_before = Pt(4)
        p_title.paragraph_format.space_after = Pt(2)
        r_t = p_title.add_run(f"Mã nguồn minh họa: {title}")
        r_t.font.size = Pt(9.5)
        r_t.font.bold = True
        r_t.font.italic = True
        r_t.font.color.rgb = RGBColor(71, 85, 105)

    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(2)
    p.paragraph_format.space_after = Pt(8)
    p.paragraph_format.line_spacing = 1.05
    run = p.add_run(code_str)
    run.font.name = "Consolas"
    run.font.size = Pt(8.5)
    run.font.color.rgb = RGBColor(30, 41, 59)

    # Shading the paragraph background
    pPr = p._element.get_or_add_pPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="F1F5F9"/>')
    pPr.append(shd)

def generate_report(output_path):
    doc = Document()

    # Set 1-inch margins
    for s in doc.sections:
        s.top_margin = Inches(1.0)
        s.bottom_margin = Inches(1.0)
        s.left_margin = Inches(1.0)
        s.right_margin = Inches(1.0)

    # Normal Style
    normal_style = doc.styles['Normal']
    normal_style.font.name = 'Arial'
    normal_style.font.size = Pt(11)
    normal_style.font.color.rgb = RGBColor(51, 65, 85)

    # ==================== COVER PAGE ====================
    title_p = doc.add_paragraph()
    title_p.paragraph_format.space_before = Pt(30)
    title_p.paragraph_format.space_after = Pt(6)
    title_p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run_sub = title_p.add_run("BÁO CÁO NGHIỆM THU KỸ THUẬT PHẦN MỀM\n")
    run_sub.font.size = Pt(13)
    run_sub.font.bold = True
    run_sub.font.color.rgb = RGBColor(100, 116, 139)

    run_main = title_p.add_run("HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN\n(EVENT TICKET MANAGEMENT SYSTEM)")
    run_main.font.size = Pt(20)
    run_main.font.bold = True
    run_main.font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph().paragraph_format.space_after = Pt(10)

    doc_type_p = doc.add_paragraph()
    doc_type_p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run_doc_type = doc_type_p.add_run("BÁO CÁO ĐÁNH GIÁ THEO 6 TIÊU CHÍ MỤC 1 & MỤC 2\n(CHUYÊN ĐỀ PHÁT TRIỂN ỨNG DỤNG WEB LARAVEL - PLO1 & PLO2)")
    run_doc_type.font.size = Pt(13)
    run_doc_type.font.bold = True
    run_doc_type.font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph().paragraph_format.space_after = Pt(24)

    # Metadata Box Table
    meta_table = doc.add_table(rows=6, cols=2)
    meta_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(meta_table, color="CBD5E1", sz="6")
    meta_data = [
        ("Tên dự án:", "Hệ Thống Quản Lý Bán Vé Sự Kiện Trực Tuyến"),
        ("Nhóm thực hiện:", "Nhóm 8 - Lớp 68PM2 (Hồ Đăng Quang, Tống Xuân Định, Nguyễn Hữu Quý)"),
        ("Công nghệ cốt lõi:", "PHP 8.3+ (8.5.9) / Laravel 13 / Blade / Bootstrap 5.3 CDN / Endroid QR / DomPDF"),
        ("Cơ sở dữ liệu:", "MySQL 8.0+ Community Server (InnoDB - Mặc định) / SQLite dự phòng"),
        ("Kiểm thử chất lượng:", "103/103 Unit & Feature Tests Passed (411 Assertions, 0 Failures) + Pint Passed"),
        ("Tài liệu đối chiếu:", "SRS 01_phan_tich_yeu_cau.md, 02_thiet_ke_co_so_du_lieu.md, tailieu/1.jpg"),
    ]
    for idx, (label, val) in enumerate(meta_data):
        row_cells = meta_table.rows[idx].cells
        format_cell_text(row_cells[0], label, bold=True, color_rgb=(30, 58, 138), font_size=10)
        format_cell_text(row_cells[1], val, bold=False, color_rgb=(15, 23, 42), font_size=10)
        set_cell_background(row_cells[0], "F1F5F9")
        set_cell_background(row_cells[1], "FFFFFF")
        set_cell_margins(row_cells[0], top=80, bottom=80, left=140, right=140)
        set_cell_margins(row_cells[1], top=80, bottom=80, left=140, right=140)
        row_cells[0].width = Inches(2.3)
        row_cells[1].width = Inches(4.2)

    doc.add_page_break()

    # ==================== SUMMARY OF THE 6 CRITERIA ====================
    h1 = doc.add_heading("TỔNG QUAN 6 TIÊU CHÍ MỤC 1 & MỤC 2 (PLO1, PLO2)", level=1)
    h1.paragraph_format.space_before = Pt(12)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph(
        "Báo cáo này được cấu trúc bám sát đầy đủ 6 tiêu chí của Mục 1 (Phân tích yêu cầu - PLO1) và Mục 2 (Thiết kế hệ thống - PLO2) trong rubric `tailieu/1.jpg`, "
        "minh chứng bằng sơ đồ kết xuất thực tế, mô hình dữ liệu và mã nguồn trong dự án:"
    )

    overview_headers = ["STT", "Nội Dung Tiêu Chí Đánh Giá", "Mức Độ Hoàn Thành", "Mã Nguồn / Tài Liệu Minh Chứng"]
    overview_data = [
        [
            "Tiêu chí 1",
            "Xác định đối tượng người dùng, vai trò, dữ liệu cần quản lý, quy trình nghiệp vụ, phạm vi và ràng buộc",
            "100% Hoàn thành",
            "docs/01_phan_tich_yeu_cau.md, docs/SPEC.md, UserRole Enum, BR-01..05"
        ],
        [
            "Tiêu chí 2",
            "Thiết kế CSDL hợp lý, có ERD và quan hệ giữa các bảng",
            "100% Hoàn thành",
            "docs/diagrams/05_erd.puml & DRAWIO_UML_PROMPTS.md, docs/02_thiet_ke_co_so_du_lieu.md"
        ],
        [
            "Tiêu chí 3",
            "Sử dụng migration, model và relationship đúng chuẩn Laravel",
            "100% Hoàn thành",
            "database/migrations/* (12 files: 7 nghiệp vụ + 2 nâng cấp), 8 Models, 4 Enums, lockForUpdate"
        ],
        [
            "Tiêu chí 4",
            "Thiết kế phân quyền, vai trò người dùng nếu hệ thống cần",
            "100% Hoàn thành",
            "CheckRole & CheckActive Middleware, EventPolicy, TicketTypePolicy, OrderPolicy (cả tải PDF)"
        ],
        [
            "Tiêu chí 5",
            "Tổ chức route, controller, view rõ ràng, dễ bảo trì",
            "100% Hoàn thành",
            "4 tệp route (58 app routes), 4 Services, OrderPaidMail, 11 FormRequests, Blade Zero-Node"
        ],
        [
            "Tiêu chí 6",
            "Tài liệu/sơ đồ thiết kế: use case, ERD, sơ đồ chức năng",
            "Đầy đủ mã nguồn UML",
            "8 file PlantUML + 6 sequence + 8 PNG & DRAWIO_UML_PROMPTS.md (chờ xuất .drawio)"
        ],
    ]
    add_styled_table(doc, overview_headers, overview_data, col_widths=[Inches(1.0), Inches(2.8), Inches(1.2), Inches(1.8)])

    # ==================== TIÊU CHÍ 1 ====================
    h1 = doc.add_heading("TIÊU CHÍ 1: XÁC ĐỊNH ĐỐI TƯỢNG NGƯỜI DÙNG, VAI TRÒ, DỮ LIỆU CẦN QUẢN LÝ, QUY TRÌNH NGHIỆP VỤ, PHẠM VI VÀ RÀNG BUỘC", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    h2 = doc.add_heading("1.1. Đối tượng Người dùng & Phân vai trò (User Roles)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Hệ thống xác định rõ 4 đối tượng tác tử tham gia vào quy trình vận hành, được định nghĩa chặt chẽ bằng PHP Enum "
        "tại `app/Enums/UserRole.php`:\n"
        "1. Khách vãng lai (Guest): Duyệt danh sách sự kiện đang mở bán, tìm kiếm từ khóa, lọc theo danh mục/thành phố/ngày, xem chi tiết sự kiện kèm khu vực (`zone`) & quyền lợi (`description`).\n"
        "2. Khách hàng (Customer - vai trò 'customer'): Đặt vé giữ chỗ 15 phút, thanh toán giả lập, nhận email xác nhận (`OrderPaidMail`) đính kèm PDF, xem lịch sử đơn hàng, nhận vé điện tử kèm mã QR ký số HMAC-SHA256 và tải vé PDF khổ A5.\n"
        "3. Ban tổ chức (Organizer - vai trò 'organizer'): Dashboard thống kê doanh thu, quản lý vòng đời sự kiện (Tạo nháp -> Thêm vé kèm `zone` & `description` -> Gửi duyệt), theo dõi đơn hàng và soát vé tại cổng bằng Camera quét QR (`html5-qrcode` offline) hoặc nhập tay.\n"
        "4. Quản trị viên (Admin - vai trò 'admin'): Dashboard toàn sàn, quản lý danh mục (Categories) & địa điểm (Venues) ràng buộc `RESTRICT`, quản lý người dùng, duyệt/từ chối và hủy sự kiện cascade (tự hủy đơn pending hoàn vé & hủy vé valid)."
    )

    h2 = doc.add_heading("1.2. Dữ liệu cần Quản lý (Core Business Entities)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    entities_data = [
        ("Người dùng (users)", "Tên, email duy nhất, mật khẩu mã hóa Bcrypt, số điện thoại, vai trò (UserRole Enum), trạng thái kích hoạt (is_active)."),
        ("Danh mục sự kiện (categories)", "Tên danh mục duy nhất, đường dẫn tĩnh thân thiện SEO (slug duy nhất), mô tả phạm vi chủ đề."),
        ("Địa điểm tổ chức (venues)", "Tên địa điểm, địa chỉ cụ thể, tỉnh/thành phố trực thuộc, sức chứa tối đa phục vụ kiểm soát quy mô."),
        ("Sự kiện (events)", "Tiêu đề, slug URL duy nhất, mô tả, ảnh banner, thời điểm bắt đầu/kết thúc, trạng thái (EventStatus Enum), soft delete."),
        ("Loại vé (ticket_types)", "Tên hạng vé, khu vực (`zone`), mô tả quyền lợi (`description`), đơn giá VNĐ, tổng số lượng, số đã bán/giữ chỗ (`sold`), thời gian mở/đóng bán, giới hạn mỗi đơn."),
        ("Đơn hàng (orders)", "Mã đơn duy nhất (ORD-XXXXXXXX), thông tin người nhận vé, tổng tiền, phương thức thanh toán, trạng thái (OrderStatus Enum), thời hạn giữ chỗ 15 phút (expires_at)."),
        ("Chi tiết đơn hàng (order_items)", "Khóa ngoại đơn hàng và loại vé, số lượng đặt, đơn giá tại thời điểm đặt (đóng băng giá), thành tiền."),
        ("Vé điện tử (tickets)", "Mã vé duy nhất (TKT-XXXXXXXXXX), payload JSON `qr_payload`, chữ ký số HMAC-SHA256 `qr_signature`, trạng thái (TicketStatus Enum), thời điểm check-in và nhân viên soát vé (`checked_in_by`)."),
    ]
    for name, desc in entities_data:
        p = doc.add_paragraph()
        p.paragraph_format.space_after = Pt(3)
        r = p.add_run(f"• {name}: ")
        r.bold = True
        r.font.color.rgb = RGBColor(15, 23, 42)
        p.add_run(desc)

    h2 = doc.add_heading("1.3. Các Quy trình Nghiệp vụ Cốt lõi (Business Workflows)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Dự án triển khai hoàn chỉnh 3 quy trình nghiệp vụ trọng yếu:\n"
        "• Quy trình 1 - Tạo, Phê duyệt & Hủy Cascade Sự kiện: Ban tổ chức tạo bản nháp (draft) -> Thêm các loại vé (kèm `zone`, `description`) -> Gửi duyệt (pending) -> Admin duyệt (published), từ chối (draft) hoặc hủy (cancelled - cascade hủy đơn pending hoàn vé & hủy vé valid).\n"
        "• Quy trình 2 - Đặt vé, Giữ chỗ, Thanh toán, QR, PDF & Email: Khách chọn vé -> `DB::transaction()` + `lockForUpdate()` trên `ticket_types` -> Sinh mã đơn `ORD-XXXXXXXX`, giữ chỗ 15 phút -> Thanh toán -> Đơn chuyển `paid`, sinh vé `TKT-XXXXXXXXXX` kèm `qr_payload` JSON và chữ ký HMAC-SHA256 -> Gửi email `OrderPaidMail` đính kèm vé PDF khổ A5 tạo trong RAM -> Khách xem QR và tải PDF qua `/my-tickets/{ticket}/pdf`.\n"
        "• Quy trình 3 - Soát vé (Check-in) 6 bước tại cổng: Ban tổ chức quét Camera QR (`html5-qrcode.min.js` offline) hoặc nhập tay mã vé tại `/organizer/checkin` -> `TicketCheckinService` khóa dòng vé (`lockForUpdate()`) và kiểm tra 6 tầng: (1) tồn tại mã vé, (2) chữ ký HMAC-SHA256 hợp lệ qua `hash_equals()` khi quét QR, (3) đơn đã thanh toán `paid`, (4) thuộc sự kiện của Organizer, (5) vé chưa `cancelled`, (6) vé chưa `used` -> Cập nhật `used`, `checked_in_at` và lưu nhân viên soát vé `checked_in_by`."
    )

    h2 = doc.add_heading("1.4. Phạm vi & Ràng buộc Nghiệp vụ (Scope & Business Rules)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "• Phạm vi (Scope): Hoàn chỉnh nghiệp vụ quản trị, kiểm soát tồn kho đồng thời, phát hành vé QR HMAC-SHA256, xuất vé PDF A5 động trong RAM, gửi email Mailpit/Log và quét camera offline; thay thế cổng ngân hàng thật bằng Mock Payment Gateway để demo an toàn.\n"
        "• Các ràng buộc cốt lõi (Business Rules BR-01..05):\n"
        "  - BR-01 (Định danh & Chữ ký): Email, slug, mã đơn `ORD-` và mã vé `TKT-` duy nhất 100%; mã QR ký HMAC-SHA256 bằng `APP_KEY`.\n"
        "  - BR-02 (Thời gian): `end_time > start_time > now()`, mở bán trước đóng bán, giữ chỗ cố định 15 phút.\n"
        "  - BR-03 (Toàn vẹn xóa & hủy): Chặn xóa danh mục/địa điểm có sự kiện (`RESTRICT`); sự kiện dùng `SoftDeletes`; Admin hủy sự kiện phải cascade xử lý đơn `pending` & vé `valid`; Admin không tự khóa/hạ quyền chính mình.\n"
        "  - BR-04 (Tiền tệ & Ngôn ngữ): VNĐ phân cách dấu chấm, múi giờ `Asia/Ho_Chi_Minh`, thông báo tiếng Việt UTF-8.\n"
        "  - BR-05 (Check-in & Zone): Chống check-in trùng bằng `lockForUpdate()`, ghi nhận `checked_in_by`; `zone` <= 100 ký tự, `description` <= 1000 ký tự."
    )

    # ==================== TIÊU CHÍ 2 ====================
    h1 = doc.add_heading("TIÊU CHÍ 2: THIẾT KẾ CSDL HỢP LÝ, CÓ ERD VÀ QUAN HỆ GIỮA CÁC BẢNG", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph(
        "Cơ sở dữ liệu của dự án được thiết kế đạt chuẩn hóa 3NF (Third Normal Form), "
        "đảm bảo không trùng lặp thông tin và bảo vệ toàn vẹn tham chiếu thông qua các khóa ngoại (Foreign Keys)."
    )

    h2 = doc.add_heading("2.1. Sơ đồ Quan hệ Thực thể (Entity Relationship Diagram - ERD)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Mô hình dữ liệu bao gồm 8 thực thể nghiệp vụ cốt lõi, được lưu trữ mã nguồn sơ đồ tại `docs/diagrams/05_erd.puml`:"
    )

    erd_summary_headers = ["Bảng Gốc", "Quan Hệ", "Bảng Tham Chiếu", "Khóa Ngoại (Foreign Key)", "Ràng Buộc Xóa (On Delete)"]
    erd_summary_data = [
        ["users", "1 - N", "events", "events.organizer_id -> users.id", "CASCADE"],
        ["categories", "1 - N", "events", "events.category_id -> categories.id", "RESTRICT (Chặn xóa danh mục có sự kiện)"],
        ["venues", "1 - N", "events", "events.venue_id -> venues.id", "RESTRICT (Chặn xóa địa điểm có sự kiện)"],
        ["events", "1 - N", "ticket_types", "ticket_types.event_id -> events.id", "CASCADE (Xóa sự kiện xóa luôn hạng vé)"],
        ["users", "1 - N", "orders", "orders.user_id -> users.id", "CASCADE"],
        ["orders", "1 - N", "order_items", "order_items.order_id -> orders.id", "CASCADE (Xóa đơn xóa dòng sản phẩm)"],
        ["ticket_types", "1 - N", "order_items", "order_items.ticket_type_id -> ticket_types.id", "CASCADE"],
        ["order_items", "1 - N", "tickets", "tickets.order_item_id -> order_items.id", "CASCADE (Xóa đơn xóa vé phát hành)"],
        ["users", "1 - N", "tickets", "tickets.checked_in_by -> users.id", "SET NULL (Lưu nhân viên soát vé)"],
    ]
    add_styled_table(doc, erd_summary_headers, erd_summary_data, col_widths=[Inches(1.2), Inches(0.8), Inches(1.2), Inches(2.0), Inches(1.6)])

    h2 = doc.add_heading("2.2. Chiến lược Đánh Chỉ mục (Indexing Strategy) Tối ưu Hiệu năng", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Hệ thống thiết lập các chỉ mục chiến lược trong các file Migration giúp giảm thiểu tối đa thời gian thực thi truy vấn:\n"
        "• Unique Indexes: `users(email)`, `categories(slug)`, `events(slug)`, `orders(order_code)`, `tickets(ticket_code)` đảm bảo tra cứu O(1).\n"
        "• Composite Index: `events(status, start_time)` tối ưu hóa câu truy vấn lọc sự kiện mở bán trên trang chủ theo thời gian bắt đầu.\n"
        "• Composite Index: `orders(user_id, status)` tối ưu trang danh sách đơn hàng cá nhân của khách hàng.\n"
        "• Foreign Key Indexes: Toàn bộ các cột `organizer_id`, `category_id`, `venue_id`, `event_id`, `order_id`, `ticket_type_id` đều được đánh index phục vụ JOIN và Eager Loading."
    )

    # ==================== TIÊU CHÍ 3 ====================
    h1 = doc.add_heading("TIÊU CHÍ 3: SỬ DỤNG MIGRATION, MODEL VÀ RELATIONSHIP ĐÚNG CHUẨN LARAVEL", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    h2 = doc.add_heading("3.1. Thiết kế Migration Chuẩn mực", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Hệ thống có 12 tệp di chuyển cơ sở dữ liệu trong `database/migrations/` (3 core Laravel, 7 bảng nghiệp vụ và 2 migration nâng cấp thêm cột QR/checked_in_by/zone/description). "
        "Tất cả đều tuân thủ cú pháp Laravel 11/12/13 với anonymous migration class và ràng buộc khóa ngoại an toàn:"
    )

    mig_code = (
        "// Trích đoạn Migration tạo bảng events (2026_10_25_000003_create_events_table.php):\n"
        "Schema::create('events', function (Blueprint $table) {\n"
        "    $table->id();\n"
        "    $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');\n"
        "    $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');\n"
        "    $table->foreignId('venue_id')->constrained('venues')->onDelete('restrict');\n"
        "    $table->string('title');\n"
        "    $table->string('slug')->unique();\n"
        "    $table->text('description');\n"
        "    $table->string('banner_path')->nullable();\n"
        "    $table->dateTime('start_time');\n"
        "    $table->dateTime('end_time');\n"
        "    $table->string('status')->default('draft');\n"
        "    $table->timestamps();\n"
        "    $table->softDeletes();\n"
        "    $table->index(['status', 'start_time']);\n"
        "});"
    )
    add_code_snippet(doc, mig_code, "database/migrations/2026_10_25_000003_create_events_table.php")

    h2 = doc.add_heading("3.2. Eloquent Models & Quan hệ 2 Chiều (Relationships)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Cả 8 Model nghiệp vụ trong thư mục `app/Models/` đều khai báo `$fillable`, `casts()` (đồng bộ 4 Backed Enums: "
        "`UserRole`, `EventStatus`, `OrderStatus`, `TicketStatus`) và quan hệ hai chiều:"
    )

    models_data = [
        ("Event.php", "belongsTo(User, 'organizer_id'), belongsTo(Category), belongsTo(Venue), hasMany(TicketType), cast EventStatus, scopes: published(), upcoming(), notEnded(), search()"),
        ("TicketType.php", "belongsTo(Event), hasMany(OrderItem), hỗ trợ zone & description, Accessor: remaining, isOnSale()"),
        ("Order.php", "belongsTo(User), hasMany(OrderItem), hasManyThrough(Ticket, OrderItem), cast OrderStatus, helpers: isPending(), isPaid(), isCancelled(), isExpired()"),
        ("OrderItem.php", "belongsTo(Order), belongsTo(TicketType), hasMany(Ticket)"),
        ("Ticket.php", "belongsTo(OrderItem), belongsTo(User, 'checked_in_by'), cast TicketStatus, QR payload & HMAC helpers (hasValidQrSignature)"),
        ("User.php", "hasMany(Event, 'organizer_id'), hasMany(Order, 'user_id'), cast UserRole, helpers: isAdmin(), isOrganizer(), isCustomer(), isActive()"),
        ("Category.php", "hasMany(Event), generateUniqueSlug()"),
        ("Venue.php", "hasMany(Event)"),
    ]
    for m_name, m_desc in models_data:
        p = doc.add_paragraph()
        p.paragraph_format.space_after = Pt(3)
        r = p.add_run(f"• Model `{m_name}`: ")
        r.bold = True
        r.font.color.rgb = RGBColor(15, 23, 42)
        p.add_run(m_desc)

    model_code = (
        "// Trích đoạn khai báo quan hệ và casts() trong app/Models/Event.php:\n"
        "protected function casts(): array {\n"
        "    return [\n"
        "        'start_time' => 'datetime',\n"
        "        'end_time' => 'datetime',\n"
        "    ];\n"
        "}\n\n"
        "public function organizer(): BelongsTo {\n"
        "    return $this->belongsTo(User::class, 'organizer_id');\n"
        "}\n"
        "public function ticketTypes(): HasMany {\n"
        "    return $this->hasMany(TicketType::class);\n"
        "}"
    )
    add_code_snippet(doc, model_code, "app/Models/Event.php")

    h2 = doc.add_heading("3.3. Tối ưu Truy vấn Chống N+1 Query & Khóa Bi Quan (Concurrency)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "• Triệt tiêu lỗi N+1 Query: Danh sách sự kiện và đơn hàng luôn sử dụng Eager Loading `with(['category', 'venue'])`, `withCount('ticketTypes')`, `withMin()` và `withSum()`. "
        "Tại chi tiết đơn hàng (`orders.show`), hệ thống eager load đầy đủ `tickets.orderItem.ticketType.event.venue` và `tickets.checkedInBy` (được kiểm chứng tự động bằng test hồi quy).\n"
        "• Khóa bi quan (`lockForUpdate`): Được áp dụng trong `DB::transaction()` tại `OrderService`, `TicketCheckinService` và `Admin\\EventController::cancel()` trên MySQL 8 InnoDB để chống overselling, chống check-in trùng và đảm bảo hủy sự kiện cascade nhất quán."
    )

    # ==================== TIÊU CHÍ 4 ====================
    h1 = doc.add_heading("TIÊU CHÍ 4: THIẾT KẾ PHÂN QUYỀN, VAI TRÒ NGƯỜI DÙNG NẾU HỆ THỐNG CẦN", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph(
        "Dự án triển khai mô hình Bảo mật và Phân quyền Đa tầng (Multi-tier Authorization Framework), "
        "kết hợp chặt chẽ giữa Middleware (tầng định tuyến), Policy (tầng đối tượng dữ liệu) và Blade Directives (tầng giao diện)."
    )

    h2 = doc.add_heading("4.1. Hệ thống Middleware Bảo vệ Tầng Route", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Hệ thống định nghĩa hai Middleware kiểm soát an ninh quan trọng trong `app/Http/Middleware/`:\n"
        "1. `CheckRole`: Hỗ trợ kiểm tra linh hoạt một hoặc nhiều vai trò cùng lúc (ví dụ: `role:admin` hoặc `role:organizer,admin`). "
        "Nếu người dùng chưa đăng nhập hoặc không đủ thẩm quyền, hệ thống chặn lại và trả về lỗi `403 Forbidden`.\n"
        "2. `CheckActive`: Kiểm tra trạng thái hoạt động của tài khoản (`is_active`). Nếu tài khoản bị Quản trị viên khóa, "
        "Middleware sẽ tự động ngắt phiên (Logout), hủy session và chuyển hướng về trang đăng nhập kèm thông báo tiếng Việt: 'Tài khoản của bạn đã bị khóa bởi quản trị viên.'"
    )

    mw_code = (
        "// Đăng ký alias Middleware trong bootstrap/app.php:\n"
        "$middleware->alias([\n"
        "    'role' => \\App\\Http\\Middleware\\CheckRole::class,\n"
        "    'active' => \\App\\Http\\Middleware\\CheckActive::class,\n"
        "]);\n\n"
        "// Áp dụng bảo vệ phân hệ Quản trị viên (routes/admin.php):\n"
        "Route::prefix('admin')->name('admin.')->middleware(['auth', 'active', 'role:admin'])->group(...);\n\n"
        "// Áp dụng bảo vệ phân hệ Ban tổ chức (routes/organizer.php):\n"
        "Route::prefix('organizer')->name('organizer.')->middleware(['auth', 'active', 'role:organizer'])->group(...);"
    )
    add_code_snippet(doc, mw_code, "bootstrap/app.php & routes/admin.php")

    h2 = doc.add_heading("4.2. Hệ thống Policies Bảo vệ Dữ liệu Cấp Đối tượng (Chống Tấn công IDOR)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Để ngăn chặn triệt để lỗ hổng Insecure Direct Object References (IDOR - người dùng thay đổi ID trên URL để xem/sửa dữ liệu của người khác), "
        "hệ thống áp dụng 3 Laravel Policies trong `app/Policies/`:\n"
        "• `EventPolicy`: Organizer chỉ có quyền xem, cập nhật, xóa hoặc gửi duyệt các sự kiện do chính mình tạo ra (`$user->id === $event->organizer_id`); Admin có quyền phê duyệt, từ chối và hủy sự kiện.\n"
        "• `TicketTypePolicy`: Chỉ cho phép tạo/sửa/xóa loại vé khi sự kiện thuộc sở hữu của người dùng đang đăng nhập (và ở trạng thái `draft`).\n"
        "• `OrderPolicy`: Bảo vệ cả màn hình đơn hàng và route tải vé PDF `/my-tickets/{ticket}/pdf` (chỉ chủ đơn, Organizer của sự kiện hoặc Admin được phép truy cập; người khác nhận HTTP 403)."
    )

    policy_code = (
        "// Trích đoạn kiểm tra quyền sở hữu trong app/Policies/EventPolicy.php:\n"
        "public function update(User $user, Event $event): bool {\n"
        "    return $user->isAdmin() || ($user->isOrganizer() && $user->id === $event->organizer_id && $event->isDraft());\n"
        "}\n"
        "public function delete(User $user, Event $event): bool {\n"
        "    return $user->isAdmin() || ($user->isOrganizer() && $user->id === $event->organizer_id && $event->isDraft());\n"
        "}"
    )
    add_code_snippet(doc, policy_code, "app/Policies/EventPolicy.php")

    h2 = doc.add_heading("4.3. Cơ chế Tự vệ Quản trị (Admin Self-Protection)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Trong `App\\Http\\Controllers\\Admin\\UserController`, hệ thống thiết lập cơ chế tự vệ cấp cao: "
        "Quản trị viên đang đăng nhập tuyệt đối không thể tự khóa tài khoản của chính mình (`$user->id === auth()->id()`) "
        "hoặc tự hạ quyền vai trò xuống Customer/Organizer, nhằm ngăn ngừa nguy cơ hệ thống bị tê liệt quyền quản trị."
    )

    # ==================== TIÊU CHÍ 5 ====================
    h1 = doc.add_heading("TIÊU CHÍ 5: TỔ CHỨC ROUTE, CONTROLLER, VIEW RÕ RÀNG, DỄ BẢO TRÌ", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    h2 = doc.add_heading("5.1. Tổ chức Tuyến đường (Routes) Module hóa & 58 Application Routes", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Thay vì dồn toàn bộ mã vào một tệp `web.php` duy nhất gây xung đột mã nguồn và khó bảo trì, "
        "hệ thống tách biệt hoàn toàn thành 4 tệp định tuyến chuyên trách trong thư mục `routes/`:\n"
        "1. `routes/auth.php`: Định tuyến Xác thực (Đăng ký, đăng nhập throttle 5 lần/phút, đăng xuất, cập nhật hồ sơ cá nhân).\n"
        "2. `routes/storefront.php`: Định tuyến Cửa hàng & Khách hàng (Trang chủ, chi tiết sự kiện, đặt vé checkout, thanh toán giả lập, lịch sử đơn, danh sách vé QR và tải vé PDF `/my-tickets/{ticket}/pdf`).\n"
        "3. `routes/organizer.php`: Định tuyến Ban tổ chức có prefix `/organizer` (Dashboard, CRUD sự kiện, quản lý hạng vé, theo dõi đơn hàng, cổng soát vé Camera QR Check-in).\n"
        "4. `routes/admin.php`: Định tuyến Quản trị có prefix `/admin` (Dashboard tài chính, quản lý danh mục, quản lý địa điểm, quản lý người dùng, duyệt/từ chối/hủy sự kiện, xem tất cả đơn hàng).\n"
        "Tổng cộng 58 application routes chuẩn RESTful, có tên định danh (`->name()`) rõ ràng, kết nối tự động trong `routes/web.php` (cùng scheduler `orders:expire-pending` trong `routes/console.php`)."
    )

    h2 = doc.add_heading("5.2. Tổ chức Controllers, Service Pattern & Mailable", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "• Phân chia Controller theo Namespace chuyên biệt: `App\\Http\\Controllers\\Auth`, `Storefront`, `Organizer`, `Admin`.\n"
        "• Tách bạch Nghiệp vụ (Skinny Controller, Fat Service) với 4 Service classes trong `app/Services/` và 1 Mailable:\n"
        "  - `OrderService.php`: Tạo đơn giữ chỗ trong `DB::transaction()` + `lockForUpdate()`, xử lý thanh toán sinh vé kèm QR HMAC, hủy đơn và hết hạn tự động.\n"
        "  - `QrTicketService.php`: Sinh mã QR PNG/SVG Data URI qua `endroid/qr-code`, giải mã payload JSON và xác thực chữ ký HMAC-SHA256.\n"
        "  - `TicketPdfService.php`: Kết xuất vé PDF khổ A5 trong RAM qua `barryvdh/laravel-dompdf` (không ghi file lên ổ đĩa).\n"
        "  - `TicketCheckinService.php`: Đảm nhiệm 6 bước xác thực vé tại cổng (tồn tại, HMAC-SHA256, paid, ownership, chưa hủy, chưa dùng) và lưu `checked_in_by`.\n"
        "  - `OrderPaidMail.php`: Gửi email xác nhận thanh toán đính kèm toàn bộ vé PDF sinh động trong RAM.\n"
        "• 11 Form Requests độc lập trong `app/Http/Requests/`: Validation tiếng Việt đầy đủ `rules()`, `attributes()`, `messages()`."
    )

    h2 = doc.add_heading("5.3. Tổ chức Giao diện (Views & Blade Templates)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Hệ thống View trong `resources/views/` được module hóa và tái sử dụng tối đa:\n"
        "• Hệ thống 2 Master Layout chuẩn mực:\n"
        "  - `layouts/app.blade.php`: Layout giao diện Cửa hàng (Storefront) dành cho khách hàng với Navbar, footer và liên kết tài khoản.\n"
        "  - `layouts/panel.blade.php`: Layout Bảng điều khiển quản trị (Dashboard) chuyên dụng cho Admin và Organizer với thanh Sidebar thu gọn, menu phân cấp theo vai trò, thống kê và thông tin tài khoản.\n"
        "• Các Partials dùng chung: `resources/views/partials/alerts.blade.php` (hiển thị thông báo flash message thành công/thất bại), `form-errors.blade.php` (hiển thị danh sách lỗi dữ liệu đầu vào).\n"
        "• Các trang lỗi tùy biến chuẩn hóa: `resources/views/errors/` bao gồm 403 (Không có quyền truy cập), 404 (Không tìm thấy trang), 419 (Hết hạn phiên CSRF), 500 (Lỗi máy chủ nội bộ).\n"
        "• Công nghệ UI: Toàn bộ giao diện sử dụng Bootstrap 5.3 CDN và Bootstrap Icons CDN, hoàn toàn không phụ thuộc vào `npm` hay Node.js (Zero-node build)."
    )

    # ==================== TIÊU CHÍ 6: SƠ ĐỒ THIẾT KẾ ====================
    h1 = doc.add_heading("TIÊU CHÍ 6: TÀI LIỆU / SƠ ĐỒ THIẾT KẾ (USE CASE, ERD, SƠ ĐỒ CHỨC NĂNG)", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph(
        "Hệ thống có 8 sơ đồ PlantUML chính + 6 sơ đồ tuần tự bổ sung trong `docs/diagrams/`, 8 ảnh PNG trong `docs/diagrams/images/`, "
        "cùng bộ Prompt AI & mã PlantUML cập nhật đầy đủ 5 cột DB mới + luồng QR/PDF/Email/Camera tại `docs/diagrams/DRAWIO_UML_PROMPTS.md` để xuất sang `.drawio`:"
    )

    base_dir = os.path.dirname(os.path.abspath(__file__))
    for img_file, caption in [
        ("01_usecase_tong_quan.png", "Hình 1: Sơ đồ Use Case Tổng quan Hệ thống"),
        ("05_erd.png", "Hình 2: Sơ đồ Quan hệ Thực thể (ERD)"),
        ("06_so_do_chuc_nang.png", "Hình 3: Sơ đồ Phân rã Chức năng (WBS)"),
    ]:
        img_path = os.path.join(base_dir, "docs", "diagrams", "images", img_file)
        if os.path.exists(img_path):
            p_i = doc.add_paragraph()
            p_i.alignment = WD_ALIGN_PARAGRAPH.CENTER
            p_i.add_run().add_picture(img_path, width=Inches(6.0))
            p_c = doc.add_paragraph()
            p_c.alignment = WD_ALIGN_PARAGRAPH.CENTER
            r_c = p_c.add_run(caption)
            r_c.italic = True
            r_c.font.size = Pt(9.5)
            r_c.font.color.rgb = RGBColor(100, 116, 139)

    # ==================== KẾT LUẬN ====================
    h1 = doc.add_heading("KẾT LUẬN & ĐÁNH GIÁ CHUNG", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph(
        "Dự án 'Hệ Thống Quản Lý Bán Vé Sự Kiện' đã đáp ứng trọn vẹn cả 6 tiêu chí của Mục 1 & Mục 2:\n"
        "1. Xác định nghiệp vụ sắc nét: Phân vai 4 tác tử rõ ràng, dữ liệu quản lý đầy đủ, 3 quy trình trọng yếu thông suốt (bao gồm QR HMAC, PDF A5, Email, Camera Check-in 6 bước, Admin hủy cascade).\n"
        "2. Thiết kế CSDL mẫu mực: Đạt chuẩn 3NF trên MySQL 8.0 (InnoDB), ERD quan hệ chặt chẽ, bảo vệ bằng CASCADE/RESTRICT/SET NULL và đánh chỉ mục tối ưu.\n"
        "3. Triển khai Laravel chuẩn mực: 12 migrations, 8 models đồng bộ 4 Enums, triệt tiêu N+1 query và áp dụng Pessimistic Locking (`lockForUpdate()`).\n"
        "4. Phân quyền đa tầng bảo mật: Kết hợp Middleware `CheckRole`/`CheckActive`, 3 Policies chống IDOR (bao gồm route PDF) và chữ ký số HMAC-SHA256.\n"
        "5. Kiến trúc MVC gọn gàng: 4 tệp route tách biệt (58 app routes), 4 Services, Mailable `OrderPaidMail` và 11 Form Requests tiếng Việt.\n"
        "6. Bộ sơ đồ đầy đủ: PlantUML/PNG và bộ Prompt UML cho draw.io (`DRAWIO_UML_PROMPTS.md`).\n\n"
        "Hệ thống đã vượt qua kiểm thử tự động toàn diện với 103/103 Unit & Feature Tests passed (411 assertions, 0 failures) và Laravel Pint sạch 100%."
    )

    # Save document
    doc.save(output_path)
    print(f"Report document successfully created at: {output_path}")

if __name__ == "__main__":
    docs_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "docs")
    generate_report(os.path.join(docs_dir, "03_bao_cao_tieu_chi_do_an.docx"))
