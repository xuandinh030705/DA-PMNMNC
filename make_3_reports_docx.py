# -*- coding: utf-8 -*-
"""
Script to generate 3 comprehensive, professional Word Reports (.docx)
corresponding to the 3 rubric images provided:

Report 1: docs/04_bao_cao_xay_dung_chuc_nang.docx
  - Mục 3: Xây dựng chức năng (PLO3) - 7 tiêu chí:
    1. Hoàn thành các chức năng CRUD chính
    2. Tìm kiếm, lọc, sắp xếp hoặc phân trang dữ liệu
    3. Áp dụng Laravel MVC: route, controller, model, blade
    4. Validation form nhập liệu
    5. Đăng nhập, đăng xuất, xác thực người dùng
    6. Giao diện Blade rõ ràng, dễ sử dụng, có layout/template phù hợp
    7. Chức năng hoạt động ổn định, ít lỗi khi thao tác

Report 2: docs/05_bao_cao_kiem_thu_bao_mat_trien_khai.docx
  - Mục 4: Kiểm thử & bảo mật (PLO3) - 4 tiêu chí:
    1. Kiểm tra dữ liệu đầu vào và thông báo lỗi rõ ràng
    2. Xử lý lỗi: trống, sai định dạng, trùng mã, không tìm thấy dữ liệu
    3. Áp dụng CSRF, middleware, auth, phân quyền
    4. Minh chứng kiểm thử chức năng (33 Feature tests, 82 assertions)
  - Mục 5: Triển khai (PLO3) - 4 tiêu chí:
    5. Project chạy được trên môi trường Laravel chuẩn
    6. Database, migration, seed/script dữ liệu mẫu
    7. Hướng dẫn cài đặt và chạy hệ thống
    8. Mã nguồn tổ chức rõ, nộp đầy đủ, không thiếu file quan trọng

Report 3: docs/06_bao_cao_lam_viec_nhom_va_bao_ve.docx
  - Mục 6: Làm việc nhóm (PLO6) - 4 tiêu chí:
    1. Phân công nhiệm vụ rõ ràng giữa thành viên
    2. Các thành viên có đóng góp phù hợp
    3. Nhật ký làm việc, commit Git hoặc minh chứng tiến độ
    4. Phối hợp, giải quyết vấn đề, hoàn thành đúng hạn
  - Mục 7: Trình bày & bảo vệ đồ án (PLO1, PLO2) - 1 tiêu chí lớn:
    5. Kịch bản Demo sản phẩm 8-10 phút & 15 câu hỏi - đáp phản biện bảo vệ đồ án
"""

import os
import docx
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import parse_xml
from docx.oxml.ns import nsdecls

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
            align = WD_ALIGN_PARAGRAPH.CENTER if c_idx == 0 and len(str(val)) < 15 else WD_ALIGN_PARAGRAPH.LEFT
            format_cell_text(row_cells[c_idx], str(val), bold=(c_idx == 0 and len(headers) <= 4), font_size=9.5, align=align)
            set_cell_background(row_cells[c_idx], bg_color)
            set_cell_margins(row_cells[c_idx], top=80, bottom=80, left=140, right=140)
            row_cells[c_idx].vertical_alignment = WD_ALIGN_VERTICAL.CENTER

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
        r_t = p_title.add_run(f"Đoạn mã minh chứng: {title}")
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

    pPr = p._element.get_or_add_pPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="F1F5F9"/>')
    pPr.append(shd)

def create_base_document(title_sub, title_main, title_doc, meta_data):
    doc = Document()
    for s in doc.sections:
        s.top_margin = Inches(1.0)
        s.bottom_margin = Inches(1.0)
        s.left_margin = Inches(1.0)
        s.right_margin = Inches(1.0)

    normal_style = doc.styles['Normal']
    normal_style.font.name = 'Arial'
    normal_style.font.size = Pt(11)
    normal_style.font.color.rgb = RGBColor(51, 65, 85)

    # Cover
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(30)
    p.paragraph_format.space_after = Pt(6)
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_sub = p.add_run(title_sub + "\n")
    r_sub.font.size = Pt(13)
    r_sub.font.bold = True
    r_sub.font.color.rgb = RGBColor(100, 116, 139)

    r_main = p.add_run(title_main)
    r_main.font.size = Pt(20)
    r_main.font.bold = True
    r_main.font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph().paragraph_format.space_after = Pt(10)

    p_doc = doc.add_paragraph()
    p_doc.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_doc = p_doc.add_run(title_doc)
    r_doc.font.size = Pt(13)
    r_doc.font.bold = True
    r_doc.font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph().paragraph_format.space_after = Pt(24)

    meta_table = doc.add_table(rows=len(meta_data), cols=2)
    meta_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(meta_table, color="CBD5E1", sz="6")
    for idx, (label, val) in enumerate(meta_data):
        cells = meta_table.rows[idx].cells
        format_cell_text(cells[0], label, bold=True, color_rgb=(30, 58, 138), font_size=10)
        format_cell_text(cells[1], val, bold=False, color_rgb=(15, 23, 42), font_size=10)
        set_cell_background(cells[0], "F1F5F9")
        set_cell_background(cells[1], "FFFFFF")
        set_cell_margins(cells[0], top=80, bottom=80, left=140, right=140)
        set_cell_margins(cells[1], top=80, bottom=80, left=140, right=140)
        cells[0].width = Inches(2.3)
        cells[1].width = Inches(4.2)

    doc.add_page_break()
    return doc

