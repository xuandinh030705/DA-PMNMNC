# BÁO CÁO MỤC 4 & 5: KIỂM THỬ, BẢO MẬT & TRIỂN KHAI (CHUẨN ĐẦU RA PLO3)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

- **Mã tài liệu**: TEST-DEPLOY-ETMS-v2.0
- **Nền tảng**: Laravel 13 (PHP 8.3+, kiểm thử trên PHP 8.5.9) / MySQL 8.0 & SQLite
- **File Word đính kèm**: [05_bao_cao_kiem_thu_bao_mat_trien_khai.docx](./05_bao_cao_kiem_thu_bao_mat_trien_khai.docx)
- **Chuẩn đầu ra**: PLO3 - Kiểm thử, bảo mật và triển khai phần mềm (đối chiếu `tailieu/3.jpg`)

---

### BẢNG ĐÁNH GIÁ 8 TIÊU CHÍ MỤC 4 & 5 (PLO3)

| Nhóm | Tiêu Chí | Nội Dung Tiêu Chí Đánh Giá | Mức Độ | Minh Chứng Kỹ Thuật |
| :---: | :---: | :--- | :---: | :--- |
| **Kiểm thử** | **4.1** | Kiểm tra dữ liệu đầu vào và thông báo lỗi rõ ràng | **100%** | 11 FormRequests, hiển thị lỗi tiếng Việt dưới từng input form |
| **Kiểm thử** | **4.2** | Xử lý lỗi: trống, sai định dạng, trùng mã, không tìm thấy | **100%** | Unique validation, trang lỗi 403/404/419/500, `BusinessException`, xử lý ngoại lệ mail |
| **Bảo mật** | **4.3** | Áp dụng CSRF, middleware, auth, phân quyền & chống giả vé | **100%** | 100% `@csrf`, không `{!! !!}` ở Blade, `CheckRole`, `CheckActive`, 3 Policies, QR HMAC-SHA256 |
| **Kiểm thử** | **4.4** | Minh chứng kiểm thử chức năng | **100%** | **103 tests passed (411 assertions)**, Laravel Pint pass, `docs/testing/*`, GitHub Actions CI |
| **Triển khai** | **5.1** | Project chạy được trên môi trường Laravel chuẩn | **100%** | PHP 8.5.9 (`gd`, `pdo_mysql`), Laravel 13, `.env.example`, zero-node Bootstrap CDN |
| **Triển khai** | **5.2** | Database, migration, seed/script dữ liệu mẫu | **100%** | **12 migrations** (7 nghiệp vụ + 2 nâng cấp), `DatabaseSeeder` + `TicketboxEventSeeder` |
| **Triển khai** | **5.3** | Hướng dẫn cài đặt và chạy hệ thống | **100%** | `README.md` hướng dẫn MySQL 8, XAMPP/Laragon, SQLite dự phòng, bật `gd`, Mailpit & Camera |
| **Triển khai** | **5.4** | Mã nguồn tổ chức rõ, nộp đầy đủ, không thiếu file quan trọng | **100%** | Cây thư mục chuẩn Laravel, `.github/workflows/ci.yml`, `.env.example`, tài liệu & sơ đồ |

---

### MỤC 4: KIỂM THỬ & BẢO MẬT HỆ THỐNG

#### 4.1. Kiểm Tra Dữ Liệu Đầu Vào & Thông Báo Lỗi Rõ Ràng
- Mọi dữ liệu POST/PUT đi qua 11 Form Requests trước khi vào Controller.
- Khi có lỗi validation, hệ thống giữ lại `old()` (trừ chuỗi JSON quét QR lỗi để không điền ngược vào ô nhập tay check-in) và hiển thị lỗi qua `partials/form-errors.blade.php` cùng class `.is-invalid`.

#### 4.2. Xử Lý Các Trường Hợp Lỗi Phổ Biến & Vận Hành Offline
- **Trống / Sai định dạng**: Kiểm tra qua `required`, `email`, `integer`, `numeric`, `date`, `after`, `min`, `max`, `mimes`, `in`.
- **Trùng lặp**: Rule `unique` trên email/danh mục; tự sinh slug duy nhất; mã đơn `ORD-` và mã vé `TKT-` kiểm tra trùng qua vòng lặp `do ... while`.
- **Không tìm thấy / Sai trạng thái**: Route Model Binding & kiểm tra `isPublished()` trả về trang lỗi tùy biến `404` (`resources/views/errors/404.blade.php`).
- **Lỗi nghiệp vụ & Lỗi Mail Transport**: Ném `BusinessException` hiển thị flash tiếng Việt; riêng gửi `OrderPaidMail` được bọc `try/catch` + `report()` để mất mạng hoặc tắt SMTP không làm rollback đơn đã thanh toán.

#### 4.3. Bảo Mật Toàn Diện: CSRF, XSS, Chữ ký số QR & Phân Quyền
- **CSRF**: 100% form `POST`/`PUT`/`DELETE` trong `resources/views/` đều có `@csrf`.
- **XSS**: Toàn bộ output Blade dùng `{{ ... }}`, không sử dụng `{!! !!}`.
- **Chữ ký số HMAC-SHA256 chống giả mạo vé QR**: Payload JSON `{"v":1,"ticket_code":"TKT-..."}` được ký bằng `APP_KEY` và kiểm tra hằng thời gian qua `hash_equals()` trong `QrTicketService`.
- **Middleware & Policies (chống IDOR)**: `CheckRole` chặn sai vai trò (403); `CheckActive` ngắt phiên tài khoản bị khóa; `EventPolicy`, `TicketTypePolicy`, `OrderPolicy` bảo vệ cả tải vé PDF `/my-tickets/{ticket}/pdf` và chi tiết đơn hàng của Organizer/Customer.

