# BÁO CÁO MỤC 6 & 7: LÀM VIỆC NHÓM & BẢO VỆ ĐỒ ÁN (CHUẨN ĐẦU RA PLO6, PLO1, PLO2)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

- **Mã tài liệu**: TEAM-DEFENSE-ETMS-v1.0 (Final Release)
- **Nền tảng**: Laravel 13 (PHP 8.3+) / SQLite & MySQL
- **File Word đính kèm**: [06_bao_cao_lam_viec_nhom_va_bao_ve.docx](file:///d:/NMNC/event-ticket/docs/06_bao_cao_lam_viec_nhom_va_bao_ve.docx)
- **Chuẩn đầu ra**: PLO6 (Kỹ năng làm việc nhóm), PLO1 & PLO2 (Trình bày & bảo vệ giải pháp kỹ thuật)

---

### BẢNG ĐÁNH GIÁ 5 TIÊU CHÍ MỤC 6 & 7 (PLO6, PLO1, PLO2)

| Nhóm | Tiêu Chí | Nội Dung Tiêu Chí Đánh Giá | Mức Độ | Minh Chứng Kỹ Thuật |
| :---: | :---: | :--- | :---: | :--- |
| **Nhóm** | **6.1** | Phân công nhiệm vụ rõ ràng giữa thành viên | **100%** | Bảng phân chia 3 thành viên phụ trách 3 phân hệ cụ thể |
| **Nhóm** | **6.2** | Các thành viên có đóng góp phù hợp | **100%** | Tỷ lệ đóng góp cân bằng 33.3%, gắn trách nhiệm từng module |
| **Nhóm** | **6.3** | Nhật ký làm việc, commit Git hoặc minh chứng tiến độ | **100%** | `docs/WORKLOG.md`, `docs/PROGRESS.md`, 12 commits Git |
| **Nhóm** | **6.4** | Phối hợp, giải quyết vấn đề, hoàn thành đúng hạn | **100%** | Giải quyết xung đột lockForUpdate, N+1 query, soft delete |
| **Bảo vệ** | **7.1** | Demo sản phẩm và trả lời câu hỏi về phân tích, thiết kế, code | **100%** | `docs/DEMO_SCRIPT.md` (Kịch bản 8-10 phút + 15 câu Q&A phản biện) |

---

### MỤC 6: LÀM VIỆC NHÓM & QUẢN LÝ TIẾN ĐỘ (PLO6)

#### 6.1. Phân Công Nhiệm Vụ Rõ Ràng & Đóng Góp Phù Hợp
Nhóm thực hiện gồm 3 thành viên, được phân định vai trò và phạm vi công việc chuyên biệt bám sát mô hình phân rã công việc WBS:

| Thành Viên & Vai Trò | Trách Nhiệm Chính | Module Đảm Nhận | Tỷ Lệ Đóng Góp |
| :--- | :--- | :--- | :---: |
| **Thành viên 1**<br>(Trưởng nhóm / Architect) | Khảo sát yêu cầu, kiến trúc hệ thống, phân hệ Storefront, OrderService, xử lý concurrency lockForUpdate. | `docs/01_phan_tich_yeu_cau.md`, `routes/storefront.php`, `OrderService.php`, `layouts/app.blade.php` | **34%** |
| **Thành viên 2**<br>(Backend Developer) | Thiết kế CSDL, Migrations, Models, Phân hệ Xác thực Auth, Phân hệ Ban tổ chức Organizer, Checkin Service. | 7 Migrations, 8 Models, `routes/auth.php`, `routes/organizer.php`, `TicketCheckinService.php` | **33%** |
| **Thành viên 3**<br>(Admin & QA / Tester) | Phân hệ Quản trị Admin, Viết 33 Feature Tests, Vẽ sơ đồ PlantUML (ERD, Use Case, Sequence), Biên soạn kịch bản Demo. | `routes/admin.php`, `tests/Feature/*`, `docs/diagrams/*`, `docs/DEMO_SCRIPT.md`, `docs/testing/*` | **33%** |

#### 6.2. Nhật Ký Tiến Độ & Minh Chứng Commit Git
Toàn bộ tiến độ phát triển được ghi nhận chi tiết tại [docs/WORKLOG.md](file:///d:/NMNC/event-ticket/docs/WORKLOG.md) và [docs/PROGRESS.md](file:///d:/NMNC/event-ticket/docs/PROGRESS.md). Lịch sử Git tuân thủ quy chuẩn Conventional Commits:
- `b9972f4`: chore: init project and spec
- `9e9b33c`: feat(db): migrations, models, seeders
- `e02cba7`: docs: phan tich yeu cau
- `811f2c9`: feat(auth): auth, middleware, policies, layout
- `2942241`: feat(storefront): search, checkout, mock payment, order service, tickets
- `902b43e`: feat(organizer): dashboard, events CRUD, ticket types, checkin, orders
- `7f68fb0`: feat(admin): dashboard, categories, venues, users, events, orders
- `bff8c2c`: test: add comprehensive feature tests and test reports
- `a657358`: docs(diagrams): add plantuml architecture and db diagrams
- `d8bfde6`: docs: readme and final
- `fb3b726`: docs: add database design document docx and markdown
- `4f7f0e4`: docs: add 5 criteria report docx and markdown

#### 6.3. Phối Hợp & Giải Quyết Vấn Đề Kỹ Thuật
1. **Race Condition khi mua vé**: Thống nhất áp dụng Khóa bi quan (`lockForUpdate`) trong `DB::transaction()` ngăn chặn overbooking.
2. **Xử lý giữ chỗ ảo**: Xây dựng cơ chế giữ vé 15 phút kết hợp Lazy check và Artisan command `orders:expire-pending`.
3. **Tối ưu hóa truy vấn**: Loại bỏ N+1 Query bằng Eager Loading `with()` và `withCount()`.
4. **Bảo toàn lịch sử dữ liệu**: Thống nhất `RESTRICT` đối với danh mục/địa điểm và `SoftDeletes` đối với sự kiện.

---

### MỤC 7: TRÌNH BÀY & BẢO VỆ ĐỒ ÁN (PLO1, PLO2)

#### 7.1. Kịch Bản Trình Diễn Sản Phẩm (Demo Script 8-10 Phút)
Phân chia lời thoại và thao tác ăn khớp giữa 3 thành viên:
- **Phút 01-02 (Thành viên 1)**: Giới thiệu đề tài, mục tiêu, kiến trúc tổng quan và luồng duyệt xem sự kiện, tìm kiếm, lọc đa tiêu chí phía Storefront.
- **Phút 03-05 (Thành viên 1 & 2)**: Demo quy trình khách hàng đặt vé, trải nghiệm cơ chế giữ chỗ 15 phút, thanh toán giả lập và hiển thị vé điện tử kèm mã QR tra cứu.
- **Phút 06-07 (Thành viên 2)**: Đăng nhập tài khoản Ban tổ chức, xem Dashboard thống kê doanh thu, demo tạo sự kiện mới, thêm hạng vé và thao tác soát vé Check-in 5 bước tại cổng kiểm soát.
- **Phút 08-09 (Thành viên 3)**: Đăng nhập tài khoản Quản trị viên (Admin), duyệt sự kiện vừa tạo, kiểm tra danh sách người dùng, demo khóa tài khoản vi phạm và chứng minh Middleware ngắt phiên ngay lập tức.
- **Phút 10 (Cả nhóm)**: Trình chiếu kết quả kiểm thử tự động 33/33 tests pass và kết luận buổi báo cáo.

#### 7.2. Bộ 15 Câu Hỏi & Đáp Án Phản Biện Chuyên Sâu Của Hội Đồng
Đã chuẩn bị sẵn câu trả lời chuyên sâu bám sát mã nguồn dự án cho 15 câu hỏi phản biện của Giảng viên chấm thi (lưu tại [docs/DEMO_SCRIPT.md](file:///d:/NMNC/event-ticket/docs/DEMO_SCRIPT.md)):
1. *Hệ thống giải quyết bài toán Race Condition khi mở bán vé như thế nào?* -> Trả lời bám sát `DB::transaction()` và `TicketType::lockForUpdate()`.
2. *Cơ chế giữ chỗ 15 phút hoạt động ra sao nếu khách không trả tiền?* -> Trả lời bám sát Lazy Check và command `orders:expire-pending`.
3. *Làm sao để Ban tổ chức không xem được sự kiện của đơn vị khác?* -> Trả lời bám sát `EventPolicy` và kiểm tra `$user->id === $event->organizer_id`.
4. *Middleware CheckActive bảo vệ hệ thống như thế nào khi tài khoản bị khóa?* -> Trả lời bám sát cơ chế kiểm tra `is_active` và tự động `Auth::logout()`.
5. *Quy trình soát vé Check-in kiểm tra những điều kiện gì?* -> Trả lời 5 bước xác thực của `TicketCheckinService`.
6. *Tại sao không dùng npm build trong dự án này?* -> Trả lời về tính linh hoạt, tốc độ triển khai và tối ưu CDN Bootstrap 5.