# ==============================================================================
# REPORT 1: XÂY DỰNG CHỨC NĂNG (PLO3 - 7 TIÊU CHÍ)
# ==============================================================================
def generate_report_1(output_path):
    meta = [
        ("Tên dự án:", "Hệ Thống Quản Lý Bán Vé Sự Kiện Trực Tuyến"),
        ("Nhóm thực hiện:", "Nhóm 8 - Lớp 68PM2 (Hồ Đăng Quang, Tống Xuân Định, Nguyễn Hữu Quý)"),
        ("Chuyên đề báo cáo:", "Mục 3: Xây Dựng Chức Năng Hệ Thống (PLO3)"),
        ("Số tiêu chí nghiệm thu:", "7/7 tiêu chí hoàn thành"),
        ("Công nghệ sử dụng:", "Laravel 13, Blade, Bootstrap 5.3 CDN, MySQL 8, Endroid QR, DomPDF, html5-qrcode"),
        ("Trạng thái chức năng:", "Hoạt động ổn định, 103/103 tests pass (411 assertions), Pint passed"),
    ]
    doc = create_base_document(
        "BÁO CÁO NGHIỆM THU KỸ THUẬT PHẦN MỀM",
        "HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN\n(EVENT TICKET MANAGEMENT SYSTEM)",
        "BÁO CÁO MỤC 3: XÂY DỰNG CHỨC NĂNG HỆ THỐNG (PLO3)\n(ĐÁNH GIÁ CHI TIẾT 7 TIÊU CHÍ CHỨC NĂNG & MVC)",
        meta
    )

    h1 = doc.add_heading("TỔNG HỢP TIÊU CHÍ ĐÁNH GIÁ MỤC 3 (PLO3)", level=1)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)
    
    headers = ["Tiêu Chí", "Nội Dung Tiêu Chí", "Mức Độ", "Minh Chứng Kỹ Thuật"]
    data = [
        ["Tiêu chí 3.1", "Hoàn thành các chức năng CRUD chính", "100%", "CRUD Events, Ticket Types (zone, description), Orders, Categories, Venues, Users"],
        ["Tiêu chí 3.2", "Tìm kiếm, lọc, sắp xếp hoặc phân trang dữ liệu", "100%", "Storefront search/filter/sort, Admin/Org filters, Pagination 9/10/12"],
        ["Tiêu chí 3.3", "Áp dụng Laravel MVC: route, controller, model, blade", "100%", "4 Route files (58 app routes), 4 Services, OrderPaidMail, 8 Models + 4 Enums"],
        ["Tiêu chí 3.4", "Validation form nhập liệu", "100%", "11 FormRequests tiếng Việt có dấu, kiểm tra ngày, giá, số lượng, zone, QR payload"],
        ["Tiêu chí 3.5", "Đăng nhập, đăng xuất, xác thực người dùng nếu phù hợp", "100%", "AuthController, throttle:5,1, Bcrypt hash, CheckActive, remember_token"],
        ["Tiêu chí 3.6", "Giao diện Blade rõ ràng, dễ sử dụng, có layout/template", "100%", "layouts.app, layouts.panel, pdf.ticket, emails.order-paid, Bootstrap 5 CDN"],
        ["Tiêu chí 3.7", "Chức năng hoạt động ổn định, ít lỗi khi thao tác", "100%", "lockForUpdate, QR HMAC, PDF RAM, Camera Check-in 6 bước, Admin cancel cascade, 103 tests"],
    ]
    add_styled_table(doc, headers, data, col_widths=[Inches(1.0), Inches(2.7), Inches(0.8), Inches(2.2)])

    # 3.1
    h2 = doc.add_heading("3.1. Hoàn Thành Các Chức Năng CRUD Chính", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Hệ thống triển khai trọn vẹn các cụm chức năng CRUD (Create - Read - Update - Delete) trên 6 phân hệ cốt lõi:\n"
        "1. Quản lý Sự kiện (Events CRUD - Ban tổ chức & Admin): Tạo mới sự kiện, tải ảnh banner lưu storage, xem chi tiết, cập nhật khi ở trạng thái `draft`, gửi duyệt (`pending`), Admin duyệt (`published`), từ chối (`draft`) hoặc hủy (`cancelled` với cascade hủy đơn pending hoàn vé & hủy vé valid), xóa mềm qua `SoftDeletes`.\n"
        "2. Quản lý Hạng vé (Ticket Types CRUD - Ban tổ chức): Tạo/sửa/xóa các loại vé lồng trong sự kiện, hỗ trợ thêm khu vực (`zone`) và quyền lợi (`description`), cập nhật giá VNĐ, số lượng phát hành (không nhỏ hơn `sold`), giới hạn mỗi đơn và thời gian mở/đóng bán.\n"
        "3. Quản lý Đặt vé, Đơn hàng & Vé điện tử (Orders & Tickets): Đặt vé giữ chỗ 15 phút, thanh toán giả lập, gửi email `OrderPaidMail` kèm PDF đính kèm, hiển thị mã QR ký HMAC-SHA256, tải vé PDF khổ A5 (`/my-tickets/{ticket}/pdf`), hủy đơn `pending`, tra cứu đơn hàng cho Organizer và Admin (đều hiển thị `zone`, `description` & nhân viên soát vé `checkedInBy`).\n"
        "4. Quản lý Danh mục (Categories CRUD - Admin): Thêm mới danh mục, tạo slug tự động, sửa mô tả, xóa danh mục (cơ chế `RESTRICT` chặn xóa khi đang có sự kiện).\n"
        "5. Quản lý Địa điểm (Venues CRUD - Admin): Thêm mới địa điểm, cập nhật sức chứa (`capacity`), sửa địa chỉ, chặn xóa khi có sự kiện liên kết.\n"
        "6. Quản lý Người dùng (Users CRUD - Admin): Xem danh sách, tìm kiếm, chỉnh sửa vai trò (`UserRole`), kích hoạt hoặc khóa tài khoản (`is_active`)."
    )

    # 3.2
    h2 = doc.add_heading("3.2. Tìm Kiếm, Lọc, Sắp Xếp & Phân Trang Dữ Liệu", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "• Tìm kiếm đa điều kiện (Multi-criteria Search): Tại trang chủ Storefront, khách hàng có thể tìm kiếm theo từ khóa tiêu đề sự kiện (`?q=...`), lọc theo danh mục (`?category=...`), lọc theo thành phố (`?city=...`), và lọc theo khoảng thời gian tổ chức (`?from=...&to=...`).\n"
        "• Sắp xếp linh hoạt (Sorting): Sắp xếp sự kiện theo ngày bắt đầu (`date_asc`, `date_desc`) hoặc theo giá vé (`price_asc`, `price_desc`) thông qua `withMin('ticketTypes as min_price', 'price')`.\n"
        "• Phân trang chuẩn mực (Pagination): Tất cả các danh sách hiển thị đều áp dụng `paginate()` kết hợp `withQueryString()` giữ nguyên bộ lọc: Storefront (9 sự kiện/trang), Vé của tôi (12 vé/trang), Admin & Organizer (10 dòng/trang). Giao diện phân trang được cấu hình Bootstrap 5 thông qua `Paginator::useBootstrapFive()` tại `AppServiceProvider`."
    )

    # 3.3
    h2 = doc.add_heading("3.3. Áp Dụng Chuẩn Mực Mô Hình Laravel MVC", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Mô hình Model - View - Controller được hiện thực hóa với kiến trúc sạch (Clean Architecture):\n"
        "• Routes (58 Application Routes): Tách thành 4 tệp độc lập `auth.php`, `storefront.php`, `organizer.php`, `admin.php`.\n"
        "• Controllers, Services & Mailable: Tổ chức theo namespace `App\\Http\\Controllers\\Auth`, `Storefront`, `Organizer`, `Admin`; tách nghiệp vụ sang 4 Services (`OrderService`, `TicketCheckinService`, `QrTicketService`, `TicketPdfService`) và `OrderPaidMail`.\n"
        "• Models & Enums: 8 Eloquent models trong `app/Models/` đồng bộ 4 Backed Enums (`UserRole`, `EventStatus`, `OrderStatus`, `TicketStatus`), quan hệ 2 chiều, scopes truy vấn cục bộ và accessors.\n"
        "• Views: Hệ thống Blade template kế thừa phân cấp, tái sử dụng partials, kèm view `pdf/ticket.blade.php` và `emails/order-paid.blade.php`."
    )

    # 3.4
    h2 = doc.add_heading("3.4. Validation Form Nhập Liệu (11 Form Requests)", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Hệ thống không validate thủ công trong Controller mà tách thành 11 Form Requests độc lập trong `app/Http/Requests/`. "
        "Mỗi Form Request đều định nghĩa rõ 3 thành phần: `rules()`, `attributes()` và `messages()` bằng tiếng Việt có dấu:"
    )
    vr_code = (
        "// Trích đoạn rules tại app/Http/Requests/Organizer/EventRequest.php:\n"
        "public function rules(): array {\n"
        "    return [\n"
        "        'title' => ['required', 'string', 'max:255'],\n"
        "        'category_id' => ['required', 'exists:categories,id'],\n"
        "        'venue_id' => ['required', 'exists:venues,id'],\n"
        "        'start_time' => ['required', 'date', 'after:now'],\n"
        "        'end_time' => ['required', 'date', 'after:start_time'],\n"
        "        'banner' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],\n"
        "    ];\n"
        "}"
    )
    add_code_snippet(doc, vr_code, "app/Http/Requests/Organizer/EventRequest.php")

    # 3.5
    h2 = doc.add_heading("3.5. Đăng Nhập, Đăng Xuất & Xác Thực Người Dùng", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "• Cơ chế xác thực: Xây dựng bằng `AuthController` thuần túy, mật khẩu băm Bcrypt an toàn.\n"
        "• Chống brute-force: Áp dụng route middleware `throttle:5,1` trên `POST /login` giới hạn tối đa 5 lần thử đăng nhập trong 1 phút.\n"
        "• Ghi nhớ đăng nhập: Hỗ trợ tính năng `Remember Me` sinh token ngẫu nhiên lưu trong cột `remember_token`.\n"
        "• Quản lý hồ sơ: Người dùng có thể xem và cập nhật thông tin cá nhân, thay đổi mật khẩu (kiểm tra `current_password`) tại `/profile`."
    )

    # 3.6
    h2 = doc.add_heading("3.6. Giao Diện Blade Rõ Ràng, Có Layout Phù Hợp", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "• Thiết kế 2 Master Layout đồng bộ CSS:\n"
        "  - `resources/views/layouts/app.blade.php`: Dành cho khách vãng lai và khách hàng, giao diện Storefront hiện đại, thanh điều hướng, nút đăng nhập/đăng ký và footer.\n"
        "  - `resources/views/layouts/panel.blade.php`: Dành cho Dashboard Quản trị và Ban tổ chức, tích hợp thanh Sidebar bên trái thu gọn, thống kê thẻ Card và bảng dữ liệu.\n"
        "• Partials tái sử dụng: `partials/alerts.blade.php` hiển thị thông báo flash message; `partials/form-errors.blade.php` hiển thị danh sách lỗi dữ liệu đầu vào.\n"
        "• Công nghệ: Bootstrap 5.3 CDN và Bootstrap Icons qua CDN jsDelivr, hoàn toàn không cần cấu hình Node.js / npm."
    )

    # 3.7
    h2 = doc.add_heading("3.7. Chức Năng Hoạt Động Ổn Định, Ít Lỗi Khi Thao Tác", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Hệ thống đạt tính ổn định tối đa nhờ các cơ chế phòng thủ sâu:\n"
        "1. Khóa bi quan (`lockForUpdate()`): Ngăn ngừa 100% xung đột bán vé đồng thời (Race Condition), check-in trùng và đảm bảo Admin hủy sự kiện cascade nhất quán.\n"
        "2. Tự động thu hồi vé: Đơn hàng quá 15 phút chưa thanh toán được tự động hủy và hoàn trả vé vào kho (`orders:expire-pending` và Lazy Check).\n"
        "3. Soát vé 6 bước & QR HMAC-SHA256: `TicketCheckinService` hỗ trợ quét Camera (`html5-qrcode.min.js` offline) lẫn nhập tay, xác thực chữ ký HMAC-SHA256 bằng `hash_equals()`, ngăn vé giả, vé chưa thanh toán, vé hủy hoặc vé BTC khác, và lưu `checked_in_by`.\n"
        "4. Xuất PDF trong RAM & Chịu lỗi Mail: `TicketPdfService` tạo PDF khổ A5 bằng font DejaVu Sans không ghi đĩa; `OrderController@pay` bọc `try/catch` khi gửi `OrderPaidMail`.\n"
        "5. Bắt lỗi nghiệp vụ: Lớp ngoại lệ tùy biến `BusinessException` hiển thị thông báo lỗi thân thiện thay vì làm sập ứng dụng."
    )

    doc.save(output_path)
    print(f"Report 1 created at {output_path}")

