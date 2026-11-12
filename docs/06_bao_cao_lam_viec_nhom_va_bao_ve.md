# BÁO CÁO MỤC 6 & 7: LÀM VIỆC NHÓM & BẢO VỆ ĐỒ ÁN (CHUẨN ĐẦU RA PLO6, PLO1, PLO2, PLO3)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

- **Mã tài liệu**: TEAM-DEFENSE-ETMS-v1.2
- **Nhóm thực hiện**: **Nhóm 8 — Lớp 68PM2**
- **Thành viên**: Hồ Đăng Quang (`0021568`), Tống Xuân Định (`0008268`), Nguyễn Hữu Quý (`0022968`)
- **Nền tảng**: Laravel 13 (PHP 8.3+) / SQLite & MySQL
- **File Word đính kèm**: [06_bao_cao_lam_viec_nhom_va_bao_ve.docx](./06_bao_cao_lam_viec_nhom_va_bao_ve.docx)
- **Chuẩn đầu ra**: PLO6 (Làm việc nhóm), PLO1, PLO2, PLO3 (Trình bày & bảo vệ sản phẩm — đối chiếu `tailieu/4.jpg`)

---

### BẢNG ĐÁNH GIÁ 5 TIÊU CHÍ MỤC 6 & 7

| Nhóm | Tiêu Chí | Nội Dung Tiêu Chí Đánh Giá | Trạng Thái Hiện Tại | Minh Chứng & Ghi Chú |
| :---: | :---: | :--- | :---: | :--- |
| **Nhóm** | **6.1** | Phân công nhiệm vụ rõ ràng giữa thành viên | **100% Hoàn thành** | Bảng phân công 3 thành viên Nhóm 8 - Lớp 68PM2 trong [`WORKLOG.md`](./WORKLOG.md) |
| **Nhóm** | **6.2** | Các thành viên có đóng góp phù hợp | **100% Hoàn thành** | Phân chia đồng đều (~34% / 33% / 33%) gắn với từng phân hệ và tài liệu cụ thể |
| **Nhóm** | **6.3** | Nhật ký làm việc, commit Git hoặc minh chứng tiến độ | **Có nhật ký MD (Cần khởi tạo Git)** | Có [`WORKLOG.md`](./WORKLOG.md) và [`PROGRESS.md`](./PROGRESS.md); khuyến nghị nhóm chạy `git init` và push repo trước khi nộp |
| **Nhóm** | **6.4** | Phối hợp, giải quyết vấn đề, hoàn thành đúng hạn | **100% Hoàn thành** | Phối hợp xử lý `lockForUpdate`, giữ chỗ 15p + scheduler, chống N+1, `SoftDeletes` & `RESTRICT` |
| **Bảo vệ** | **7.1** | Demo sản phẩm và trả lời câu hỏi về phân tích, thiết kế, code | **Sẵn sàng** | [`DEMO_SCRIPT.md`](./DEMO_SCRIPT.md) phân vai 3 thành viên (8–10 phút) + 15 câu hỏi-đáp phản biện |

---

### MỤC 6: LÀM VIỆC NHÓM & QUẢN LÝ TIẾN ĐỘ (PLO6)

#### 6.1. Bảng Phân Công Nhiệm Vụ & Đóng Góp Của 3 Thành Viên (Nhóm 8 - Lớp 68PM2)

| STT | Thành Viên (MSSV) | Vai Trò Trong Nhóm | Module & Công Việc Đảm Nhận | Tỷ Lệ Đóng Góp |
| :---: | :--- | :--- | :--- | :---: |
| **1** | **Hồ Đăng Quang**<br>(`0021568`) | Trưởng nhóm / Kiến trúc & Fullstack (Storefront) | • Điều phối tiến độ chung, phân tích yêu cầu (`01_phan_tich_yeu_cau.md`), đặc tả kỹ thuật (`SPEC.md`).<br>• Xây dựng giao diện chung (`layouts/app.blade.php`, `layouts/panel.blade.php`), phân hệ Storefront (tìm kiếm/lọc/sắp xếp), `OrderService` (`lockForUpdate()`), thanh toán giả lập, trang vé cá nhân và điều phối kịch bản Demo. | **34%** |
| **2** | **Tống Xuân Định**<br>(`0008268`) | Backend, Database & Organizer Developer | • Thiết kế CSDL chuẩn 3NF (`02_thiet_ke_co_so_du_lieu.*`), 10 Migrations, 8 Eloquent Models, Factories và 5 Seeders.<br>• Xây dựng phân hệ Auth/Profile, Middleware (`CheckRole`, `CheckActive`), 3 Policies chống IDOR, kênh Ban tổ chức (CRUD sự kiện/loại vé, đơn hàng, `TicketCheckinService` 5 bước, Scheduler `orders:expire-pending`). | **33%** |
| **3** | **Nguyễn Hữu Quý**<br>(`0022968`) | Admin Developer, QA & System Architecture | • Xây dựng phân hệ Quản trị viên (Dashboard toàn sàn, CRUD Danh mục/Địa điểm ràng buộc `RESTRICT`, Quản lý người dùng khóa/mở & chống tự khóa, Duyệt/từ chối/hủy sự kiện, Quản lý đơn hàng).<br>• Xây dựng 37 Unit/Feature Tests (`tests/`), tài liệu `test-cases.md`, 8 sơ đồ PlantUML + PNG (`docs/diagrams/`), `README.md` và bộ báo cáo nghiệm thu. | **33%** |

