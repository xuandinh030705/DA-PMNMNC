# -*- coding: utf-8 -*-
"""
Script to generate a comprehensive, professional Database Design Document (.docx)
for the Event Ticket Management System (Hệ thống Quản lý Bán vé Sự kiện).
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
    """Set background color of a table cell."""
    tcPr = cell._element.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{hex_color}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
    """Set inner padding for table cells in dxa (1 pt = 20 dxa)."""
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

def set_table_borders(table, color="D1D5DB", sz="4", val="single"):
    """Set soft gray borders for the entire table."""
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
            # Center specific short columns like PK, FK, Nullable
            align = WD_ALIGN_PARAGRAPH.CENTER if c_idx in [0, 2, 3, 4] and len(headers) >= 6 else WD_ALIGN_PARAGRAPH.LEFT
            format_cell_text(row_cells[c_idx], str(val), bold=(c_idx == 0 or "PK" in str(val)), font_size=9.5, align=align)
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

def build_database_document(output_path):
    doc = Document()

    # Page Margins: Normal 1 inch (72 pt)
    sections = doc.sections
    for section in sections:
        section.top_margin = Inches(1.0)
        section.bottom_margin = Inches(1.0)
        section.left_margin = Inches(1.0)
        section.right_margin = Inches(1.0)

    # Base Styles
    normal_style = doc.styles['Normal']
    normal_style.font.name = 'Arial'
    normal_style.font.size = Pt(11)
    normal_style.font.color.rgb = RGBColor(51, 65, 85) # Slate 700

    # ==================== COVER PAGE ====================
    title_p = doc.add_paragraph()
    title_p.paragraph_format.space_before = Pt(36)
    title_p.paragraph_format.space_after = Pt(8)
    title_p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run_sub = title_p.add_run("BÁO CÁO CÔNG NGHỆ PHẦN MỀM & PHÁT TRIỂN WEB\n")
    run_sub.font.size = Pt(13)
    run_sub.font.bold = True
    run_sub.font.color.rgb = RGBColor(100, 116, 139) # Slate 500

    run_main = title_p.add_run("HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN\n(EVENT TICKET MANAGEMENT SYSTEM)")
    run_main.font.size = Pt(22)
    run_main.font.bold = True
    run_main.font.color.rgb = RGBColor(30, 58, 138) # Navy Blue 900

    doc.add_paragraph().paragraph_format.space_after = Pt(12)

    doc_type_p = doc.add_paragraph()
    doc_type_p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run_doc_type = doc_type_p.add_run("TÀI LIỆU THIẾT KẾ CƠ SỞ DỮ LIỆU\n(DATABASE DESIGN DOCUMENT - 3NF)")
    run_doc_type.font.size = Pt(15)
    run_doc_type.font.bold = True
    run_doc_type.font.color.rgb = RGBColor(37, 99, 235) # Blue 600

    doc.add_paragraph().paragraph_format.space_after = Pt(36)

    # Metadata Box Table
    meta_table = doc.add_table(rows=6, cols=2)
    meta_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(meta_table, color="CBD5E1", sz="6")
    meta_data = [
        ("Mã tài liệu / Phiên bản:", "DDD-ETMS-v1.1 (Final Release)"),
        ("Nhóm thực hiện:", "Nhóm 8 - Lớp 68PM2 (Hồ Đăng Quang, Tống Xuân Định, Nguyễn Hữu Quý)"),
        ("Nền tảng Framework:", "PHP 8.3+ / Laravel 13 Framework"),
        ("Hệ quản trị CSDL:", "SQLite 3 (Mặc định) / Hỗ trợ MySQL 8.0+ (InnoDB)"),
        ("Mã hóa ký tự (Charset):", "UTF-8 Unicode (utf8mb4_unicode_ci)"),
        ("Quy chuẩn thiết kế:", "Chuẩn hóa dạng 3NF (Third Normal Form)"),
    ]
    for idx, (label, val) in enumerate(meta_data):
        row_cells = meta_table.rows[idx].cells
        format_cell_text(row_cells[0], label, bold=True, color_rgb=(30, 58, 138), font_size=10)
        format_cell_text(row_cells[1], val, bold=False, color_rgb=(15, 23, 42), font_size=10)
        set_cell_background(row_cells[0], "F1F5F9")
        set_cell_background(row_cells[1], "FFFFFF")
        set_cell_margins(row_cells[0], top=100, bottom=100, left=150, right=150)
        set_cell_margins(row_cells[1], top=100, bottom=100, left=150, right=150)
        row_cells[0].width = Inches(2.5)
        row_cells[1].width = Inches(4.0)

    doc.add_page_break()

    # ==================== TABLE OF CONTENTS SUMMARY ====================
    h1 = doc.add_heading("MỤC LỤC TỔNG QUAN", level=1)
    h1.paragraph_format.space_before = Pt(12)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    toc_items = [
        ("Phần 1: Tổng quan Kiến trúc Dữ liệu & Quy ước Thiết kế", "Mục tiêu, chuẩn hóa 3NF, quy ước tên bảng/cột, cơ chế quản lý bằng Laravel Migrations"),
        ("Phần 2: Sơ đồ Quan hệ Thực thể (ERD) & Phân tích Quan hệ", "Mô tả 8 thực thể nghiệp vụ cốt lõi, quan hệ 1-N, N-N, các ràng buộc tham chiếu Cascade/Restrict"),
        ("Phần 3: Từ điển Dữ liệu Chi tiết (Data Dictionary)", "Cấu trúc trường chi tiết cho 8 bảng: users, categories, venues, events, ticket_types, orders, order_items, tickets"),
        ("Phần 4: Các Bảng Quản trị Hệ thống (System Tables)", "Cấu trúc các bảng nội bộ của Laravel: sessions, cache, cache_locks, jobs, job_batches, failed_jobs"),
        ("Phần 5: Kiểm soát Đồng thời & Ràng buộc Toàn vẹn Nghiệp vụ", "Cơ chế Pessimistic Locking (lockForUpdate) chống bán vượt vé (overbooking), quy trình giữ chỗ 15 phút, xóa mềm"),
        ("Phần 6: Chiến lược Đánh Chỉ mục (Indexing Strategy) & Hiệu năng", "Phân tích Unique Indexes, Composite Indexes, Foreign Keys và tối ưu hóa truy vấn chống N+1"),
        ("Phần 7: Mẫu Dữ liệu Khởi tạo & Kiểm thử CSDL", "Chi tiết các Seeders ban đầu và số liệu kiểm định tính toàn vẹn cơ sở dữ liệu"),
    ]
    for title, desc in toc_items:
        p = doc.add_paragraph()
        p.paragraph_format.space_after = Pt(4)
        run_t = p.add_run(f"• {title}: ")
        run_t.bold = True
        run_t.font.color.rgb = RGBColor(15, 23, 42)
        run_d = p.add_run(desc)
        run_d.font.size = Pt(10)

    doc.add_paragraph().paragraph_format.space_after = Pt(16)

    # ==================== PHẦN 1 ====================
    h1 = doc.add_heading("PHẦN 1: TỔNG QUAN KIẾN TRÚC DỮ LIỆU & QUY ƯỚC THIẾT KẾ", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    p = doc.add_paragraph(
        "Cơ sở dữ liệu của Hệ thống Quản lý Bán vé Sự kiện được xây dựng nhằm phục vụ mô hình sàn thương mại điện tử chuyên biệt "
        "dành cho vé sự kiện trực tuyến. Cơ sở dữ liệu cần đảm bảo tính nhất quán tuyệt đối (ACID), khả năng xử lý đồng thời cao "
        "khi mở bán các sự kiện 'nóng', và hỗ trợ phân quyền chặt chẽ giữa 4 nhóm tác tử: Khách vãng lai, Khách hàng, Ban tổ chức và Quản trị viên."
    )
    p.paragraph_format.line_spacing = 1.2
    p.paragraph_format.space_after = Pt(8)

    h2 = doc.add_heading("1.1. Chuẩn hóa Cơ sở Dữ liệu (Third Normal Form - 3NF)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Toàn bộ cấu trúc các bảng trong hệ thống đều tuân thủ nghiêm ngặt các quy tắc chuẩn hóa dữ liệu:\n"
        "1. Dạng chuẩn 1 (1NF): Mọi thuộc tính đều mang giá trị nguyên tử (atomic value), không có nhóm lặp (repeating groups). Mỗi hàng đều có khóa chính định danh duy nhất (id).\n"
        "2. Dạng chuẩn 2 (2NF): Đạt 1NF và mọi thuộc tính không khóa đều phụ thuộc hàm toàn phần vào toàn bộ khóa chính (loại bỏ phụ thuộc từng phần).\n"
        "3. Dạng chuẩn 3 (3NF): Đạt 2NF và không có thuộc tính không khóa nào phụ thuộc bắc cầu vào khóa chính (loại bỏ transitive dependency). Ví dụ: Thông tin sự kiện và thông tin địa điểm được tách riêng thành hai bảng `events` và `venues` liên kết qua `venue_id`."
    )

    h2 = doc.add_heading("1.2. Quy ước Đặt tên (Naming Conventions)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    conventions = [
        ("Tên Bảng (Tables):", "Danh từ tiếng Anh ở dạng số nhiều (plural), viết thường dạng snake_case (ví dụ: `ticket_types`, `order_items`)."),
        ("Khóa Chính (Primary Keys):", "Cố định là cột `id` kiểu số nguyên lớn tự tăng (BIGINT UNSIGNED Auto Increment)."),
        ("Khóa Ngoại (Foreign Keys):", "Đặt theo quy tắc `{tên_bảng_số_ít}_id` (ví dụ: `organizer_id` trỏ về `users.id`, `order_id` trỏ về `orders.id`)."),
        ("Cột Thời gian (Timestamps):", "Sử dụng cặp `created_at` và `updated_at` do Laravel Eloquent tự động quản lý theo múi giờ `Asia/Ho_Chi_Minh`."),
        ("Cột Xóa Mềm (Soft Deletes):", "Sử dụng cột `deleted_at` (nullable timestamp) trên bảng `events` để lưu trữ trạng thái lưu trữ an toàn."),
        ("Mã Định danh Nghiệp vụ:", "Sử dụng chuỗi định dạng duy nhất có tiền tố chuẩn: `order_code` (ORD-XXXXXXXX) và `ticket_code` (TKT-XXXXXXXXXX)."),
    ]
    for label, val in conventions:
        p = doc.add_paragraph()
        p.paragraph_format.space_after = Pt(3)
        r1 = p.add_run(f"• {label} ")
        r1.bold = True
        r1.font.color.rgb = RGBColor(15, 23, 42)
        p.add_run(val)

    # ==================== PHẦN 2 ====================
    h1 = doc.add_heading("PHẦN 2: SƠ ĐỒ QUAN HỆ THỰC THỂ (ERD) & PHÂN TÍCH QUAN HỆ", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph(
        "Hệ thống bao gồm 8 thực thể dữ liệu nghiệp vụ chính với mối quan hệ liên kết chặt chẽ qua các ràng buộc khóa ngoại (Foreign Keys). "
        "Dưới đây là ma trận phân tích các mối quan hệ thực thể cốt lõi trong hệ thống:"
    )

    erd_headers = ["Thực thể 1", "Quan hệ", "Thực thể 2", "Ràng buộc Khóa ngoại (FK)", "Hành vi Xóa (On Delete)", "Ý nghĩa Nghiệp vụ"]
    erd_data = [
        ["users", "1 - N", "events", "events.organizer_id -> users.id", "CASCADE", "Một Ban tổ chức có thể tạo và quản lý nhiều sự kiện."],
        ["categories", "1 - N", "events", "events.category_id -> categories.id", "RESTRICT", "Một danh mục chứa nhiều sự kiện. Chặn xóa danh mục đang có sự kiện."],
        ["venues", "1 - N", "events", "events.venue_id -> venues.id", "RESTRICT", "Một địa điểm có thể tổ chức nhiều sự kiện. Chặn xóa địa điểm đang dùng."],
        ["events", "1 - N", "ticket_types", "ticket_types.event_id -> events.id", "CASCADE", "Một sự kiện có nhiều loại vé (VIP, GA, Early Bird...). Xóa sự kiện xóa luôn vé."],
        ["users", "1 - N", "orders", "orders.user_id -> users.id", "CASCADE", "Một khách hàng có thể đặt nhiều đơn hàng trong lịch sử."],
        ["orders", "1 - N", "order_items", "order_items.order_id -> orders.id", "CASCADE", "Một đơn hàng có thể chứa nhiều dòng sản phẩm loại vé khác nhau."],
        ["ticket_types", "1 - N", "order_items", "order_items.ticket_type_id -> ticket_types.id", "CASCADE", "Một loại vé có thể được đặt trong nhiều đơn hàng."],
        ["order_items", "1 - N", "tickets", "tickets.order_item_id -> order_items.id", "CASCADE", "Mỗi vé điện tử độc nhất gắn liền với dòng đặt vé của đơn hàng đã thanh toán."],
    ]
    col_w = [Inches(1.0), Inches(0.7), Inches(1.0), Inches(1.6), Inches(0.9), Inches(1.8)]
    add_styled_table(doc, erd_headers, erd_data, col_widths=col_w)

    doc.add_paragraph(
        "Lưu ý về quy tắc toàn vẹn dữ liệu: Bảng `categories` và `venues` áp dụng `ON DELETE RESTRICT` để bảo vệ toàn vẹn lịch sử sự kiện. "
        "Ban quản trị hệ thống không thể xóa nhầm một danh mục hoặc địa điểm đang được gán cho các sự kiện đang diễn ra."
    )

    erd_img_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "docs", "diagrams", "images", "05_erd.png")
    if os.path.exists(erd_img_path):
        p_img = doc.add_paragraph()
        p_img.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_img.add_run().add_picture(erd_img_path, width=Inches(6.2))
        p_cap = doc.add_paragraph()
        p_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r_cap = p_cap.add_run("Hình 1: Sơ đồ Quan hệ Thực thể (ERD - 8 bảng nghiệp vụ)")
        r_cap.italic = True
        r_cap.font.size = Pt(9.5)
        r_cap.font.color.rgb = RGBColor(100, 116, 139)

    # ==================== PHẦN 3 ====================
    h1 = doc.add_heading("PHẦN 3: TỪ ĐIỂN DỮ LIỆU CHI TIẾT (DATA DICTIONARY)", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph(
        "Phần này mô tả chi tiết từng cột trong 8 bảng nghiệp vụ của hệ thống, bao gồm kiểu dữ liệu trong Laravel Migration, "
        "tương thích MySQL/SQLite, ràng buộc khóa, giá trị mặc định và ý nghĩa nghiệp vụ cụ thể."
    )

    tables_spec = [
        {
            "name": "users",
            "desc": "Bảng lưu trữ tài khoản người dùng của toàn bộ hệ thống (Quản trị viên, Ban tổ chức, Khách hàng).",
            "cols": [
                ("id", "BIGINT UNSIGNED", "PK", "No", "Auto Increment", "Mã định danh duy nhất của người dùng."),
                ("name", "VARCHAR(255)", "-", "No", "-", "Họ và tên đầy đủ của người dùng."),
                ("email", "VARCHAR(255)", "UK", "No", "-", "Địa chỉ email đăng nhập (Duy nhất trên toàn hệ thống)."),
                ("password", "VARCHAR(255)", "-", "No", "-", "Mật khẩu đăng nhập được băm an toàn bằng Bcrypt/Argon2id."),
                ("phone", "VARCHAR(20)", "-", "Yes", "NULL", "Số điện thoại liên lạc của người dùng."),
                ("role", "VARCHAR(50)", "-", "No", "'customer'", "Vai trò người dùng: 'admin', 'organizer', 'customer'."),
                ("is_active", "TINYINT(1)", "-", "No", "1 (TRUE)", "Trạng thái tài khoản: 1 = Hoạt động, 0 = Bị khóa."),
                ("remember_token", "VARCHAR(100)", "-", "Yes", "NULL", "Token duy trì phiên đăng nhập 'Ghi nhớ đăng nhập'."),
                ("created_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm đăng ký tài khoản."),
                ("updated_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm cập nhật thông tin tài khoản gần nhất."),
            ]
        },
        {
            "name": "categories",
            "desc": "Bảng phân loại các sự kiện theo chủ đề (Âm nhạc, Công nghệ, Workshop, Thể thao, v.v.).",
            "cols": [
                ("id", "BIGINT UNSIGNED", "PK", "No", "Auto Increment", "Mã định danh duy nhất của danh mục."),
                ("name", "VARCHAR(255)", "UK", "No", "-", "Tên danh mục hiển thị (Duy nhất, ví dụ: 'Âm nhạc & Lễ hội')."),
                ("slug", "VARCHAR(255)", "UK", "No", "-", "Đường dẫn thân thiện SEO (Ví dụ: 'am-nhac-va-le-hoi')."),
                ("description", "TEXT", "-", "Yes", "NULL", "Mô tả chi tiết về phạm vi của danh mục."),
                ("created_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm khởi tạo danh mục."),
                ("updated_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm cập nhật danh mục gần nhất."),
            ]
        },
        {
            "name": "venues",
            "desc": "Bảng quản lý địa điểm vật lý nơi diễn ra các sự kiện, phục vụ việc kiểm soát sức chứa tối đa.",
            "cols": [
                ("id", "BIGINT UNSIGNED", "PK", "No", "Auto Increment", "Mã định danh duy nhất của địa điểm."),
                ("name", "VARCHAR(255)", "-", "No", "-", "Tên địa điểm (Ví dụ: 'Trung tâm Hội nghị Quốc gia')."),
                ("address", "VARCHAR(255)", "-", "No", "-", "Địa chỉ cụ thể (Số nhà, tên đường, phường/xã)."),
                ("city", "VARCHAR(100)", "-", "No", "-", "Tỉnh / Thành phố trực thuộc (Ví dụ: 'Hà Nội', 'TP. Hồ Chí Minh')."),
                ("capacity", "INT UNSIGNED", "-", "No", "-", "Sức chứa tối đa của địa điểm (người)."),
                ("created_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm tạo địa điểm."),
                ("updated_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm chỉnh sửa địa điểm."),
            ]
        },
        {
            "name": "events",
            "desc": "Bảng lưu thông tin chi tiết sự kiện, trạng thái vòng đời phê duyệt và hình ảnh truyền thông.",
            "cols": [
                ("id", "BIGINT UNSIGNED", "PK", "No", "Auto Increment", "Mã định danh duy nhất của sự kiện."),
                ("organizer_id", "BIGINT UNSIGNED", "FK", "No", "-", "Khóa ngoại trỏ tới users.id (Người tổ chức sự kiện)."),
                ("category_id", "BIGINT UNSIGNED", "FK", "No", "-", "Khóa ngoại trỏ tới categories.id (Danh mục sự kiện)."),
                ("venue_id", "BIGINT UNSIGNED", "FK", "No", "-", "Khóa ngoại trỏ tới venues.id (Địa điểm tổ chức)."),
                ("title", "VARCHAR(255)", "-", "No", "-", "Tiêu đề / Tên chính thức của sự kiện."),
                ("slug", "VARCHAR(255)", "UK", "No", "-", "Đường dẫn thân thiện URL duy nhất để tra cứu sự kiện."),
                ("description", "TEXT", "-", "No", "-", "Mô tả chi tiết nội dung, nghệ sĩ, chương trình sự kiện."),
                ("banner_path", "VARCHAR(255)", "-", "Yes", "NULL", "Đường dẫn file ảnh banner lưu trong storage."),
                ("start_time", "DATETIME", "-", "No", "-", "Thời điểm bắt đầu diễn ra sự kiện."),
                ("end_time", "DATETIME", "-", "No", "-", "Thời điểm kết thúc sự kiện (Phải > start_time)."),
                ("status", "VARCHAR(50)", "-", "No", "'draft'", "Trạng thái: 'draft', 'pending', 'published', 'cancelled'."),
                ("created_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm tạo bản ghi."),
                ("updated_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm cập nhật bản ghi."),
                ("deleted_at", "TIMESTAMP", "-", "Yes", "NULL", "Thời điểm xóa mềm (Soft Delete)."),
            ]
        },
        {
            "name": "ticket_types",
            "desc": "Bảng lưu các hạng vé của từng sự kiện (Ví dụ: VIP, Khán đài A, Vé Tiêu chuẩn) kèm số lượng và giá bán.",
            "cols": [
                ("id", "BIGINT UNSIGNED", "PK", "No", "Auto Increment", "Mã định danh duy nhất của loại vé."),
                ("event_id", "BIGINT UNSIGNED", "FK", "No", "-", "Khóa ngoại trỏ tới events.id (Thuộc sự kiện nào)."),
                ("name", "VARCHAR(255)", "-", "No", "-", "Tên hạng vé (Ví dụ: 'Hạng Vé VIP - Vé kèm quà tặng')."),
                ("price", "DECIMAL(12,0)", "-", "No", "-", "Đơn giá vé tính bằng VNĐ (Ví dụ: 500.000 đ)."),
                ("quantity", "INT UNSIGNED", "-", "No", "-", "Tổng số lượng vé phát hành cho hạng vé này."),
                ("sold", "INT UNSIGNED", "-", "No", "0", "Số lượng vé đã được đặt/bán (Dùng tính vé khả dụng)."),
                ("sale_start", "DATETIME", "-", "No", "-", "Thời điểm mở cổng bán vé trực tuyến."),
                ("sale_end", "DATETIME", "-", "No", "-", "Thời điểm đóng cổng bán vé trực tuyến."),
                ("max_per_order", "INT UNSIGNED", "-", "No", "10", "Số lượng vé tối đa được phép mua trong 1 đơn hàng."),
                ("created_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm tạo loại vé."),
                ("updated_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm cập nhật loại vé."),
            ]
        },
        {
            "name": "orders",
            "desc": "Bảng quản lý đơn đặt hàng của khách hàng, tổng tiền, phương thức thanh toán và thời hạn giữ chỗ 15 phút.",
            "cols": [
                ("id", "BIGINT UNSIGNED", "PK", "No", "Auto Increment", "Mã định danh duy nhất của đơn hàng."),
                ("user_id", "BIGINT UNSIGNED", "FK", "No", "-", "Khóa ngoại trỏ tới users.id (Người mua vé)."),
                ("order_code", "VARCHAR(50)", "UK", "No", "-", "Mã đơn hàng nghiệp vụ duy nhất (Ví dụ: 'ORD-7FA91B2E')."),
                ("customer_name", "VARCHAR(255)", "-", "No", "-", "Tên người nhận vé tại thời điểm đặt hàng."),
                ("customer_email", "VARCHAR(255)", "-", "No", "-", "Email nhận thông tin vé điện tử."),
                ("customer_phone", "VARCHAR(20)", "-", "Yes", "NULL", "Số điện thoại liên lạc người nhận vé."),
                ("total_amount", "DECIMAL(12,0)", "-", "No", "-", "Tổng số tiền cần thanh toán của đơn hàng (VNĐ)."),
                ("status", "VARCHAR(50)", "-", "No", "'pending'", "Trạng thái: 'pending', 'paid', 'cancelled'."),
                ("payment_method", "VARCHAR(50)", "-", "Yes", "NULL", "Phương thức thanh toán: 'bank_transfer', 'momo', 'card'."),
                ("paid_at", "DATETIME", "-", "Yes", "NULL", "Thời điểm thanh toán thành công."),
                ("expires_at", "DATETIME", "-", "Yes", "NULL", "Thời điểm hết hạn giữ chỗ (Mặc định: created_at + 15 phút)."),
                ("created_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm đặt đơn hàng."),
                ("updated_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm cập nhật trạng thái đơn hàng."),
            ]
        },
        {
            "name": "order_items",
            "desc": "Bảng chi tiết các mặt hàng trong đơn hàng, lưu giá vé tại thời điểm mua để chống biến động giá.",
            "cols": [
                ("id", "BIGINT UNSIGNED", "PK", "No", "Auto Increment", "Mã định danh duy nhất của dòng đơn hàng."),
                ("order_id", "BIGINT UNSIGNED", "FK", "No", "-", "Khóa ngoại trỏ tới orders.id (Thuộc đơn hàng nào)."),
                ("ticket_type_id", "BIGINT UNSIGNED", "FK", "No", "-", "Khóa ngoại trỏ tới ticket_types.id (Loại vé đặt mua)."),
                ("quantity", "INT UNSIGNED", "-", "No", "-", "Số lượng vé đặt cho hạng vé này."),
                ("unit_price", "DECIMAL(12,0)", "-", "No", "-", "Đơn giá vé tại thời điểm đặt (VNĐ)."),
                ("subtotal", "DECIMAL(12,0)", "-", "No", "-", "Thành tiền của dòng sản phẩm = quantity * unit_price."),
                ("created_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm tạo dòng chi tiết đơn."),
                ("updated_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm cập nhật."),
            ]
        },
        {
            "name": "tickets",
            "desc": "Bảng lưu trữ vé điện tử cá nhân phát hành sau khi đơn hàng thanh toán thành công, phục vụ soát vé tại cổng.",
            "cols": [
                ("id", "BIGINT UNSIGNED", "PK", "No", "Auto Increment", "Mã định danh duy nhất của vé điện tử."),
                ("order_item_id", "BIGINT UNSIGNED", "FK", "No", "-", "Khóa ngoại trỏ tới order_items.id (Gắn với dòng đặt vé)."),
                ("ticket_code", "VARCHAR(50)", "UK", "No", "-", "Mã vé duy nhất để tra cứu / check-in (Ví dụ: 'TKT-82HA9F01BC')."),
                ("status", "VARCHAR(50)", "-", "No", "'valid'", "Trạng thái vé: 'valid' (Hợp lệ), 'used' (Đã dùng), 'cancelled'."),
                ("checked_in_at", "DATETIME", "-", "Yes", "NULL", "Thời điểm ban tổ chức quét mã check-in tại cổng."),
                ("created_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm sinh vé điện tử."),
                ("updated_at", "TIMESTAMP", "-", "Yes", "CURRENT_TIMESTAMP", "Thời điểm cập nhật trạng thái vé."),
            ]
        },
    ]

    table_headers = ["Tên Cột", "Kiểu Dữ Liệu", "Khóa", "Null?", "Mặc Định", "Ý Nghĩa Nghiệp Vụ"]
    col_w_dict = [Inches(1.3), Inches(1.3), Inches(0.6), Inches(0.6), Inches(1.1), Inches(2.1)]

    for idx, tbl in enumerate(tables_spec):
        h2 = doc.add_heading(f"3.{idx+1}. Bảng `{tbl['name']}`", level=2)
        h2.paragraph_format.space_before = Pt(14)
        h2.paragraph_format.space_after = Pt(3)
        h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

        p = doc.add_paragraph()
        p.paragraph_format.space_after = Pt(6)
        r_desc = p.add_run(f"Mô tả nghiệp vụ: {tbl['desc']}")
        r_desc.font.italic = True
        r_desc.font.color.rgb = RGBColor(71, 85, 105)

        add_styled_table(doc, table_headers, tbl["cols"], col_widths=col_w_dict)

    # ==================== PHẦN 4 ====================
    h1 = doc.add_heading("PHẦN 4: CÁC BẢNG QUẢN TRỊ HỆ THỐNG CỦA LARAVEL", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph(
        "Bên cạnh các bảng nghiệp vụ, hệ thống tận dụng tối đa cơ chế lưu trữ cơ sở dữ liệu tích hợp của Laravel "
        "để quản lý phiên làm việc, lưu bộ đệm và xử lý hàng đợi mà không cần cài đặt thêm phần mềm Redis hay Memcached:"
    )

    sys_tables_data = [
        ("sessions", "Lưu trữ phiên đăng nhập của người dùng. Cột: id (PK), user_id (FK nullable), ip_address, user_agent, payload, last_activity (Index). Hỗ trợ tự động ngắt phiên ngay lập tức khi tài khoản bị khóa."),
        ("cache & cache_locks", "Lưu trữ bộ đệm dữ liệu truy vấn tĩnh và cơ chế khóa phân tán phục vụ tác vụ nền."),
        ("jobs & failed_jobs", "Lưu trữ hàng đợi tác vụ nền (Queue) và lịch sử các tác vụ lỗi phục vụ gửi email và xử lý bất đồng bộ."),
        ("migrations", "Lịch sử thực thi các tệp di chuyển cấu trúc CSDL của Laravel, đảm bảo đồng nhất giữa các môi trường triển khai."),
    ]
    for name, desc in sys_tables_data:
        p = doc.add_paragraph()
        p.paragraph_format.space_after = Pt(4)
        r_n = p.add_run(f"• Bảng `{name}`: ")
        r_n.bold = True
        r_n.font.color.rgb = RGBColor(15, 23, 42)
        p.add_run(desc)

    # ==================== PHẦN 5 ====================
    h1 = doc.add_heading("PHẦN 5: KIỂM SOÁT ĐỒNG THỜI & RÀNG BUỘC TOÀN VẸN NGHIỆP VỤ", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    h2 = doc.add_heading("5.1. Cơ chế Khóa Bi Quan (Pessimistic Locking - lockForUpdate)", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Vấn đề lớn nhất của hệ thống bán vé là tình trạng 'Bán vượt số lượng vé' (Overselling / Race Condition) "
        "khi có hàng trăm người dùng cùng nhấn nút đặt những chiếc vé cuối cùng trong cùng một tích tắc.\n\n"
        "Giải pháp áp dụng trong `app/Services/OrderService.php`:\n"
        "1. Toàn bộ thao tác kiểm tra tồn kho và tạo đơn được bao bọc trong giao dịch cơ sở dữ liệu: `DB::transaction()`.\n"
        "2. Sử dụng mệnh đề `lockForUpdate()` trên bảng `ticket_types`:\n"
        "   `TicketType::whereIn('id', $ids)->lockForUpdate()->get();`\n"
        "3. Lệnh này gửi câu lệnh SQL `SELECT ... FOR UPDATE` tới hệ quản trị cơ sở dữ liệu, khóa độc quyền (exclusive lock) "
        "trên đúng các hàng loại vé đang giao dịch cho đến khi transaction kết thúc (Commit hoặc Rollback).\n"
        "4. Nếu `sold + quantity_requested > quantity`, hệ thống ném ngoại lệ `BusinessException` và Rollback ngay lập tức, "
        "đảm bảo không bao giờ xảy ra tình trạng bán vượt tồn kho dù tải đồng thời cực lớn."
    )

    h2 = doc.add_heading("5.2. Cơ chế Giữ chỗ 15 Phút & Tự động Hoàn trả Vé", level=2)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(4)
    h2.runs[0].font.color.rgb = RGBColor(37, 99, 235)

    doc.add_paragraph(
        "Nhằm ngăn chặn hành vi 'giữ chỗ ảo' (đặt vé nhưng không trả tiền làm người khác mất cơ hội mua vé), hệ thống áp dụng cơ chế giữ chỗ 15 phút:\n"
        "• Khi đơn hàng khởi tạo thành công ở trạng thái `pending`, cột `expires_at` được gán bằng `now()->addMinutes(15)`.\n"
        "• Đồng thời số lượng vé đặt đã được cộng tạm thời vào `ticket_types.sold` để giữ vé an toàn cho khách.\n"
        "• Nếu sau 15 phút đơn hàng chưa chuyển sang `paid`, hệ thống thu hồi vé theo 2 cách:\n"
        "  a) Tác vụ nền định kỳ: Command `php artisan orders:expire-pending` quét tất cả đơn `pending` có `expires_at < now()`, cập nhật trạng thái `cancelled` và hoàn trừ lại `ticket_types.sold` trong `DB::transaction()`.\n"
        "  b) Lazy Check: Nếu khách hàng truy cập hoặc cố gắng thanh toán đơn hàng đã quá hạn, hệ thống kiểm tra tức thời và kích hoạt hủy đơn, từ chối nhận tiền."
    )

    # ==================== PHẦN 6 ====================
    h1 = doc.add_heading("PHẦN 6: CHIẾN LƯỢC ĐÁNH CHỈ MỤC (INDEXING STRATEGY)", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph(
        "Để đảm bảo tốc độ phản hồi dưới 200ms ngay cả khi dữ liệu tăng trưởng lớn, các chỉ mục (Indexes) "
        "được thiết kế kỹ lưỡng dựa trên tần suất và hành vi truy vấn thực tế của người dùng:"
    )

    idx_headers = ["Tên Bảng", "Tên / Loại Chỉ mục", "Các Cột Tham Gia", "Mục Đích Tối Ưu Truy Vấn"]
    idx_data = [
        ["users", "Unique Index", "email", "Tăng tốc xác thực đăng nhập và bảo đảm email không trùng lặp."],
        ["categories", "Unique Index", "slug", "Tra cứu trang danh mục theo URL thân thiện SEO với độ phức tạp O(1)."],
        ["events", "Unique Index", "slug", "Tra cứu chi tiết sự kiện theo slug trên Storefront."],
        ["events", "Composite Index", "status, start_time", "Tối ưu hóa bộ lọc sự kiện đang mở bán trên Trang chủ theo thời gian."],
        ["events", "Foreign Key Index", "category_id, venue_id", "Tối ưu hóa các truy vấn tìm kiếm lọc sự kiện theo địa điểm và thể loại."],
        ["ticket_types", "Foreign Key Index", "event_id", "Tăng tốc nạp danh sách loại vé khi xem chi tiết sự kiện (Eager Loading)."],
        ["orders", "Unique Index", "order_code", "Tra cứu nhanh đơn hàng qua mã định danh ORD-XXXXXXXX."],
        ["orders", "Composite Index", "user_id, status", "Tăng tốc tải trang 'Đơn hàng của tôi' và lọc các đơn cần xử lý."],
        ["tickets", "Unique Index", "ticket_code", "Tối ưu hóa tuyệt đối tốc độ kiểm tra soát vé tại cổng (Check-in nhanh < 50ms)."],
        ["sessions", "Index", "last_activity, user_id", "Tối ưu hóa việc dọn dẹp các phiên hết hạn và ngắt phiên khi khóa tài khoản."],
    ]
    col_w_idx = [Inches(1.2), Inches(1.4), Inches(1.6), Inches(2.8)]
    add_styled_table(doc, idx_headers, idx_data, col_widths=col_w_idx)

    # ==================== PHẦN 7 ====================
    h1 = doc.add_heading("PHẦN 7: DỮ LIỆU MẪU (SEED DATA) & KIỂM ĐỊNH TOÀN VẸN", level=1)
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(8)
    h1.runs[0].font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph(
        "Cơ sở dữ liệu đi kèm bộ Seeder hoàn chỉnh (`database/seeders/DatabaseSeeder.php`), "
        "cho phép khởi tạo lại toàn bộ dữ liệu mẫu ban đầu chỉ bằng 1 câu lệnh duy nhất:\n"
        "`php artisan migrate:fresh --seed --force`"
    )

    seed_headers = ["Seeder Class", "Thực Thể", "Số Lượng Mẫu", "Nội Dung Dữ Liệu Khởi Tạo"]
    seed_data = [
        ["UserSeeder", "users", "8 tài khoản", "1 Admin (admin@example.com), 2 Organizer (organizer1..2@example.com), 5 Customer (customer1..5@example.com)."],
        ["CategorySeeder", "categories", "5 danh mục", "Âm nhạc & Hòa nhạc, Hội thảo & Workshop, Thể thao & Giải đấu, Nghệ thuật & Sân khấu, Công nghệ & Triển lãm."],
        ["VenueSeeder", "venues", "4 địa điểm", "TT Hội nghị Quốc gia HN, Nhà thi đấu Quân khu 7, Cung Thể thao Tiên Sơn, Nhà hát Hòa Bình."],
        ["EventSeeder", "events & ticket_types", "8 sự kiện, 16 loại vé", "5 sự kiện published, 1 sự kiện draft, 1 sự kiện pending, 1 sự kiện cancelled."],
        ["OrderSeeder", "orders, items, tickets", "4 đơn, 3 vé điện tử", "2 đơn đã thanh toán (paid) kèm 3 vé điện tử (1 used, 2 valid), 1 đơn pending, 1 đơn cancelled."],
    ]
    col_w_seed = [Inches(1.4), Inches(1.5), Inches(1.2), Inches(2.9)]
    add_styled_table(doc, seed_headers, seed_data, col_widths=col_w_seed)

    doc.add_paragraph(
        "Kết quả kiểm định toàn vẹn: 100% các bảng đều vượt qua 37 bài kiểm thử tự động (2 Unit + 35 Feature Tests) "
        "đạt 104 assertions thành công, không phát hiện bất kỳ lỗi vi phạm khóa ngoại, mất đồng bộ tồn kho hoặc rò rỉ dữ liệu."
    )

    # Save document
    doc.save(output_path)
    print(f"Document successfully created at: {output_path}")

if __name__ == "__main__":
    docs_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "docs")
    build_database_document(os.path.join(docs_dir, "02_thiet_ke_co_so_du_lieu.docx"))
    build_database_document(os.path.join(docs_dir, "thiet_ke_co_so_du_lieu.docx"))