# ==============================================================================
# REPORT 2: KIỂM THỬ, BẢO MẬT & TRIỂN KHAI (PLO3 - 8 TIÊU CHÍ)
# ==============================================================================
def generate_report_2(output_path):
    meta = [
        ("Tên dự án:", "Hệ Thống Quản Lý Bán Vé Sự Kiện Trực Tuyến"),
        ("Nhóm thực hiện:", "Nhóm 8 - Lớp 68PM2 (Hồ Đăng Quang, Tống Xuân Định, Nguyễn Hữu Quý)"),
        ("Chuyên đề báo cáo:", "Mục 4: Kiểm Thử & Bảo Mật + Mục 5: Triển Khai (PLO3)"),
        ("Số tiêu chí nghiệm thu:", "8/8 tiêu chí hoàn thành"),
        ("Bộ kiểm thử tự động:", "103/103 Unit & Feature Tests Passed (411 Assertions, 0 Failures)"),
        ("Tiêu chuẩn bảo mật:", "CSRF Token, Role/Active Middleware, Policies chống IDOR, QR HMAC-SHA256"),
    ]
    doc = create_base_document(
        "BÁO CÁO NGHIỆM THU KỸ THUẬT PHẦN MỀM",
        "HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN\n(EVENT TICKET MANAGEMENT SYSTEM)",
        "BÁO CÁO MỤC 4 & 5: KIỂM THỬ, BẢO MẬT & TRIỂN KHAI (PLO3)\n(ĐÁNH GIÁ CHI TIẾT 8 TIÊU CHÍ AN NINH, TEST VÀ DEPLOY)",
        meta
    )

    h1 = doc.add_heading("TỔNG HỢP TIÊU CHÍ ĐÁNH GIÁ MỤC 4 & 5 (PLO3)", level=1)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)
    
    headers = ["Nhóm", "Nội Dung Tiêu Chí Đánh Giá", "Mức Độ", "Minh Chứng Kỹ Thuật"]
    data = [
        ["Kiểm thử", "Kiểm tra dữ liệu đầu vào và thông báo lỗi rõ ràng", "100%", "11 FormRequests, hiển thị lỗi tiếng Việt dưới từng input form"],
        ["Kiểm thử", "Xử lý lỗi: trống, sai định dạng, trùng mã, không tìm thấy", "100%", "Unique validation, ModelNotFound 404, BusinessException, chịu lỗi mail offline"],
        ["Bảo mật", "Áp dụng CSRF, middleware, auth, phân quyền & chữ ký QR", "100%", "100% @csrf, CheckRole, CheckActive, 3 Policies (cả PDF), QR HMAC-SHA256"],
        ["Kiểm thử", "Minh chứng kiểm thử chức năng", "100%", "103 Unit & Feature tests passed (411 assertions), Pint passed, GitHub Actions CI"],
        ["Triển khai", "Project chạy được trên môi trường Laravel chuẩn", "100%", "PHP 8.5.9 (gd, pdo_mysql), Laravel 13, zero-node build CDN, html5-qrcode local"],
        ["Triển khai", "Database, migration, seed/script dữ liệu mẫu", "100%", "12 migrations (7 nghiệp vụ + 2 nâng cấp), DatabaseSeeder + TicketboxEventSeeder"],
        ["Triển khai", "Hướng dẫn cài đặt và chạy hệ thống", "100%", "README.md hướng dẫn MySQL 8, XAMPP/Laragon, SQLite dự phòng, ext-gd, Mailpit"],
        ["Triển khai", "Mã nguồn tổ chức rõ, nộp đầy đủ, không thiếu file quan trọng", "100%", "Cây thư mục chuẩn Laravel, .github/workflows/ci.yml, .env.example, docs & sơ đồ"],
    ]
    add_styled_table(doc, headers, data, col_widths=[Inches(1.0), Inches(2.7), Inches(0.8), Inches(2.2)])

    # Section 4: Testing & Security
    h1 = doc.add_heading("MỤC 4: KIỂM THỬ & BẢO MẬT HỆ THỐNG", level=1)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    h2 = doc.add_heading("4.1. Kiểm Tra Dữ Liệu Đầu Vào & Thông Báo Lỗi Rõ Ràng", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Mọi dữ liệu gửi lên từ người dùng qua phương thức POST/PUT đều được kiểm tra nghiêm ngặt:\n"
        "• Kiểm tra dữ liệu tại 11 Form Request trước khi vào Controller (bao gồm `zone`, `description` và dữ liệu quét QR).\n"
        "• Khi có lỗi, hệ thống tự động redirect về form cũ kèm dữ liệu nhập trước đó (`old()`) và danh sách lỗi (`$errors`); riêng chuỗi JSON quét QR lỗi không điền ngược lại vào ô nhập tay.\n"
        "• Tầng View hiển thị thông báo lỗi chi tiết ngay dưới từng trường nhập liệu bằng lớp CSS `.is-invalid` và `.invalid-feedback` của Bootstrap 5."
    )

    h2 = doc.add_heading("4.2. Xử Lý Các Trường Hợp Ngoại Lệ & Vận Hành Offline", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "• Lỗi để trống / sai định dạng: Bắt buộc thông qua các rules `required`, `email`, `numeric`, `date`, `min`, `max`, `mimes`, `in`.\n"
        "• Lỗi trùng lặp dữ liệu: Rule `unique:users,email` chặn trùng email đăng ký; slug sự kiện tự động thêm hậu tố số tăng dần nếu trùng tiêu đề; mã đơn `ORD-` và mã vé `TKT-` sinh ngẫu nhiên và kiểm tra tồn tại qua vòng lặp `do ... while`.\n"
        "• Lỗi không tìm thấy dữ liệu: Route Model Binding tự động chuyển hướng đến trang lỗi 404 thân thiện (`resources/views/errors/404.blade.php`).\n"
        "• Lỗi nghiệp vụ & Lỗi Mail Transport: Ném qua `BusinessException` hiển thị flash đỏ; bước gửi `OrderPaidMail` bọc `try/catch` + `report()` để lỗi SMTP khi chạy offline không làm rollback đơn hàng đã thanh toán."
    )

    h2 = doc.add_heading("4.3. Bảo Mật Toàn Diện: CSRF, XSS, Chữ ký số QR, Middleware & Policies", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "• Bảo vệ CSRF & XSS: 100% các thẻ `<form>` thực hiện POST/PUT/DELETE đều nhúng `@csrf`; 100% output Blade dùng `{{ ... }}` (không dùng `{!! !!}`).\n"
        "• Bảo vệ mã vé QR bằng HMAC-SHA256: Payload JSON `{\"v\":1,\"ticket_code\":\"TKT-...\"}` ký bằng `APP_KEY` và kiểm tra hằng thời gian qua `hash_equals()` trong `QrTicketService`.\n"
        "• Middleware tầng Route: `CheckRole` chặn truy cập trái vai trò (403); `CheckActive` tự động đăng xuất khi tài khoản bị khóa; `throttle:5,1` chống brute-force đăng nhập.\n"
        "• Policies tầng Resource (Chống IDOR): `EventPolicy`, `TicketTypePolicy`, `OrderPolicy` bảo vệ cả route tải vé PDF `/my-tickets/{ticket}/pdf` và đơn hàng của Organizer."
    )

    h2 = doc.add_heading("4.4. Minh Chứng Kiểm Thử Chức Năng (103 Unit & Feature Tests / 411 Assertions)", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Dự án được trang bị bộ kiểm thử tự động hoàn chỉnh gồm 103 ca kiểm thử (15 Unit + 88 Feature) trên 10 test suites, "
        "chạy thực tế qua lệnh `php artisan test` đạt tỉ lệ thành công 100% (411 assertions, 0 failures) và `vendor/bin/pint --test` sạch 100%:"
    )

    test_summary_headers = ["Nhóm Kiểm Thử (Test Class)", "Số Test", "Số Assertions", "Mục Tiêu Kiểm Định"]
    test_summary_data = [
        ["DomainModelsTest (Unit)", "2 tests", "29 assertions", "Accessor remaining của TicketType, role helpers & đối chiếu Enum"],
        ["QrTicketServiceTest (Unit)", "13 tests", "27 assertions", "Payload QR, PNG/SVG Data URI, ký & verify HMAC-SHA256, đổi APP_KEY"],
        ["AuthTest", "6 tests", "26 assertions", "Đăng ký, đăng nhập đúng/sai, chặn tài khoản bị khóa, profile, đăng xuất"],
        ["AuthorizationTest", "6 tests", "9 assertions", "Chặn trái quyền Admin/Organizer, chống IDOR Event/Order/Organizer Order, chặn tự khóa"],
        ["StorefrontTest", "6 tests", "18 assertions", "Duyệt sự kiện, tìm/lọc, sort giá NULL cuối, 404 bản nháp, hiển thị zone/description"],
        ["OrderAndPaymentTest", "19 tests", "93 assertions", "Giữ chỗ 15p, chặn pay event hủy, OrderPaidMail, chịu lỗi mail, chống N+1, chống âm sold, E2E & checkedInBy"],
        ["OrganizerTicketTypeTest", "9 tests", "37 assertions", "Form tạo/sửa zone & description, validate độ dài/min 1, giữ banner soft-delete, chặn BTC khác"],
        ["CheckinTest", "20 tests", "63 assertions", "Soát vé 6 bước bằng mã tay & JSON QR HMAC, chặn mã giả/hủy/chéo BTC/xóa mềm, camera offline"],
        ["TicketPdfTest", "9 tests", "27 assertions", "Render PDF khổ A5 trong RAM không ghi đĩa, tiếng Việt Unicode, quyền tải PDF qua OrderPolicy"],
        ["AdminManagementTest", "13 tests", "82 assertions", "Duyệt/từ chối sự kiện, hủy cascade, Top bán chạy, RESTRICT kể cả soft-deleted, khóa user, checkedInBy"],
    ]
    add_styled_table(doc, test_summary_headers, test_summary_data, col_widths=[Inches(1.8), Inches(0.9), Inches(1.3), Inches(2.7)])

    # Section 5: Deployment
    h1 = doc.add_heading("MỤC 5: TRIỂN KHAI DỰ ÁN", level=1)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    h2 = doc.add_heading("5.1. Môi Trường Chạy Chuẩn, Zero-Node & Offline", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "• Yêu cầu môi trường: PHP >= 8.3 (đã kiểm thử trên PHP 8.5.9) kèm extension `pdo_mysql`, `gd`, `mbstring`, `openssl`, `fileinfo`, `zip`; Composer 2.x; MySQL 8.0+ Community Server (mặc định) hoặc SQLite dự phòng.\n"
        "• Triển khai Zero-Node & Offline: Giao diện dùng Bootstrap 5.3 CDN & Bootstrap Icons CDN (không cần Node.js / `npm run build`); thư viện camera quét QR `html5-qrcode.min.js` lưu cục bộ trong `public/js/`; QR và PDF sinh hoàn toàn cục bộ trong RAM."
    )

    h2 = doc.add_heading("5.2. Cơ Sở Dữ Liệu & Kịch Bản Nạp Dữ Liệu Mẫu (Seed Data)", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Hệ thống đi kèm trọn bộ 12 migrations (3 core + 7 nghiệp vụ + 2 nâng cấp) và 6 seeder classes trong `DatabaseSeeder`:\n"
        "• Lệnh khởi tạo một bước: `php artisan migrate:fresh --seed --force` và `php artisan storage:link`\n"
        "• Dữ liệu mẫu khởi tạo: 8 tài khoản mẫu (`@example.com`), 6 danh mục, 4 địa điểm, 8 sự kiện mẫu + 14 sự kiện thật từ `TicketboxEventSeeder` (kèm ảnh banner & 14 BTC `@ticketbox.local`), 4 đơn hàng và 3 vé điện tử."
    )

    h2 = doc.add_heading("5.3. Hướng Dẫn Cài Đặt, Mailpit & GitHub Actions CI", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Tệp `README.md` và `.env.example` hướng dẫn chi tiết cài đặt MySQL 8.0 (DB `event_ticket`, `utf8mb4_unicode_ci`), XAMPP/Laragon, chuyển sang SQLite dự phòng, cấu hình Mailpit (`127.0.0.1:1025`, web `8025`) hoặc `MAIL_MAILER=log` khi offline.\n"
        "Đồng thời, workflow `.github/workflows/ci.yml` tự động chạy `vendor/bin/pint --test`, `php artisan migrate:fresh --seed --force` và toàn bộ 103 test cases trên container `mysql:8.0` mỗi khi push/PR."
    )

    h2 = doc.add_heading("5.4. Tổ Chức Mã Nguồn & Tính Đầy Đủ", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Dự án được bàn giao đầy đủ mã nguồn Laravel, cấu hình `.env.example`, workflow CI `.github/workflows/ci.yml`, "
        "thư viện `public/js/html5-qrcode.min.js`, liên kết `public/storage`, bộ 103 kiểm thử tự động và toàn bộ tài liệu thiết kế/sơ đồ."
    )

    doc.save(output_path)
    print(f"Report 2 created at {output_path}")

