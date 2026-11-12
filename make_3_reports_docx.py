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
        ("Công nghệ sử dụng:", "Laravel 13, Blade, Bootstrap 5.3 CDN, Eloquent ORM"),
        ("Trạng thái chức năng:", "Hoạt động ổn định, 37/37 tests pass (104 assertions)"),
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
        ["Tiêu chí 3.1", "Hoàn thành các chức năng CRUD chính", "100%", "CRUD Events, Ticket Types, Orders, Categories, Venues, Users"],
        ["Tiêu chí 3.2", "Tìm kiếm, lọc, sắp xếp hoặc phân trang dữ liệu", "100%", "Storefront search/filter/sort, Admin filters, Pagination 9/10/12"],
        ["Tiêu chí 3.3", "Áp dụng Laravel MVC: route, controller, model, blade", "100%", "4 Route files (57 app routes), Service Layer, 8 Models, 2 Master layouts"],
        ["Tiêu chí 3.4", "Validation form nhập liệu", "100%", "11 FormRequests tiếng Việt có dấu, kiểm tra ngày, giá, số lượng"],
        ["Tiêu chí 3.5", "Đăng nhập, đăng xuất, xác thực người dùng nếu phù hợp", "100%", "AuthController, throttle:5,1, Bcrypt hash, remember_token"],
        ["Tiêu chí 3.6", "Giao diện Blade rõ ràng, dễ sử dụng, có layout/template", "100%", "layouts.app, layouts.panel, partials alerts/errors, Bootstrap 5 CDN"],
        ["Tiêu chí 3.7", "Chức năng hoạt động ổn định, ít lỗi khi thao tác", "100%", "Khóa bi quan lockForUpdate, 5-step check-in, 37 Unit/Feature tests pass"],
    ]
    add_styled_table(doc, headers, data, col_widths=[Inches(1.0), Inches(2.7), Inches(0.8), Inches(2.2)])

    # 3.1
    h2 = doc.add_heading("3.1. Hoàn Thành Các Chức Năng CRUD Chính", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Hệ thống triển khai trọn vẹn các cụm chức năng CRUD (Create - Read - Update - Delete) trên 6 phân hệ cốt lõi:\n"
        "1. Quản lý Sự kiện (Events CRUD - Ban tổ chức & Admin): Tạo mới sự kiện, tải ảnh banner lưu storage, xem chi tiết, cập nhật nội dung, chuyển đổi trạng thái (draft -> pending -> published/cancelled), xóa sự kiện (xóa mềm qua softDeletes khi đã có đơn hàng).\n"
        "2. Quản lý Hạng vé (Ticket Types CRUD - Ban tổ chức): Tạo các loại vé (VIP, Standard, Early Bird) lồng trong sự kiện, cập nhật giá, điều chỉnh số lượng phát hành, giới hạn vé mỗi đơn và thời gian mở/đóng bán.\n"
        "3. Quản lý Đặt vé & Đơn hàng (Orders CRUD - Khách hàng & Admin): Đặt vé (Create), xem lịch sử đơn hàng cá nhân (Read), thanh toán/hủy đơn (Update status), tra cứu danh sách toàn sàn cho Admin.\n"
        "4. Quản lý Danh mục (Categories CRUD - Admin): Thêm mới danh mục, tạo slug tự động, sửa mô tả, xóa danh mục (có cơ chế RESTRICT chặn xóa khi đang có sự kiện).\n"
        "5. Quản lý Địa điểm (Venues CRUD - Admin): Thêm mới địa điểm, cập nhật sức chứa (capacity), sửa địa chỉ, chặn xóa khi có sự kiện liên kết.\n"
        "6. Quản lý Người dùng (Users CRUD - Admin): Xem danh sách, tìm kiếm, chỉnh sửa vai trò (Role), kích hoạt hoặc khóa tài khoản (`is_active`)."
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
        "• Routes (57 Application Routes): Tách thành 4 tệp độc lập `auth.php`, `storefront.php`, `organizer.php`, `admin.php`.\n"
        "• Controllers: Tổ chức theo namespace `App\\Http\\Controllers\\Auth`, `Storefront`, `Organizer`, `Admin`. Áp dụng tư tưởng Skinny Controller, trích xuất logic giao dịch phức tạp sang Service Layer (`OrderService`, `TicketCheckinService`).\n"
        "• Models: 8 Eloquent models trong `app/Models/` định nghĩa quan hệ 2 chiều (`belongsTo`, `hasMany`, `hasManyThrough`), scopes truy vấn cục bộ và accessors.\n"
        "• Views: Hệ thống Blade template kế thừa phân cấp, tái sử dụng partials."
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
        "1. Khóa bi quan (`lockForUpdate`): Ngăn ngừa 100% xung đột bán vé đồng thời (Race Condition).\n"
        "2. Tự động thu hồi vé: Đơn hàng quá 15 phút chưa thanh toán được tự động hủy và hoàn trả vé vào kho (`orders:expire-pending`).\n"
        "3. Soát vé 5 bước chặt chẽ: `TicketCheckinService` ngăn chặn vé giả, vé chưa trả tiền, vé bị hủy hoặc vé của sự kiện khác.\n"
        "4. Bắt lỗi nghiệp vụ: Lớp ngoại lệ tùy biến `BusinessException` hiển thị thông báo lỗi thân thiện thay vì làm sập ứng dụng (500 Error)."
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
        ("Bộ kiểm thử tự động:", "37/37 Unit & Feature Tests Passed (104 Assertions, 0 Failures)"),
        ("Tiêu chuẩn bảo mật:", "CSRF Token, Role/Active Middleware, Policies chống IDOR"),
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
        ["Kiểm thử", "Kiểm tra dữ liệu đầu vào và thông báo lỗi rõ ràng", "100%", "11 FormRequests, hiển thị lỗi dưới từng input form"],
        ["Kiểm thử", "Xử lý lỗi: trống, sai định dạng, trùng mã, không tìm thấy", "100%", "Unique validation, ModelNotFound 404, BusinessException"],
        ["Bảo mật", "Áp dụng CSRF, middleware, auth, phân quyền", "100%", "100% @csrf, CheckRole, CheckActive, EventPolicy, TicketTypePolicy, OrderPolicy"],
        ["Kiểm thử", "Minh chứng kiểm thử chức năng", "100%", "37 Unit & Feature tests passed (104 assertions), docs/testing/*"],
        ["Triển khai", "Project chạy được trên môi trường Laravel chuẩn", "100%", "PHP 8.5.9, Laravel 13, zero-node build CDN"],
        ["Triển khai", "Database, migration, seed/script dữ liệu mẫu", "100%", "10 migrations (7 nghiệp vụ), DatabaseSeeder nạp 8 users, 4 venues, 8 events, 4 orders"],
        ["Triển khai", "Hướng dẫn cài đặt và chạy hệ thống", "100%", "README.md hướng dẫn Windows, Linux, macOS, switch MySQL"],
        ["Triển khai", "Mã nguồn tổ chức rõ, nộp đầy đủ, không thiếu file quan trọng", "100%", "Cây thư mục chuẩn Laravel, .env, .env.example, vendor/, database.sqlite"],
    ]
    add_styled_table(doc, headers, data, col_widths=[Inches(1.0), Inches(2.7), Inches(0.8), Inches(2.2)])

    # Section 4: Testing & Security
    h1 = doc.add_heading("MỤC 4: KIỂM THỬ & BẢO MẬT HỆ THỐNG", level=1)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    h2 = doc.add_heading("4.1. Kiểm Tra Dữ Liệu Đầu Vào & Thông Báo Lỗi Rõ Ràng", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Mọi dữ liệu gửi lên từ người dùng qua phương thức POST/PUT đều được kiểm tra nghiêm ngặt:\n"
        "• Kiểm tra dữ liệu tại Form Request trước khi vào Controller.\n"
        "• Khi có lỗi, hệ thống tự động redirect về form cũ kèm dữ liệu nhập trước đó (`old()`) và danh sách lỗi (`$errors`).\n"
        "• Tầng View hiển thị thông báo lỗi chi tiết ngay dưới từng trường nhập liệu bằng lớp CSS `.is-invalid` và `.invalid-feedback` của Bootstrap 5, giúp người dùng dễ dàng nhận diện và sửa lỗi."
    )

    h2 = doc.add_heading("4.2. Xử Lý Các Trường Hợp Ngoại Lệ & Lỗi Thường Gặp", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "• Lỗi để trống / sai định dạng: Bắt buộc thông qua các rules `required`, `email`, `numeric`, `date`, `min`, `max`.\n"
        "• Lỗi trùng lặp dữ liệu: Rule `unique:users,email` chặn trùng email đăng ký; slug sự kiện tự động thêm hậu tố số tăng dần nếu trùng tiêu đề; mã đơn `ORD-` và mã vé `TKT-` sinh ngẫu nhiên và kiểm tra tồn tại qua vòng lặp `while`.\n"
        "• Lỗi không tìm thấy dữ liệu: Route Model Binding tự động ném ngoại lệ `ModelNotFoundException` và chuyển hướng đến trang lỗi 404 thân thiện (`resources/views/errors/404.blade.php`).\n"
        "• Lỗi vi phạm ràng buộc nghiệp vụ: Được ném thông qua `BusinessException` (ví dụ: mua quá số lượng vé khả dụng, đơn hàng đã hết hạn) và hiển thị thông báo flash màu đỏ trên giao diện."
    )

    h2 = doc.add_heading("4.3. Bảo Mật Toàn Diện: CSRF, Middleware, Auth & Phân Quyền", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "• Bảo vệ CSRF: 100% các thẻ `<form>` thực hiện hành động POST, PUT, DELETE đều nhúng directive `@csrf` để chống tấn công Cross-Site Request Forgery.\n"
        "• Middleware tầng Route:\n"
        "  - `CheckRole`: Ngăn chặn khách hàng truy cập vào `/organizer/*` hoặc `/admin/*` (trả về lỗi `403 Forbidden`).\n"
        "  - `CheckActive`: Kiểm tra ngay lập tức trạng thái tài khoản. Nếu bị Admin khóa, hệ thống tự động hủy phiên và đẩy về màn hình đăng nhập.\n"
        "• Policies tầng Resource (Chống tấn công IDOR): `EventPolicy` và `OrderPolicy` đảm bảo mỗi người dùng chỉ thao tác đúng trên tài nguyên thuộc quyền sở hữu của mình."
    )

    h2 = doc.add_heading("4.4. Minh Chứng Kiểm Thử Chức Năng (37 Unit & Feature Tests)", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Dự án được trang bị bộ kiểm thử tự động hoàn chỉnh gồm 37 ca kiểm thử (2 Unit + 35 Feature), "
        "chạy thực tế qua lệnh `php artisan test` và đạt tỉ lệ thành công 100% (104 assertions, 0 failures):"
    )

    test_summary_headers = ["Nhóm Kiểm Thử (Test Class)", "Số Test", "Số Assertions", "Mục Tiêu Kiểm Định"]
    test_summary_data = [
        ["DomainModelsTest (Unit)", "2 tests", "9 assertions", "Accessor remaining của TicketType, role helpers & đối chiếu Enum"],
        ["AuthTest", "6 tests", "24 assertions", "Đăng ký, đăng nhập đúng/sai, chặn tài khoản bị khóa, profile, đăng xuất"],
        ["AuthorizationTest", "5 tests", "8 assertions", "Chặn trái quyền Admin/Organizer, bảo vệ IDOR Event/Order, chặn admin tự khóa/hạ quyền"],
        ["StorefrontTest", "4 tests", "9 assertions", "Duyệt sự kiện công khai, tìm kiếm từ khóa, lọc danh mục/thành phố, 404 bản nháp"],
        ["OrderAndPaymentTest", "9 tests", "26 assertions", "Đặt vé giữ chỗ 15p, giới hạn vé/tồn kho, thanh toán giả lập, hủy đơn, quá hạn command & pay"],
        ["CheckinTest", "5 tests", "9 assertions", "Soát vé hợp lệ, không tồn tại, chưa trả tiền, đã dùng, thuộc sự kiện Organizer khác"],
        ["AdminManagementTest", "6 tests", "19 assertions", "Duyệt/từ chối/hủy sự kiện, chặn xóa danh mục có sự kiện, khóa/mở user, dashboard & CRUD"],
    ]
    add_styled_table(doc, test_summary_headers, test_summary_data, col_widths=[Inches(1.8), Inches(0.9), Inches(1.3), Inches(2.7)])

    # Section 5: Deployment
    h1 = doc.add_heading("MỤC 5: TRIỂN KHAI DỰ ÁN", level=1)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    h2 = doc.add_heading("5.1. Môi Trường Chạy Chuẩn & Triển Khai Không Cần Node.js", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "• Yêu cầu môi trường: PHP >= 8.3 (đã kiểm thử trên PHP 8.5.9), Composer 2.x, SQLite 3 hoặc MySQL 8.0+.\n"
        "• Triển khai Zero-Node: Toàn bộ CSS và Javascript được nhúng qua mạng phân phối nội dung CDN (Bootstrap 5.3 và Bootstrap Icons), "
        "loại bỏ hoàn toàn bước biên dịch tài nguyên nặng nề (`npm install` hay `npm run build`), cho phép khởi chạy ngay lập tức trên bất kỳ máy tính nào."
    )

    h2 = doc.add_heading("5.2. Cơ Sở Dữ Liệu & Kịch Bản Nạp Dữ Liệu Mẫu (Seed Data)", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Hệ thống đi kèm trọn bộ 10 migration (7 nghiệp vụ) và 5 seeder classes nạp sẵn dữ liệu phong phú để demo ngay:\n"
        "• Lệnh khởi tạo một bước: `php artisan migrate:fresh --seed --force` và `php artisan storage:link`\n"
        "• Dữ liệu mẫu khởi tạo: 8 tài khoản (1 Admin, 2 Organizer, 5 Customer dùng email @example.com), 5 danh mục, 4 địa điểm tổ chức, 8 sự kiện (5 published, 1 draft, 1 pending, 1 cancelled) với 16 loại vé, 4 đơn hàng và 3 vé điện tử."
    )

    h2 = doc.add_heading("5.3. Hướng Dẫn Cài Đặt & Chuyển Đổi Hệ Quản Trị CSDL", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Tệp `README.md` hướng dẫn chi tiết từng câu lệnh khởi chạy trên Windows (PowerShell/CMD), Linux và macOS. "
        "Ngoài ra, dự án cung cấp tài liệu chi tiết cách chuyển đổi từ SQLite sang MySQL (XAMPP/Laragon) chỉ bằng việc thay đổi biến môi trường trong `.env`:\n"
        "`DB_CONNECTION=mysql`\n"
        "`DB_HOST=127.0.0.1`\n"
        "`DB_DATABASE=event_ticket`"
    )

    h2 = doc.add_heading("5.4. Tổ Chức Mã Nguồn & Tính Đầy Đủ", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Dự án được bàn giao đầy đủ tệp mã nguồn, cấu hình `.env` / `.env.example`, thư viện `vendor/`, CSDL SQLite đã seed, "
        "liên kết `public/storage`, bộ kiểm thử tự động và toàn bộ tài liệu thiết kế/sơ đồ."
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
        ("Chuyên đề báo cáo:", "Mục 6: Làm Việc Nhóm (PLO6) + Mục 7: Trình Bày & Bảo Vệ (PLO1, PLO2, PLO3)"),
        ("Quản lý tiến độ:", "Phân công 3 thành viên theo docs/WORKLOG.md & docs/PROGRESS.md"),
        ("Kịch bản Demo & Q&A:", "Kịch bản demo 8-10 phút cho 3 người & 15 câu hỏi phản biện"),
    ]
    doc = create_base_document(
        "BÁO CÁO NGHIỆM THU KỸ THUẬT PHẦN MỀM",
        "HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN\n(EVENT TICKET MANAGEMENT SYSTEM)",
        "BÁO CÁO MỤC 6 & 7: LÀM VIỆC NHÓM & BẢO VỆ ĐỒ ÁN (PLO6, PLO1, PLO2, PLO3)\n(PHÂN CÔNG TIẾN ĐỘ, NHẬT KÝ LÀM VIỆC, KỊCH BẢN DEMO & PHẢN BIỆN)",
        meta
    )

    h1 = doc.add_heading("TỔNG HỢP TIÊU CHÍ ĐÁNH GIÁ MỤC 6 & 7 (PLO6, PLO1, PLO2, PLO3)", level=1)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)
    
    headers = ["Nhóm", "Nội Dung Tiêu Chí Đánh Giá", "Trạng Thái", "Minh Chứng & Lưu Ý"]
    data = [
        ["Nhóm", "Phân công nhiệm vụ rõ ràng giữa thành viên", "Hoàn thành", "Bảng phân chia 3 thành viên Nhóm 8 - Lớp 68PM2 trong docs/WORKLOG.md"],
        ["Nhóm", "Các thành viên có đóng góp phù hợp", "Hoàn thành", "Phân chia đồng đều (34% / 33% / 33%) gắn trách nhiệm từng phân hệ"],
        ["Nhóm", "Nhật ký làm việc, commit Git hoặc minh chứng tiến độ", "Có nhật ký MD", "docs/WORKLOG.md, docs/PROGRESS.md (khuyến nghị git init & push repo thật)"],
        ["Nhóm", "Phối hợp, giải quyết vấn đề, hoàn thành đúng hạn", "Hoàn thành", "Giải quyết lockForUpdate, giữ chỗ 15p + scheduler, chống N+1, soft delete"],
        ["Bảo vệ", "Demo sản phẩm và trả lời câu hỏi về phân tích, thiết kế, code", "Sẵn sàng", "docs/DEMO_SCRIPT.md (8-10 phút demo theo 3 thành viên + 15 câu Q&A)"],
    ]
    add_styled_table(doc, headers, data, col_widths=[Inches(0.9), Inches(2.7), Inches(0.9), Inches(2.2)])

    # Section 6: Teamwork
    h1 = doc.add_heading("MỤC 6: LÀM VIỆC NHÓM & QUẢN LÝ TIẾN ĐỘ (PLO6)", level=1)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    h2 = doc.add_heading("6.1. Phân Công Nhiệm Vụ Rõ Ràng Giữa Các Thành Viên (Nhóm 8 - Lớp 68PM2)", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Nhóm 8 (Lớp 68PM2) gồm 3 thành viên được phân công nhiệm vụ cụ thể, đồng bộ với docs/WORKLOG.md:"
    )

    team_headers = ["Thành Viên & MSSV", "Vai Trò Trong Nhóm", "Mảng Phụ Trách", "Tỷ Lệ"]
    team_data = [
        ["Hồ Đăng Quang\n(MSSV: 0021568)", "Trưởng nhóm / Architect & Storefront", "Phân tích yêu cầu (SRS), SPEC.md, Layouts (app/panel), Storefront lọc/tìm, OrderService (lockForUpdate), Thanh toán giả lập, Vé của tôi, Demo Chặng 1", "34%"],
        ["Tống Xuân Định\n(MSSV: 0008268)", "Backend, DB & Organizer Dev", "Thiết kế CSDL 3NF, 10 Migrations, 8 Models, Seeders, Auth & Profile, Middleware, 3 Policies, Phân hệ Organizer & Check-in 5 bước, Scheduler, Demo Chặng 2", "33%"],
        ["Nguyễn Hữu Quý\n(MSSV: 0022968)", "Admin Dev, QA & Architecture", "Phân hệ Admin (Dashboard, Danh mục, Địa điểm, Người dùng, Duyệt sự kiện, Đơn hàng), 37 Unit/Feature Tests, test-cases.md, 8 Sơ đồ PlantUML + PNG, Báo cáo, Demo Chặng 3", "33%"],
    ]
    add_styled_table(doc, team_headers, team_data, col_widths=[Inches(1.5), Inches(1.5), Inches(3.0), Inches(0.7)])

    h2 = doc.add_heading("6.2. Nhật Ký Tiến Độ & Lưu Ý Quản Lý Mã Nguồn Git", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Toàn bộ tiến độ phát triển qua 7 Phase (Phase 0 đến Phase 6) được ghi nhận chi tiết tại [docs/WORKLOG.md] và [docs/PROGRESS.md].\n"
        "Lưu ý quan trọng: Trong thư mục dự án hiện tại chưa khởi tạo thư mục `.git`. Để đạt điểm tối đa cho Tiêu chí 6.3, "
        "nhóm cần thực hiện `git init`, đẩy mã nguồn lên GitHub/GitLab và phân công các thành viên tạo commit theo phân hệ phụ trách."
    )

    h2 = doc.add_heading("6.3. Phối Hợp & Giải Quyết Các Vấn Đề Kỹ Thuật Phức Tạp", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Trong quá trình phát triển và rà soát kiểm thử, các vấn đề kỹ thuật trọng yếu đã được xử lý triệt để:\n"
        "1. Chống Race Condition khi mua vé: Áp dụng Khóa bi quan (`lockForUpdate`) trong `DB::transaction()` tại `OrderService`.\n"
        "2. Thu hồi vé giữ chỗ quá hạn 15 phút: Kết hợp Lazy Check khi xem/thanh toán đơn quá hạn và Scheduler `orders:expire-pending` chạy mỗi phút trong `routes/console.php`.\n"
        "3. Tối ưu truy vấn chống N+1: Áp dụng Eager Loading `with()`, `withCount()`, `withMin()`, `withSum()`.\n"
        "4. Toàn vẹn dữ liệu & đồng bộ giao diện: `RESTRICT` cho Danh mục/Địa điểm, `SoftDeletes` cho Sự kiện, đồng bộ CSS giữa `layouts/app` và `layouts/panel`."
    )

    # Section 7: Presentation
    h1 = doc.add_heading("MỤC 7: TRÌNH BÀY & BẢO VỆ ĐỒ ÁN (PLO1, PLO2, PLO3)", level=1)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    h2 = doc.add_heading("7.1. Kịch Bản Trình Diễn Sản Phẩm (Demo Script 8-10 Phút)", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Nhóm đã chuẩn bị kịch bản demo trực tiếp theo từng phút (chi tiết tại [docs/DEMO_SCRIPT.md]) với tài khoản demo thống nhất (`admin@example.com`, `organizer1@example.com`, `customer1@example.com`, mật khẩu `password`):\n"
        "• Chặng 1 (~3.5 phút): Duyệt/tìm kiếm/lọc sự kiện phía Storefront -> Đặt vé giữ chỗ 15 phút -> Thanh toán giả lập -> Xem thẻ vé điện tử mã monospace (TKT-XXXXXXXXXX) & in vé.\n"
        "• Chặng 2 (~3.5 phút): Đăng nhập Ban tổ chức, xem Dashboard doanh thu, tạo sự kiện mới, thêm hạng vé, gửi duyệt và thao tác soát vé Check-in 5 bước.\n"
        "• Chặng 3 (~2 phút): Đăng nhập Quản trị viên (Admin), duyệt sự kiện, kiểm tra chặn xóa danh mục có sự kiện, khóa/mở tài khoản người dùng và trình chiếu kết quả 37/37 tests pass."
    )

    h2 = doc.add_heading("7.2. Bộ 15 Câu Hỏi & Đáp Án Phản Biện Chuyên Sâu Của Hội Đồng", level=2)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)
    doc.add_paragraph(
        "Nhóm đã chuẩn bị sẵn câu trả lời chuyên sâu bám sát mã nguồn dự án cho 15 câu hỏi phản biện thường gặp của Giảng viên chấm thi:\n"
        "1. Hệ thống giải quyết bài toán Race Condition khi mở bán vé như thế nào? -> Trả lời bám sát `DB::transaction()` và `TicketType::lockForUpdate()`.\n"
        "2. Cơ chế giữ chỗ 15 phút hoạt động ra sao nếu khách không trả tiền? -> Trả lời bám sát Lazy Check và command `orders:expire-pending`.\n"
        "3. Làm sao để Ban tổ chức không xem được sự kiện của đơn vị khác? -> Trả lời bám sát `EventPolicy` và kiểm tra `$user->id === $event->organizer_id`.\n"
        "4. Middleware CheckActive bảo vệ hệ thống như thế nào khi tài khoản bị khóa? -> Trả lời bám sát cơ chế kiểm tra `is_active` và tự động `Auth::logout()`.\n"
        "5. Quy trình soát vé Check-in kiểm tra những điều kiện gì? -> Trả lời 5 bước xác thực của `TicketCheckinService`.\n"
        "6. Tại sao không dùng npm build trong dự án này? -> Trả lời về tính linh hoạt, tốc độ triển khai và CDN Bootstrap 5.3.\n"
        "*(Chi tiết trọn vẹn 15 câu hỏi và câu trả lời mẫu được lưu trữ tại file docs/DEMO_SCRIPT.md)*."
    )

    doc.save(output_path)
    print(f"Report 3 created at {output_path}")

if __name__ == "__main__":
    out_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "docs")
    generate_report_1(os.path.join(out_dir, "04_bao_cao_xay_dung_chuc_nang.docx"))
    generate_report_2(os.path.join(out_dir, "05_bao_cao_kiem_thu_bao_mat_trien_khai.docx"))
    generate_report_3(os.path.join(out_dir, "06_bao_cao_lam_viec_nhom_va_bao_ve.docx"))