#### 6.2. Nhật Ký Tiến Độ & Lưu Ý Quản Lý Mã Nguồn Git
- Toàn bộ tiến độ phát triển qua các giai đoạn (Phase 0 đến Phase 6 và đợt rà soát ngày 10/11/2026) được ghi nhận chi tiết trong [`docs/PROGRESS.md`](./PROGRESS.md) và [`docs/WORKLOG.md`](./WORKLOG.md).
- **Khuyến nghị trước khi nộp**: Trong thư mục hiện tại chưa khởi tạo repository `.git`. Nhóm nên chạy `git init`, đẩy mã nguồn lên GitHub/GitLab và tạo các commit thực tế cho 3 thành viên để làm minh chứng bổ sung cho Tiêu chí 6.3.

#### 6.3. Phối Hợp & Giải Quyết Vấn Đề Kỹ Thuật
1. **Race Condition khi mua vé**: Áp dụng Khóa bi quan (`lockForUpdate()`) trong `DB::transaction()`.
2. **Xử lý giữ chỗ ảo & đơn hết hạn**: Thời hạn giữ chỗ 15 phút kết hợp Lazy Check khi xem/thanh toán đơn quá hạn và tác vụ định kỳ `orders:expire-pending` (đã lập lịch mỗi phút trong `routes/console.php`).
3. **Tối ưu truy vấn**: Áp dụng Eager Loading `with()`, `withCount()`, `withMin()`, `withSum()` để loại bỏ N+1 Query.
4. **Bảo toàn dữ liệu & Giao diện nhất quán**: Áp dụng `RESTRICT` trên khóa ngoại danh mục/địa điểm, `SoftDeletes` trên sự kiện, đồng bộ CSS giữa `layouts/app` và `layouts/panel`.

---

### MỤC 7: TRÌNH BÀY & BẢO VỆ ĐỒ ÁN (PLO1, PLO2, PLO3)

#### 7.1. Kịch Bản Trình Diễn Sản Phẩm (8–10 Phút) — Nhóm 8 (68PM2)
Chi tiết lời thoại, tài khoản đăng nhập (`admin@example.com`, `organizer1@example.com`, `customer1@example.com`, mật khẩu `password`) và thao tác từng bước được biên soạn tại [`docs/DEMO_SCRIPT.md`](./DEMO_SCRIPT.md):
- **Chặng 1 (~3.5 phút — Hồ Đăng Quang)**: Giới thiệu đề tài, kiến trúc tổng quan, duyệt/tìm kiếm/lọc sự kiện phía Storefront -> Đặt vé giữ chỗ 15 phút -> Thanh toán giả lập -> Xem thẻ vé điện tử mã monospace (`TKT-XXXXXXXXXX`) & in vé.
- **Chặng 2 (~3.5 phút — Tống Xuân Định)**: Đăng nhập Ban tổ chức, xem Dashboard -> Tạo sự kiện nháp, thêm loại vé, gửi duyệt -> Thử soát vé Check-in 5 bước tại `/organizer/checkin` (vé hợp lệ, vé đã dùng, vé không tồn tại).
- **Chặng 3 (~2–3 phút — Nguyễn Hữu Quý)**: Đăng nhập Quản trị viên (Admin), xem Dashboard -> Duyệt sự kiện vừa gửi -> Kiểm tra chặn xóa danh mục có sự kiện -> Khóa/mở khóa tài khoản -> Chạy minh chứng `php artisan test` (37/37 tests pass, 104 assertions).

#### 7.2. Bộ 15 Câu Hỏi & Đáp Án Phản Biện Chuyên Sâu
Đã chuẩn bị đầy đủ tại Phần 2 của [`docs/DEMO_SCRIPT.md`](./DEMO_SCRIPT.md), bao gồm các nhóm câu hỏi về Cơ sở dữ liệu & Concurrency (`lockForUpdate`, giữ chỗ 15p, SoftDeletes, N+1), Bảo mật & Phân quyền (`CheckRole`, `CheckActive`, Policies chống IDOR, CSRF/XSS, `throttle:5,1`), và Nghiệp vụ (`OrderService`, `TicketCheckinService` 5 bước, `BusinessException`, Zero-node Bootstrap CDN, chuyển đổi MySQL).