# ==============================================================================
# REPORT 3: LÀM VIỆC NHÓM & BẢO VỆ ĐỒ ÁN (PLO6, PLO1, PLO2 - 5 TIÊU CHÍ)
# ==============================================================================
def generate_report_3(output_path):
    meta = [
        ("Tên dự án:", "Hệ Thống Quản Lý Bán Vé Sự Kiện Trực Tuyến"),
        ("Nhóm thực hiện:", "Nhóm 8 - Lớp 68PM2"),
        ("Danh sách thành viên:", "Hồ Đăng Quang (0021568), Tống Xuân Định (0008268), Nguyễn Hữu Quý (0022968)"),
        ("Repository GitHub:", "https://github.com/xuandinh030705/DA-PMNMNC"),
        ("Chuyên đề báo cáo:", "Mục 6: Làm Việc Nhóm (PLO6) + Mục 7: Trình Bày & Bảo Vệ (PLO1, PLO2, PLO3)"),
        ("Quản lý tiến độ:", "GitHub Flow (PR + CI/CD MySQL 8), docs/WORKLOG.md & docs/PROGRESS.md"),
    ]
    doc = create_base_document(
        "BÁO CÁO NGHIỆM THU KỸ THUẬT PHẦN MỀM",
        "HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN\n(EVENT TICKET MANAGEMENT SYSTEM)",
        "BÁO CÁO MỤC 6 & 7: LÀM VIỆC NHÓM & BẢO VỆ ĐỒ ÁN (PLO6, PLO1, PLO2, PLO3)\n(PHÂN CÔNG TIẾN ĐỘ, GITHUB FLOW, KỊCH BẢN DEMO & PHẢN BIỆN)",
        meta
    )

    h1 = doc.add_heading("TỔNG HỢP TIÊU CHÍ ĐÁNH GIÁ MỤC 6 & 7 (PLO6, PLO1, PLO2, PLO3)", level=1)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)
    
    headers = ["Nhóm", "Nội Dung Tiêu Chí Đánh Giá", "Trạng Thái", "Minh Chứng & Lưu Ý"]
    data = [
        ["Nhóm", "Phân công nhiệm vụ rõ ràng giữa thành viên", "100% Hoàn thành", "Bảng phân chia 3 thành viên trong docs/WORKLOG.md & KE_HOACH_NANG_CAP_1_THANG.md"],
        ["Nhóm", "Các thành viên có đóng góp phù hợp", "100% Hoàn thành", "Phân chia đồng đều (34% / 33% / 33%) gắn trách nhiệm từng phân hệ & PR"],
        ["Nhóm", "Nhật ký làm việc, commit Git hoặc minh chứng tiến độ", "100% Hoàn thành", "Repository GitHub (xuandinh030705/DA-PMNMNC), GitHub Flow, CI/CD, WORKLOG.md"],
        ["Nhóm", "Phối hợp, giải quyết vấn đề, hoàn thành đúng hạn", "100% Hoàn thành", "Giải quyết lockForUpdate, QR HMAC, PDF RAM, Camera offline, N+1, Admin cascade"],
        ["Bảo vệ", "Demo sản phẩm và trả lời câu hỏi về phân tích, thiết kế, code", "Sẵn sàng", "docs/DEMO_SCRIPT.md (8-10 phút demo 3 chặng + 15 câu Q&A cập nhật mới nhất)"],
    ]
    add_styled_table(doc, headers, data, col_widths=[Inches(0.9), Inches(2.7), Inches(0.9), Inches(2.2)])

    # Section 6: Teamwork
    h1 = doc.add_heading("MỤC 6: LÀM VIỆC NHÓM & QUẢN LÝ TIẾN ĐỘ (PLO6)", level=1)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    h2 = doc.add_heading("6.1. Phân Công Nhiệm Vụ Rõ Ràng Giữa Các Thành Viên (Nhóm 8 - Lớp 68PM2)", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Nhóm 8 (Lớp 68PM2) gồm 3 thành viên được phân công nhiệm vụ cụ thể qua cả giai đoạn nền tảng và kế hoạch nâng cấp 4 tuần:"
    )

    team_headers = ["Thành Viên & MSSV", "Vai Trò Trong Nhóm", "Mảng Phụ Trách", "Tỷ Lệ"]
    team_data = [
        ["Hồ Đăng Quang\n(MSSV: 0021568)", "Trưởng nhóm / Architect & Storefront", "SRS, SPEC.md, GitHub Flow/PR, MySQL 8 (.env.example), Layouts, Storefront (zone/description), OrderService, OrderPaidMail + Mailpit, tối ưu N+1, README.md, Demo Chặng 1", "34%"],
        ["Tống Xuân Định\n(MSSV: 0008268)", "Backend, DB, QR/PDF & Organizer Dev", "CSDL 3NF, 12 Migrations, 8 Models, Seeders (kèm Ticketbox), Auth/Middleware/3 Policies, QrTicketService (HMAC), TicketPdfService, Camera Check-in 6 bước (html5-qrcode), zone Organizer, Demo Chặng 2", "33%"],
        ["Nguyễn Hữu Quý\n(MSSV: 0022968)", "Admin Dev, QA, CI/CD & Architecture", "Admin Dashboard/CRUD RESTRICT/Users/Duyệt & Hủy sự kiện cascade (checkedInBy), đồng bộ 4 Enums, GitHub Actions CI (MySQL 8), 103 Unit/Feature Tests, tài liệu testing & báo cáo, Demo Chặng 3", "33%"],
    ]
    add_styled_table(doc, team_headers, team_data, col_widths=[Inches(1.5), Inches(1.5), Inches(3.0), Inches(0.7)])

    h2 = doc.add_heading("6.2. Quy trình Quản lý Mã nguồn Git & GitHub Actions CI/CD", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Toàn bộ mã nguồn được quản lý trên GitHub (`https://github.com/xuandinh030705/DA-PMNMNC`) theo quy trình GitHub Flow:\n"
        "• Mỗi thành viên tạo nhánh riêng theo đầu việc (`chore/mysql8-setup`, `feat/qr-and-pdf-services`, `chore/github-actions-and-enums`, `feat/camera-checkin-and-organizer-zone`, `feat/storefront-qr-pdf-and-email`, `feat/admin-cancel-cascade-and-views`, `test/...`, `fix/...`), mở Pull Request vào `develop` / `main` và review chéo.\n"
        "• Workflow `.github/workflows/ci.yml` tự động chạy `pint --test`, `migrate:fresh --seed` và `php artisan test` trên container MySQL 8.0.\n"
        "• Nhật ký chi tiết qua từng ngày và 4 tuần nâng cấp được ghi nhận đầy đủ tại `docs/WORKLOG.md` và `docs/PROGRESS.md`."
    )

    h2 = doc.add_heading("6.3. Phối Hợp & Giải Quyết Các Vấn Đề Kỹ Thuật Phức Tạp", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Trong quá trình phát triển và rà soát kiểm thử, các vấn đề kỹ thuật trọng yếu đã được xử lý triệt để:\n"
        "1. Chống Race Condition: Áp dụng Khóa bi quan (`lockForUpdate()`) trong `DB::transaction()` tại `OrderService`, `TicketCheckinService` và `Admin\\EventController::cancel()` trên MySQL 8 InnoDB.\n"
        "2. Bảo mật QR & chống IDOR tải PDF: Ký `qr_payload` bằng HMAC-SHA256 (`hash_equals()`), bảo vệ `/my-tickets/{ticket}/pdf` qua `OrderPolicy`.\n"
        "3. Tối ưu truy vấn chống N+1: Áp dụng `with()`, `withCount()`, `withMin()`, `withSum()` và eager load `tickets.orderItem.ticketType.event.venue` + `tickets.checkedInBy` trên `orders.show`.\n"
        "4. Chạy Offline ổn định: `html5-qrcode.min.js` lưu nội bộ, PDF render trong RAM không ghi đĩa, gửi email bọc `try/catch` với mặc định `MAIL_MAILER=log`."
    )

    # Section 7: Presentation
    h1 = doc.add_heading("MỤC 7: TRÌNH BÀY & BẢO VỆ ĐỒ ÁN (PLO1, PLO2, PLO3)", level=1)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    h2 = doc.add_heading("7.1. Kịch Bản Trình Diễn Sản Phẩm (Demo Script 8-10 Phút)", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Nhóm đã chuẩn bị kịch bản demo trực tiếp theo từng phút (chi tiết tại `docs/DEMO_SCRIPT.md`) với tài khoản demo thống nhất (`admin@example.com`, `organizer1@example.com`, `customer1@example.com`, mật khẩu `password`):\n"
        "• Chặng 1 (~3.5 phút — Hồ Đăng Quang): Duyệt/tìm/lọc Storefront kèm `zone` & `description` -> Đặt vé giữ chỗ 15 phút -> Thanh toán giả lập -> Mở Mailpit xem email `OrderPaidMail` kèm PDF đính kèm -> Xem mã QR và tải vé PDF khổ A5.\n"
        "• Chặng 2 (~3.5 phút — Tống Xuân Định): Dashboard Ban tổ chức -> Tạo sự kiện & loại vé có `zone`/`description` -> Gửi duyệt -> Quét Camera QR (`html5-qrcode` offline) tại `/organizer/checkin`, hiển thị thẻ xanh HMAC-SHA256 & nhân viên soát vé, test chặn vé đã dùng / QR giả mạo.\n"
        "• Chặng 3 (~2-3 phút — Nguyễn Hữu Quý): Dashboard Admin -> Duyệt sự kiện -> Hủy sự kiện Cascade (hủy đơn `pending` hoàn vé & hủy vé `valid`) -> Xem chi tiết đơn có `checkedInBy` -> Chạy `php artisan test` (103/103 tests pass, 411 assertions) & mở GitHub Actions CI xanh trên MySQL 8."
    )

    h2 = doc.add_heading("7.2. Bộ 15 Câu Hỏi & Đáp Án Phản Biện Chuyên Sâu Của Hội Đồng", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Nhóm đã chuẩn bị sẵn câu trả lời chuyên sâu bám sát mã nguồn dự án cho 15 câu hỏi phản biện trọng tâm tại `docs/DEMO_SCRIPT.md`:\n"
        "1. Cơ chế `DB::transaction()` + `lockForUpdate()` trên MySQL 8 InnoDB chống overselling.\n"
        "2. Giữ chỗ 15 phút với Lazy Check + Scheduler `orders:expire-pending`.\n"
        "3. Bảo mật mã QR bằng chữ ký số HMAC-SHA256 (`hash_equals`) và chống IDOR tải vé PDF qua `OrderPolicy`.\n"
        "4. Quy trình soát vé Check-in 6 bước và quét Camera offline bằng `public/js/html5-qrcode.min.js`.\n"
        "5. Xuất PDF khổ A5 trong RAM qua `barryvdh/laravel-dompdf` (`extension=gd` & font DejaVu Sans) + `OrderPaidMail` qua Mailpit/`log`.\n"
        "6. Admin hủy sự kiện cascade trong transaction, tối ưu N+1 trên `orders.show`, kiến trúc Zero-node Bootstrap 5 và GitHub Actions CI.\n"
        "*(Chi tiết trọn vẹn 15 câu hỏi và câu trả lời mẫu được lưu trữ tại file docs/DEMO_SCRIPT.md)*."
    )

    doc.save(output_path)
    print(f"Report 3 created at {output_path}")

if __name__ == "__main__":
    out_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "docs")
    generate_report_1(os.path.join(out_dir, "04_bao_cao_xay_dung_chuc_nang.docx"))
    generate_report_2(os.path.join(out_dir, "05_bao_cao_kiem_thu_bao_mat_trien_khai.docx"))
    generate_report_3(os.path.join(out_dir, "06_bao_cao_lam_viec_nhom_va_bao_ve.docx"))
