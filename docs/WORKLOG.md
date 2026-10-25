# NHẬT KÝ LÀM VIỆC VÀ PHÂN CÔNG NHÓM (WORKLOG.md)

## 1. Bảng phân công nhóm

| STT | Thành viên | Vai trò trong nhóm | Mảng phụ trách | PLO phụ trách |
|-----|------------|-------------------|----------------|---------------|
| 1 | [Họ tên thành viên 1] | Trưởng nhóm (Leader / Analyst) | Quản lý dự án, Phân tích yêu cầu, Tài liệu nghiệp vụ & Kịch bản demo | PLO1, Mục 7 |
| 2 | [Họ tên thành viên 2] | Backend & Database Developer | Thiết kế CSDL, Migrations, Models, Seeders, ERD | PLO2 |
| 3 | [Họ tên thành viên 3] | Frontend & UI/UX Developer | Thiết kế Layouts (Bootstrap 5.3), Blade Views, Auth & Profile | PLO3 |
| 4 | [Họ tên thành viên 4] | Fullstack Developer (Storefront) | Chức năng Đặt vé, Thanh toán giả lập, Chi tiết sự kiện & Giỏ hàng | PLO3 |
| 5 | [Họ tên thành viên 5] | Fullstack Developer (Organizer & Admin) | Quản lý Sự kiện, Loại vé, Check-in vé, Phê duyệt & Quản trị hệ thống | PLO3 |
| 6 | [Họ tên thành viên 6] | QA & Security Engineer | Viết Unit/Feature Tests, Rà soát bảo mật CSRF/XSS/Auth, Vẽ sơ đồ kiến trúc | PLO3, PLO2, PLO6 |

---

## 2. Nhật ký làm việc theo ngày

### Ngày 25/10/2026
- **Khởi tạo dự án (Phase 0)**:
  - Kiểm tra môi trường: PHP 8.3.33, Composer v2.11, Git.
  - Khởi tạo project Laravel 11/13 (`event-ticket`).
  - Thiết lập múi giờ `Asia/Ho_Chi_Minh`, ngôn ngữ tiếng Việt `vi`, kết nối SQLite.
  - Tích hợp trait `AuthorizesRequests`, tạo `BusinessException`, tạo cấu trúc route tách biệt.
  - Thiết lập tài liệu đặc tả `docs/SPEC.md`, tiến độ `docs/PROGRESS.md` và nhật ký `docs/WORKLOG.md`.
- **Phân tích yêu cầu & Cơ sở dữ liệu (Phase 1)**:
  - Hoàn thành `docs/01_phan_tich_yeu_cau.md` (SRS chuẩn IEEE, 21 yêu cầu chức năng, 5 yêu cầu phi chức năng, 3 quy trình chi tiết, ma trận truy vết).
  - Hoàn thành `docs/DEMO_SCRIPT.md` (kịch bản trình diễn 8-10 phút, 15 câu hỏi - đáp chuyên sâu bám sát code).
  - Hoàn thành hệ thống CSDL: 7 migrations, 8 models Eloquent kèm relationships 2 chiều, scopes, accessors, Enums, factories, seeders.
  - Cổng kiểm tra Phase 1 `migrate:fresh --seed` vượt qua 100%.
- **Xác thực, Phân quyền & Giao diện cốt lõi (Phase 2)**:
  - Xây dựng hệ thống định tuyến xác thực `routes/auth.php` (đăng ký, đăng nhập throttle 5 lần/phút, đăng xuất, cập nhật hồ sơ cá nhân).
  - Tạo `AuthController`, `ProfileController`, các Form Requests tiếng Việt đầy đủ rules, attributes, messages.
  - Cài đặt Middleware `CheckRole` (đa quyền) và `CheckActive` (ngắt phiên tài khoản bị khóa) đăng ký alias tại `bootstrap/app.php`.
  - Thiết lập các Policy kiểm soát truy cập chéo: `EventPolicy`, `TicketTypePolicy`, `OrderPolicy`.
  - Thiết kế Layout `layouts.app` (Storefront responsive, Google Fonts, Bootstrap 5.3 CDN) và `layouts.panel` (Dashboard sidebar chuyên dụng).
  - Xây dựng Partials `alerts.blade.php`, `form-errors.blade.php`, các trang lỗi chuẩn hóa tiếng Việt 403, 404, 419, 500.
  - Cổng kiểm tra Phase 2 (migrate, route:list, view:cache) chạy sạch 100%.
