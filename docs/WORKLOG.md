# NHẬT KÝ LÀM VIỆC VÀ PHÂN CÔNG NHÓM (WORKLOG.md)

- **Nhóm thực hiện**: Nhóm 8 — Lớp **68PM2**
- **Danh sách thành viên (3 thành viên)**:
  1. **Hồ Đăng Quang** — MSSV: `0021568`
  2. **Tống Xuân Định** — MSSV: `0008268`
  3. **Nguyễn Hữu Quý** — MSSV: `0022968`

## 1. Bảng phân công nhóm

| STT | Thành viên (MSSV) | Vai trò trong nhóm | Mảng phụ trách | PLO phụ trách |
|-----|-------------------|-------------------|----------------|---------------|
| 1 | **Hồ Đăng Quang** (`0021568`) | Trưởng nhóm / Kiến trúc & Fullstack (Storefront) | Quản lý tiến độ, Phân tích yêu cầu (`01_phan_tich_yeu_cau.md`), Đặc tả `SPEC.md`, Layouts (`app`, `panel`), Storefront (tìm/lọc/sắp xếp), `OrderService` (`lockForUpdate`), Thanh toán giả lập, Vé của tôi, Kịch bản Demo | PLO1, PLO3, Mục 7 |
| 2 | **Tống Xuân Định** (`0008268`) | Backend, Database & Organizer Developer | Thiết kế CSDL 3NF (`02_thiet_ke_co_so_du_lieu.*`), 10 Migrations, 8 Models, Factories & Seeders, Auth & Profile, Middleware (`CheckRole`, `CheckActive`), 3 Policies, Phân hệ Ban tổ chức (CRUD sự kiện/loại vé, đơn hàng, `TicketCheckinService` 5 bước, Scheduler `orders:expire-pending`) | PLO2, PLO3 |
| 3 | **Nguyễn Hữu Quý** (`0022968`) | Admin Developer, QA & System Architecture | Phân hệ Quản trị viên (`Admin\Dashboard`, CRUD Danh mục/Địa điểm ràng buộc `RESTRICT`, Quản lý người dùng & khóa tài khoản, Duyệt/từ chối/hủy sự kiện, Quản lý đơn hàng), 37 Unit/Feature Tests (`tests/`), `test-cases.md`, 8 Sơ đồ PlantUML + PNG (`docs/diagrams/`), `README.md` & Báo cáo nghiệm thu | PLO2, PLO3, PLO6 |

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

### Ngày 10/11/2026 (Rà soát, sửa lỗi & đồng bộ tài liệu theo rubric `tailieu/`)
- **Khởi tạo & Sửa lỗi kỹ thuật**:
  - Khởi tạo môi trường chạy hoàn chỉnh: `composer install`, tạo `.env`, tạo `database/database.sqlite`, sinh `APP_KEY`, `php artisan storage:link`, `php artisan migrate:fresh --seed --force`.
  - Sửa lỗi CSS thiếu trong `resources/views/layouts/panel.blade.php` (`.badge-role*`, `.btn-primary-custom`, `.ticket-code-font`, `.card-custom`) và bỏ thẻ `<link>` `bootstrap.min.php` lỗi trong `resources/views/layouts/app.blade.php`.
  - Sửa `routes/organizer.php` (`->except(['index', 'show'])` cho shallow `ticket-types` thành 57 app routes) và đăng ký lịch chạy `orders:expire-pending` mỗi phút trong `routes/console.php`.
  - Sửa lỗi xử lý đơn quá hạn trong `app/Policies/OrderPolicy.php` và `app/Services/OrderService.php::payOrder()` để tự động hủy đơn + hoàn trả vé đúng giao dịch (không trả 403 và không bị rollback khi ném `BusinessException`).
  - Sửa lỗi cú pháp PlantUML trong `docs/diagrams/07_activity_mua_ve.puml` (`elseif`), chỉnh `05_erd.puml`, kết xuất 8 ảnh PNG trong `docs/diagrams/images/` và nhúng vào tài liệu Word `.docx`.
- **Kiểm thử & Đồng bộ tài liệu**:
  - Bổ sung `tests/Unit/DomainModelsTest.php` (giải quyết lỗi thiếu thư mục `tests/Unit` khi chạy `php artisan test`), bổ sung test quá hạn thanh toán trong `OrderAndPaymentTest.php` và kiểm tra Dashboard/CRUD trong `AdminManagementTest.php`.
  - Đồng bộ lại `README.md`, `docs/01_*` đến `06_*`, `docs/testing/test-cases.md`, `docs/testing/test-output.txt` và xuất lại toàn bộ các file `.docx`.
  - Kết quả kiểm thử sau chỉnh sửa cuối cùng: **37/37 tests PASSED (104 assertions, 0 failures)**.