#### 4.4. Minh Chứng Kiểm Thử Chức Năng (103 Tests / 411 Assertions)
Toàn bộ bộ kiểm thử tự động vượt qua 100% khi chạy `php artisan test` / `php vendor/bin/phpunit --testdox`, và định dạng code vượt qua `php vendor/bin/pint --test`:

| Test Suite / Class | Loại | Số Tests | Số Assertions | Phạm Vi Kiểm Định |
| :--- | :---: | :---: | :---: | :--- |
| `Tests\Unit\DomainModelsTest` | Unit | 2 | 29 | Accessor `remaining`, helper vai trò `User`, đồng bộ hằng số & Enum |
| `Tests\Unit\QrTicketServiceTest` | Unit | 13 | 27 | Payload QR, xuất PNG/SVG Data URI, ký & xác thực HMAC-SHA256, đổi `APP_KEY` |
| `Tests\Feature\AuthTest` | Feature | 6 | 26 | Đăng ký, đăng nhập đúng/sai, chặn user bị khóa, cập nhật profile, đăng xuất |
| `Tests\Feature\AuthorizationTest` | Feature | 6 | 9 | Chặn trái quyền Admin/Organizer, chống IDOR Event/Order/Organizer Order, chặn admin tự khóa/tự hạ quyền |
| `Tests\Feature\StorefrontTest` | Feature | 6 | 18 | Duyệt sự kiện, tìm kiếm từ khóa, lọc danh mục/thành phố, sort giá `NULL` cuối, 404 bản nháp, `zone`/`description` |
| `Tests\Feature\OrderAndPaymentTest` | Feature | 19 | 93 | Giữ chỗ 15p, giới hạn tồn, chặn pay khi hủy event, `OrderPaidMail`, chịu lỗi mail, chống N+1 `orders.show`, hủy đơn chống âm `sold`, luồng E2E, hiển thị `zone`/`checkedInBy` |
| `Tests\Feature\OrganizerTicketTypeTest` | Feature | 9 | 37 | Form tạo/sửa `zone` & `description`, validation độ dài/`quantity` min 1, giữ banner khi xóa mềm, chặn BTC khác sửa |
| `Tests\Feature\CheckinTest` | Feature | 20 | 63 | Check-in 6 bước bằng mã tay & JSON QR HMAC, chặn mã giả/hủy/chưa paid/đã dùng/chéo BTC/event xóa mềm, camera UI offline |
| `Tests\Feature\TicketPdfTest` | Feature | 9 | 27 | Sinh PDF khổ A5 trong RAM không ghi đĩa, tiếng Việt Unicode, quyền tải PDF qua `OrderPolicy` |
| `Tests\Feature\AdminManagementTest` | Feature | 13 | 82 | Duyệt/từ chối sự kiện, **hủy sự kiện cascade**, Top bán chạy, `RESTRICT` danh mục/địa điểm (cả soft-deleted), khóa user, `checkedInBy` |
| **Tổng cộng** | — | **103** | **411** | **100% PASSED (0 failures, 0 errors)** |

---

### MỤC 5: TRIỂN KHAI DỰ ÁN

#### 5.1. Môi Trường Chạy Chuẩn & Zero-Node
- Môi trường chuẩn: PHP `>= 8.3` (đã kiểm thử trên **PHP 8.5.9**) với các extension bắt buộc `pdo_mysql`, `gd`, `mbstring`, `openssl`, `fileinfo`, `zip`, `pdo_sqlite`; Composer 2.x; **MySQL 8.0+ Community Server** (mặc định) hoặc SQLite dự phòng.
- Zero-Node: Giao diện dùng Bootstrap 5.3 CDN & Bootstrap Icons CDN; thư viện quét camera `html5-qrcode.min.js` lưu nội bộ tại `public/js/`.

#### 5.2. Cơ Sở Dữ Liệu & Nạp Dữ Liệu Mẫu (Seed Data)
- Lệnh khởi tạo: `php artisan migrate:fresh --seed --force` và `php artisan storage:link`.
- 12 migrations (3 core + 7 nghiệp vụ + 2 nâng cấp) và 6 seeders trong `DatabaseSeeder`:
  - `UserSeeder`: 8 tài khoản mẫu (`admin@example.com`, `organizer1..2@example.com`, `customer1..5@example.com`).
  - `CategorySeeder` (6 danh mục) & `VenueSeeder` (4 địa điểm).
  - `EventSeeder`: 8 sự kiện mẫu (5 `published`, 1 `draft`, 1 `pending`, 1 `cancelled`) với 16 loại vé (có `zone` & `description`).
  - `TicketboxEventSeeder`: 14 sự kiện thật kèm ảnh banner & 14 tài khoản BTC (`@ticketbox.local`).
  - `OrderSeeder`: 4 đơn hàng (2 `paid`, 1 `pending`, 1 `cancelled`) kèm 3 vé điện tử (`used` có `checked_in_by`, `valid`).

#### 5.3. Email, Hướng Dẫn Cài Đặt & CI/CD GitHub Actions
- [`README.md`](../README.md) và `.env.example` hướng dẫn đầy đủ cài đặt MySQL 8, XAMPP/Laragon, SQLite dự phòng, bật `extension=gd`, chạy **Mailpit** (`127.0.0.1:1025` / web `8025`) hoặc `MAIL_MAILER=log`, và chạy scheduler `orders:expire-pending`.
- File [`.github/workflows/ci.yml`](../.github/workflows/ci.yml) tự động chạy `vendor/bin/pint --test`, `php artisan migrate:fresh --seed --force` và `php artisan test` trên container **MySQL 8.0** mỗi khi `push` hoặc `pull_request`.