- **Storefront & Kênh Ban Tổ Chức (Phase 3)**:
  - **Storefront**:
    - Trang chủ tìm kiếm từ khóa, lọc đa tiêu chí (danh mục, thành phố, ngày), sắp xếp theo ngày/giá (`withMin`), phân trang 9 mục.
    - Trang chi tiết sự kiện và bảng chọn loại vé, kiểm tra tồn khả dụng và thời gian mở bán.
    - `OrderService`: Triển khai đặt vé trong `DB::transaction()` + `lockForUpdate()` trên `ticket_types`, sinh mã `ORD-XXXXXXXX` duy nhất, giữ chỗ 15 phút.
    - Thanh toán giả lập (bank_transfer, momo, card) sinh mã vé `TKT-XXXXXXXXXX`, hủy đơn tự động hoàn trả số lượng vé, lazy check và artisan command `orders:expire-pending`.
    - Màn hình lịch sử đơn hàng, chi tiết đơn hàng, danh sách vé của tôi kèm nút in vé.
  - **Kênh Ban Tổ Chức (Organizer)**:
    - Bảng điều khiển thống kê (số sự kiện, tổng vé bán, doanh thu) dùng `withCount`/`withSum` loại bỏ N+1.
    - Quản lý sự kiện (CRUD), upload và thay thế ảnh banner sự kiện an toàn, gửi duyệt sự kiện lên Admin.
    - Quản lý các loại vé (lồng shallow trong event), kiểm tra ràng buộc số lượng và ngày mở bán.
    - Quản lý danh sách và chi tiết các đơn hàng thuộc sự kiện của mình.
    - Cổng soát vé (Check-in) với `TicketCheckinService` kiểm tra nghiêm ngặt 5 bước và cập nhật trạng thái `used`.
  - Cổng kiểm tra Phase 3 hoàn thành 100%.
- **Quản trị hệ thống Admin (Phase 4)**:
  - Xây dựng hệ thống định tuyến `routes/admin.php` với tiền tố `/admin`, middleware `[auth, active, role:admin]`.
  - `DashboardController`: Bảng điều khiển thống kê tổng quan (người dùng theo vai trò, sự kiện theo trạng thái, doanh thu, đơn hàng đã thanh toán, sự kiện bán chạy, sự kiện chờ phê duyệt).
  - `CategoryController` & `VenueController`: CRUD Danh mục và Địa điểm, cơ chế toàn vẹn dữ liệu chặn xóa khi đang có sự kiện liên kết.
  - `UserController`: Quản lý người dùng toàn hệ thống, tìm kiếm/lọc, đổi vai trò, khóa/mở khóa tài khoản; chặn admin tự khóa hoặc tự hạ quyền chính mình.
  - `EventController`: Xem xét và phê duyệt (`approve`), từ chối (`reject`) hoặc hủy (`cancel`) sự kiện.
  - `OrderController`: Quản lý, tra cứu và xem chi tiết toàn bộ đơn hàng và mã vé trên toàn sàn.
  - Cổng kiểm tra Phase 4 đạt 62 routes chuẩn xác, view:cache không lỗi, CSDL seed sạch sẽ.
- **Kiểm thử tự động QA & Thiết kế Sơ đồ hệ thống (Phase 5)**:
  - **QA & Security**:
    - Xây dựng 33 ca kiểm thử tính năng (Feature Tests) độc lập: `AuthTest`, `AuthorizationTest`, `StorefrontTest`, `OrderAndPaymentTest`, `CheckinTest`, `AdminManagementTest`.
    - Kết quả kiểm thử thực tế đạt 100% (33 passed, 82 assertions, 0 failures).
    - Tạo `docs/testing/test-cases.md` chi tiết từ TC-01 đến TC-33 và lưu log chạy thực `docs/testing/test-output.txt`.
    - Rà soát bảo mật toàn diện: 100% `@csrf`, chống XSS, chống IDOR qua Policies, tối ưu N+1 Query.
  - **Diagrams (Sơ đồ hệ thống)**:
    - Xây dựng 8 file PlantUML chuẩn xác và độc lập: Use Case tổng quan, 3 Use Case chi tiết theo vai trò, ERD 8 bảng chuẩn 100% schema, cây phân rã WBS, Activity luồng mua vé, Sequence luồng thanh toán.
    - Soạn thảo `docs/diagrams/README.md` hướng dẫn sử dụng và bảng tra cứu tương ứng trong báo cáo.
- **Hoàn thiện tài liệu, Hướng dẫn & Đóng gói dự án (Phase 6)**:
  - Soạn thảo `README.md` chuyên nghiệp tại thư mục gốc: Yêu cầu hệ thống, hướng dẫn cài đặt & khởi chạy từng bước cho Windows (PowerShell/CMD) và Linux/macOS, hướng dẫn chuyển đổi MySQL qua XAMPP/Laragon, bảng tài khoản demo 4 nhóm vai trò, cây thư mục dự án, hướng dẫn chạy 33 Feature tests, hướng dẫn chạy artisan command `orders:expire-pending` và Scheduler.
  - Cập nhật `.env.example` với hướng dẫn chuyển đổi SQLite sang MySQL minh bạch.
  - Hoàn thiện và đối soát 100% Bảng truy vết yêu cầu (Traceability Matrix) tại `docs/01_phan_tich_yeu_cau.md` (FR-01 đến FR-21 tương ứng 62 routes và controllers).
  - Hoàn thành cổng kiểm tra tổng thể hệ thống: Chạy mới CSDL (`migrate:fresh --seed`), kiểm tra danh sách routes (`route:list`), biên dịch view (`view:cache`), và xác nhận 100% test cases (`php artisan test` 33/33 passed).
