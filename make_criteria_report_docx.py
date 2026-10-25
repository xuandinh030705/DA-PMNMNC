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
    run_doc_type = doc_type_p.add_run("BÁO CÁO ĐÁNH GIÁ THEO 5 TIÊU CHÍ ĐỒ ÁN CỐT LÕI\n(CHUYÊN ĐỀ PHÁT TRIỂN ỨNG DỤNG WEB LARAVEL)")
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
        ("Công nghệ cốt lõi:", "PHP 8.3+ / Laravel 13 Framework / Blade / Bootstrap 5.3 CDN"),
        ("Cơ sở dữ liệu:", "SQLite 3 (Mặc định) / MySQL 8.0+ (Chuẩn 3NF, Pessimistic Locking)"),
        ("Kiểm thử chất lượng:", "33/33 Feature Tests Passed (82 Assertions, 0 Failures)"),
        ("Tài liệu đối chiếu:", "SRS 01_phan_tich_yeu_cau.md, 02_thiet_ke_co_so_du_lieu.md"),
        ("Thời gian thực hiện:", "Năm học 2026"),
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

    # ==================== SUMMARY OF THE 5 CRITERIA ====================
    h1 = doc.add_heading("TỔNG QUAN 5 TIÊU CHÍ NGHIỆM THU ĐỒ ÁN", level=1)
    h1.paragraph_format.space_before = Pt(12)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph(
        "Báo cáo này được cấu trúc bám sát chính xác 5 tiêu chuẩn kỹ thuật phần mềm được yêu cầu, "
        "minh chứng bằng kiến trúc, sơ đồ, mô hình dữ liệu và mã nguồn thực tế đã được triển khai trong dự án:"
    )

    overview_headers = ["STT", "Nội Dung Tiêu Chí Đánh Giá", "Mức Độ Hoàn Thành", "Mã Nguồn / Tài Liệu Minh Chứng"]
    overview_data = [
        [
            "Tiêu chí 1",
            "Xác định đối tượng người dùng, vai trò, dữ liệu cần quản lý, quy trình nghiệp vụ, phạm vi và ràng buộc",
            "100% Hoàn thành",
            "docs/01_phan_tich_yeu_cau.md, UserRole Enum, Business Rules BR-01..04"
        ],
        [
            "Tiêu chí 2",
            "Thiết kế CSDL hợp lý, có ERD và quan hệ giữa các bảng",
            "100% Hoàn thành",
            "docs/diagrams/05_erd.puml, docs/02_thiet_ke_co_so_du_lieu.md, 8 bảng 3NF"
        ],
        [
            "Tiêu chí 3",
            "Sử dụng migration, model và relationship đúng chuẩn Laravel",
            "100% Hoàn thành",
            "database/migrations/* (7 files), app/Models/* (8 models), Eager Loading"
        ],
        [
            "Tiêu chí 4",
            "Thiết kế phân quyền, vai trò người dùng nếu hệ thống cần",
            "100% Hoàn thành",
            "CheckRole & CheckActive Middleware, EventPolicy, OrderPolicy, Blade @can"
        ],
        [
            "Tiêu chí 5",
            "Tổ chức route, controller, view rõ ràng, dễ bảo trì",
            "100% Hoàn thành",
            "4 tệp route riêng biệt (62 routes), Service Pattern, 12 FormRequests, Blade CDN"
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
        "1. Khách vãng lai (Guest): Người chưa đăng nhập; có quyền duyệt xem danh sách sự kiện đang mở bán, tìm kiếm từ khóa, lọc theo danh mục, xem chi tiết sự kiện và bảng giá các loại vé.\n"
        "2. Khách hàng (Customer - vai trò 'customer'): Người tham gia sự kiện; có tài khoản cá nhân, thực hiện đặt vé giữ chỗ 15 phút, thanh toán giả lập, tra cứu lịch sử đơn hàng, nhận vé điện tử kèm mã QR tra cứu và in vé.\n"
        "3. Ban tổ chức (Organizer - vai trò 'organizer'): Đơn vị chủ trì sự kiện; có Dashboard thống kê doanh thu, quản lý vòng đời sự kiện (Tạo nháp -> Gửi duyệt -> Quản lý loại vé), theo dõi danh sách đơn hàng thuộc sự kiện của mình và soát vé (Check-in) tại cổng.\n"
        "4. Quản trị viên (Admin - vai trò 'admin'): Người quản trị toàn sàn; có Dashboard tổng quan tài chính và tăng trưởng, quản lý danh mục (Categories), địa điểm (Venues), quản lý người dùng (khóa/mở khóa tài khoản), phê duyệt hoặc hủy bỏ sự kiện vi phạm."
    )

    h2 = doc.add_heading("1.2. Dữ liệu cần Quản lý (Core Business Entities)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    entities_data = [
        ("Người dùng (users)", "Tên, email duy nhất, mật khẩu mã hóa Bcrypt, số điện thoại, vai trò (admin/organizer/customer), trạng thái kích hoạt (is_active)."),
        ("Danh mục sự kiện (categories)", "Tên danh mục duy nhất, đường dẫn tĩnh thân thiện SEO (slug duy nhất), mô tả phạm vi chủ đề."),
        ("Địa điểm tổ chức (venues)", "Tên địa điểm, địa chỉ cụ thể, tỉnh/thành phố trực thuộc, sức chứa tối đa phục vụ kiểm soát quy mô."),
        ("Sự kiện (events)", "Tiêu đề, slug URL duy nhất, mô tả, ảnh banner, thời điểm bắt đầu/kết thúc, trạng thái (draft, pending, published, cancelled), soft delete."),
        ("Loại vé (ticket_types)", "Tên hạng vé, đơn giá VNĐ, tổng số lượng phát hành, số lượng đã bán/giữ chỗ (sold), thời gian mở/đóng bán, giới hạn vé mỗi đơn."),
        ("Đơn hàng (orders)", "Mã đơn duy nhất (ORD-XXXXXXXX), thông tin người nhận vé, tổng tiền, phương thức thanh toán, trạng thái (pending, paid, cancelled), thời hạn giữ chỗ 15 phút (expires_at)."),
        ("Chi tiết đơn hàng (order_items)", "Khóa ngoại đơn hàng và loại vé, số lượng đặt, đơn giá tại thời điểm đặt (đóng băng giá), thành tiền."),
        ("Vé điện tử (tickets)", "Mã vé duy nhất (TKT-XXXXXXXXXX), mã tra cứu QR, trạng thái (valid, used, cancelled), thời điểm quét check-in tại cổng."),
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
        "• Quy trình 1 - Tạo và Phê duyệt Sự kiện: Ban tổ chức tạo bản nháp (draft) -> Thêm các loại vé tương ứng -> Nhấn gửi duyệt (pending) -> Admin kiểm duyệt: nếu đạt tiêu chuẩn thì phê duyệt (published) để hiển thị lên Storefront, nếu chưa đạt thì từ chối (draft) kèm lý do.\n"
        "• Quy trình 2 - Đặt vé, Giữ chỗ và Thanh toán: Khách hàng chọn số lượng vé -> Hệ thống thực thi giao dịch Database Transaction kết hợp Khóa bi quan (`lockForUpdate()`) trên `ticket_types` -> Nếu đủ số lượng, sinh mã đơn `ORD-XXXXXXXX`, tăng tạm thời số `sold` và cho giữ chỗ 15 phút (`expires_at = now()->addMinutes(15)`) -> Khách chọn cổng thanh toán (Chuyển khoản, MoMo, Thẻ) -> Khi thanh toán thành công, đơn chuyển sang `paid`, hệ thống tự động sinh các vé điện tử độc nhất `TKT-XXXXXXXXXX`.\n"
        "• Quy trình 3 - Soát vé (Check-in) 5 bước tại cổng: Ban tổ chức truy cập màn hình `/organizer/checkin` -> Nhập mã vé hoặc quét QR -> Hệ thống thực thi 5 bước xác thực nghiêm ngặt qua `TicketCheckinService`: (1) Vé có tồn tại không? (2) Đơn hàng đã thanh toán chưa? (3) Vé đã bị hủy chưa? (4) Vé đã được sử dụng trước đó chưa? (5) Vé có đúng là thuộc sự kiện do Organizer này quản lý không? -> Khi thỏa mãn, cập nhật `status = 'used'` và ghi nhận `checked_in_at = now()`."
    )

    h2 = doc.add_heading("1.4. Phạm vi & Ràng buộc Nghiệp vụ (Scope & Business Rules)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "• Phạm vi (Scope): Trong phạm vi (In-Scope) tập trung hoàn chỉnh quy trình quản trị, vòng đời sự kiện, kiểm soát tồn kho đồng thời và soát vé điện tử. Ngoài phạm vi (Out-of-Scope) là liên kết trực tiếp cổng thanh toán tiền thật của ngân hàng (được thay thế bằng Mock Payment Gateway chuẩn mực).\n"
        "• Các ràng buộc cốt lõi (Business Rules):\n"
        "  - BR-01 (Định danh): Email, slug sự kiện/danh mục, mã đơn hàng `ORD-` và mã vé `TKT-` bắt buộc duy nhất 100% trên toàn hệ thống.\n"
        "  - BR-02 (Thời gian): Thời gian bắt đầu sự kiện phải trong tương lai, `end_time > start_time`, thời gian mở bán phải trước thời gian đóng bán, thời gian giữ chỗ cố định 15 phút.\n"
        "  - BR-03 (Toàn vẹn xóa): Chặn xóa danh mục và địa điểm đang có sự kiện (Restrict); Sự kiện đã phát sinh vé bán chỉ cho phép xóa mềm (Soft Delete); Admin không được tự khóa tài khoản hoặc hạ quyền của chính mình.\n"
        "  - BR-04 (Tiền tệ & Ngôn ngữ): Đơn vị tiền tệ chính thức là Việt Nam Đồng (VNĐ, phân cách hàng nghìn dấu chấm), múi giờ chuẩn `Asia/Ho_Chi_Minh`, toàn bộ thông báo tiếng Việt có dấu."
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
        "Hệ thống có 7 tệp di chuyển cơ sở dữ liệu độc lập trong `database/migrations/`. "
        "Tất cả đều tuân thủ cú pháp mới nhất của Laravel 11/12/13 với anonymous migration class, kiểu dữ liệu chặt chẽ và cơ chế cascade/restrict an toàn:"
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
        "Cả 8 Model nghiệp vụ trong thư mục `app/Models/` đều được khai báo đầy đủ thuộc tính `$fillable`, "
        "ép kiểu dữ liệu `$casts` với Enum, và thiết lập đầy đủ quan hệ hai chiều:"
    )

    models_data = [
        ("Event.php", "belongsTo(User, 'organizer_id'), belongsTo(Category), belongsTo(Venue), hasMany(TicketType), hasManyThrough(OrderItem), casts: EventStatus"),
        ("TicketType.php", "belongsTo(Event), hasMany(OrderItem), Accessor: available_quantity (quantity - sold), is_on_sale"),
        ("Order.php", "belongsTo(User), hasMany(OrderItem), hasManyThrough(Ticket), casts: OrderStatus, Scope: pending, paid, expired"),
        ("OrderItem.php", "belongsTo(Order), belongsTo(TicketType), hasMany(Ticket)"),
        ("Ticket.php", "belongsTo(OrderItem), casts: TicketStatus, Scope: valid, used, cancelled"),
        ("User.php", "hasMany(Event, 'organizer_id'), hasMany(Order), casts: UserRole, Helper: isAdmin(), isOrganizer(), isCustomer()"),
        ("Category.php", "hasMany(Event), Accessor: events_count"),
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
        "// Trích đoạn khai báo quan hệ và Casts trong app/Models/Event.php:\n"
        "protected $casts = [\n"
        "    'status' => EventStatus::class,\n"
        "    'start_time' => 'datetime',\n"
        "    'end_time' => 'datetime',\n"
        "];\n\n"
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
        "• Triệt tiêu lỗi N+1 Query: Khi truy vấn danh sách sự kiện trên Storefront và Admin Dashboard, "
        "hệ thống luôn sử dụng Eager Loading `with(['category', 'venue', 'ticketTypes'])` và `withCount('ticketTypes')`, "
        "giảm số lượng câu lệnh SQL từ hàng trăm truy vấn xuống chỉ còn 2-3 truy vấn cố định.\n"
        "• Khóa bi quan (`lockForUpdate`): Trong `app/Services/OrderService.php`, khi khách hàng nhấn thanh toán, "
        "dòng bản ghi của loại vé được khóa độc quyền trong `DB::transaction()` để kiểm tra `sold + requested <= quantity`. "
        "Cơ chế này ngăn chặn tuyệt đối tình trạng bán vượt số lượng vé (overselling) khi tải cao."
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
        "Route::prefix('organizer')->name('organizer.')->middleware(['auth', 'active', 'role:organizer,admin'])->group(...);"
    )
    add_code_snippet(doc, mw_code, "bootstrap/app.php & routes/admin.php")

    h2 = doc.add_heading("4.2. Hệ thống Policies Bảo vệ Dữ liệu Cấp Đối tượng (Chống Tấn công IDOR)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Để ngăn chặn triệt để lỗ hổng Insecure Direct Object References (IDOR - người dùng thay đổi ID trên URL để xem/sửa dữ liệu của người khác), "
        "hệ thống áp dụng 3 Laravel Policies trong `app/Policies/`:\n"
        "• `EventPolicy`: Organizer chỉ có quyền cập nhật, thêm vé, xóa hoặc gửi duyệt các sự kiện do chính mình tạo ra (`$user->id === $event->organizer_id`). Admin có quyền phê duyệt toàn quyền.\n"
        "• `TicketTypePolicy`: Chỉ cho phép chỉnh sửa loại vé khi sự kiện thuộc sở hữu của người dùng đang đăng nhập.\n"
        "• `OrderPolicy`: Khách hàng chỉ được phép xem chi tiết, thanh toán hoặc hủy các đơn hàng của chính tài khoản mình sở hữu."
    )

    policy_code = (
        "// Trích đoạn kiểm tra quyền sở hữu trong app/Policies/EventPolicy.php:\n"
        "public function update(User $user, Event $event): bool {\n"
        "    return $user->isAdmin() || ($user->isOrganizer() && $user->id === $event->organizer_id);\n"
        "}\n"
        "public function delete(User $user, Event $event): bool {\n"
        "    return $user->isAdmin() || ($user->id === $event->organizer_id && $event->status === EventStatus::DRAFT);\n"
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

    h2 = doc.add_heading("5.1. Tổ chức Tuyến đường (Routes) Module hóa & 62 Contract Routes", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Thay vì dồn toàn bộ mã vào một tệp `web.php` duy nhất gây xung đột mã nguồn và khó bảo trì, "
        "hệ thống tách biệt hoàn toàn thành 4 tệp định tuyến chuyên trách trong thư mục `routes/`:\n"
        "1. `routes/auth.php`: Định tuyến Xác thực (Đăng ký, đăng nhập throttle 5 lần/phút, đăng xuất, cập nhật hồ sơ cá nhân).\n"
        "2. `routes/storefront.php`: Định tuyến Cửa hàng & Khách hàng (Trang chủ, chi tiết sự kiện, đặt vé checkout, thanh toán giả lập, lịch sử đơn, in vé cá nhân).\n"
        "3. `routes/organizer.php`: Định tuyến Ban tổ chức có prefix `/organizer` (Dashboard, CRUD sự kiện, quản lý hạng vé, theo dõi đơn hàng, cổng soát vé Check-in).\n"
        "4. `routes/admin.php`: Định tuyến Quản trị có prefix `/admin` (Dashboard tài chính, quản lý danh mục, quản lý địa điểm, quản lý tài khoản người dùng, duyệt sự kiện, xem tất cả đơn hàng).\n"
        "Tổng cộng 62 routes chuẩn RESTful, có tên định danh (`->name()`) rõ ràng, kết nối tự động trong `routes/web.php`."
    )

    h2 = doc.add_heading("5.2. Tổ chức Controllers & Áp dụng Service Pattern", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "• Phân chia Controller theo Namespace chuyên biệt: `App\\Http\\Controllers\\Auth`, `Storefront`, `Organizer`, `Admin`.\n"
        "• Tách bạch Nghiệp vụ (Skinny Controller, Fat Service): Các logic nghiệp vụ phức tạp liên quan đến giao dịch tài chính, "
        "khóa đồng thời và soát vé đa bước được tách hoàn toàn sang thư mục `app/Services/`:\n"
        "  - `OrderService.php`: Chịu trách nhiệm tạo đơn hàng trong `DB::transaction()`, áp dụng `lockForUpdate()`, xử lý thanh toán giả lập, hủy đơn và hoàn trả vé vào kho.\n"
        "  - `TicketCheckinService.php`: Đảm nhiệm 5 bước xác thực tính hợp lệ của mã vé tại cổng kiểm soát.\n"
        "• 12 Form Requests độc lập trong `app/Http/Requests/`: Tách toàn bộ logic kiểm tra hợp lệ dữ liệu (validation rules, attributes, error messages) ra khỏi Controller, thông báo 100% bằng tiếng Việt chuẩn có dấu."
    )

    h2 = doc.add_heading("5.3. Tổ chức Giao diện (Views & Blade Templates)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Hệ thống View trong `resources/views/` được module hóa và tái sử dụng tối đa:\n"
        "• Hệ thống 2 Master Layout chuẩn mực:\n"
        "  - `layouts/app.blade.php`: Layout giao diện Cửa hàng (Storefront) dành cho khách hàng với Navbar, thanh tìm kiếm nhanh, giỏ vé, footer và liên kết tài khoản.\n"
        "  - `layouts/panel.blade.php`: Layout Bảng điều khiển quản trị (Dashboard) chuyên dụng cho Admin và Organizer với thanh Sidebar thu gọn, menu phân cấp theo vai trò, thống kê và thông tin tài khoản.\n"
        "• Các Partials dùng chung: `resources/views/partials/alerts.blade.php` (hiển thị thông báo flash message thành công/thất bại), `form-errors.blade.php` (hiển thị danh sách lỗi dữ liệu đầu vào).\n"
        "• Các trang lỗi tùy biến chuẩn hóa: `resources/views/errors/` bao gồm 403 (Không có quyền truy cập), 404 (Không tìm thấy trang), 419 (Hết hạn phiên CSRF), 500 (Lỗi máy chủ nội bộ).\n"
        "• Công nghệ UI: Toàn bộ giao diện sử dụng Bootstrap 5.3 CDN và Bootstrap Icons CDN, hoàn toàn không phụ thuộc vào `npm` hay Node.js (Zero-node build), tải trang cực nhanh và tương thích hoàn hảo trên mọi thiết bị di động (Responsive)."
    )

    # ==================== KẾT LUẬN ====================
    h1 = doc.add_heading("KẾT LUẬN & ĐÁNH GIÁ CHUNG", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph(
        "Dự án 'Hệ Thống Quản Lý Bán Vé Sự Kiện' đã đáp ứng trọn vẹn, xuất sắc cả 5 tiêu chí đánh giá cốt lõi:\n"
        "1. Xác định nghiệp vụ sắc nét: Phân vai 4 tác tử rõ ràng, dữ liệu quản lý đầy đủ, 3 quy trình trọng yếu thông suốt.\n"
        "2. Thiết kế CSDL mẫu mực: Đạt chuẩn 3NF, ERD quan hệ chặt chẽ, bảo vệ toàn vẹn bằng CASCADE/RESTRICT và đánh chỉ mục tối ưu.\n"
        "3. Triển khai Laravel chuẩn công nghiệp: 7 migration sạch sẽ, 8 model đầy đủ quan hệ 2 chiều, loại bỏ N+1 query và có Pessimistic Locking chống race condition.\n"
        "4. Phân quyền đa tầng bảo mật: Kết hợp Middleware kiểm tra vai trò/khóa tài khoản và Policies chống IDOR dữ liệu.\n"
        "5. Kiến trúc MVC gọn gàng: 4 tệp route tách biệt, áp dụng Service Pattern và Form Requests, giao diện Blade Bootstrap 5 responsive đẹp mắt.\n\n"
        "Hệ thống đã trải qua kiểm thử tự động toàn diện với 33/33 Feature Tests passed (82 assertions) và sẵn sàng 100% để bảo vệ trước Hội đồng chấm đồ án."
    )

    # Save document
    doc.save(output_path)
    print(f"Report document successfully created at: {output_path}")

if __name__ == "__main__":
    out_file = r"d:\NMNC\event-ticket\docs\03_bao_cao_tieu_chi_do_an.docx"
    generate_report(out_file)
