# BÁO CÁO MỤC 4 & 5: KIỂM THỬ, BẢO MẬT & TRIỂN KHAI (CHUẨN ĐẦU RA PLO3)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

- **Mã tài liệu**: TEST-DEPLOY-ETMS-v1.1
- **Nền tảng**: Laravel 13 (PHP 8.3+) / SQLite & MySQL
- **File Word đính kèm**: [05_bao_cao_kiem_thu_bao_mat_trien_khai.docx](./05_bao_cao_kiem_thu_bao_mat_trien_khai.docx)
- **Chuẩn đầu ra**: PLO3 - Kiểm thử, bảo mật và triển khai phần mềm (đối chiếu `tailieu/3.jpg`)

---

### BẢNG ĐÁNH GIÁ 8 TIÊU CHÍ MỤC 4 & 5 (PLO3)

| Nhóm | Tiêu Chí | Nội Dung Tiêu Chí Đánh Giá | Mức Độ | Minh Chứng Kỹ Thuật |
| :---: | :---: | :--- | :---: | :--- |
| **Kiểm thử** | **4.1** | Kiểm tra dữ liệu đầu vào và thông báo lỗi rõ ràng | **100%** | 11 FormRequests, hiển thị lỗi dưới từng input form |
| **Kiểm thử** | **4.2** | Xử lý lỗi: trống, sai định dạng, trùng mã, không tìm thấy | **100%** | Unique validation, trang lỗi 403/404/419/500, `BusinessException` |
| **Bảo mật** | **4.3** | Áp dụng CSRF, middleware, auth, phân quyền | **100%** | 100% `@csrf`, không `{!! !!}` ở Blade, `CheckRole`, `CheckActive`, 3 Policies |
| **Kiểm thử** | **4.4** | Minh chứng kiểm thử chức năng | **100%** | 37 tests passed (104 assertions), `docs/testing/*` |
| **Triển khai** | **5.1** | Project chạy được trên môi trường Laravel chuẩn | **100%** | PHP 8.5.9, Laravel 13, `.env` sẵn sàng, zero-node Bootstrap CDN |
| **Triển khai** | **5.2** | Database, migration, seed/script dữ liệu mẫu | **100%** | 10 migrations (7 nghiệp vụ), `DatabaseSeeder` nạp 8 users, 5 categories, 4 venues, 8 events, 4 orders |
| **Triển khai** | **5.3** | Hướng dẫn cài đặt và chạy hệ thống | **100%** | `README.md` hướng dẫn Windows, Linux, macOS, switch MySQL |
| **Triển khai** | **5.4** | Mã nguồn tổ chức rõ, nộp đầy đủ, không thiếu file quan trọng | **100%** | Cây thư mục chuẩn Laravel, `.env.example`, tài liệu & sơ đồ đầy đủ |

---

### MỤC 4: KIỂM THỬ & BẢO MẬT HỆ THỐNG

#### 4.1. Kiểm Tra Dữ Liệu Đầu Vào & Thông Báo Lỗi Rõ Ràng
- Dữ liệu POST/PUT đều đi qua 11 Form Requests trước khi vào Controller.
- Khi có lỗi validation, hệ thống giữ lại giá trị cũ (`old()`), trả về `$errors` và hiển thị qua `partials/form-errors.blade.php` cùng class `.is-invalid`.

#### 4.2. Xử Lý Các Trường Hợp Lỗi Phổ Biến
- **Trống / Sai định dạng**: Kiểm tra qua rules `required`, `email`, `integer`, `numeric`, `date`, `after`, `min`, `max`, `mimes`.
- **Trùng lặp**: Rule `unique` trên email/danh mục; tự động tạo slug duy nhất; mã đơn `ORD-` và mã vé `TKT-` kiểm tra trùng qua vòng lặp `do ... while`.
- **Không tìm thấy dữ liệu**: Route Model Binding và kiểm tra trạng thái công khai trả về trang 404 tùy biến (`resources/views/errors/404.blade.php`).
- **Lỗi nghiệp vụ**: Ném `BusinessException` và hiển thị thông báo tiếng Việt qua flash `error`.

#### 4.3. Bảo Mật Toàn Diện: CSRF, Middleware, Auth & Phân Quyền
- **CSRF**: 100% form `POST`/`PUT`/`DELETE` trong `resources/views/` đều có `@csrf`.
- **XSS**: Toàn bộ output Blade sử dụng `{{ ... }}`, không dùng `{!! !!}`.
- **Middleware & Policies (chống IDOR)**: `CheckRole` chặn sai vai trò (403); `CheckActive` ngắt phiên tài khoản bị khóa; `EventPolicy`, `TicketTypePolicy`, `OrderPolicy` chặn truy cập chéo dữ liệu người khác.

#### 4.4. Minh Chứng Kiểm Thử Chức Năng (37 Tests / 104 Assertions)
Toàn bộ bộ kiểm thử tự động vượt qua 100% khi chạy `php artisan test` / `php vendor/bin/phpunit --testdox`:

| Test Suite / Class | Số Tests | Số Assertions | Phạm Vi Kiểm Định |
| :--- | :---: | :---: | :--- |
| `Tests\Unit\DomainModelsTest` | 2 | 9 | Accessor `remaining`, helper vai trò `User`, đồng bộ hằng số & Enum |
| `Tests\Feature\AuthTest` | 6 | 24 | Đăng ký, đăng nhập đúng/sai, chặn user bị khóa, cập nhật profile, đăng xuất |
| `Tests\Feature\AuthorizationTest` | 5 | 8 | Chặn truy cập trái quyền Admin/Organizer, chống IDOR Event/Order, chặn admin tự khóa/tự hạ quyền |
| `Tests\Feature\StorefrontTest` | 4 | 9 | Duyệt sự kiện công khai, tìm kiếm từ khóa, lọc danh mục/thành phố, 404 với sự kiện nháp |
| `Tests\Feature\OrderAndPaymentTest` | 9 | 26 | Đặt vé giữ chỗ 15p, kiểm tra 0 vé / quá giới hạn / hết tồn, thanh toán sinh vé, hủy đơn hoàn vé, command `orders:expire-pending` và thanh toán đơn quá hạn |
| `Tests\Feature\CheckinTest` | 5 | 9 | Soát vé hợp lệ, không tồn tại, đơn chưa thanh toán, vé đã dùng, vé của Organizer khác |
| `Tests\Feature\AdminManagementTest` | 6 | 19 | Duyệt/từ chối/hủy sự kiện, chặn xóa danh mục có sự kiện, khóa/mở khóa user, dashboard & CRUD danh mục/địa điểm |
| **Tổng cộng** | **37** | **104** | **100% PASSED (0 failures)** |

---

### MỤC 5: TRIỂN KHAI DỰ ÁN

#### 5.1. Môi Trường Chạy Chuẩn & Triển Khai Không Cần Node.js
- Môi trường chạy thực tế: PHP 8.5.9 (yêu cầu `>= 8.3`), Composer 2.10+, SQLite 3 hoặc MySQL 8.0+.
- Triển khai Zero-Node qua Bootstrap 5.3 CDN & Bootstrap Icons CDN.

#### 5.2. Cơ Sở Dữ Liệu & Nạp Dữ Liệu Mẫu (Seed Data)
- Lệnh khởi tạo: `php artisan migrate:fresh --seed --force` và `php artisan storage:link`.
- Dữ liệu mẫu thực tế từ `DatabaseSeeder`: 8 tài khoản (`admin@example.com`, `organizer1@example.com`, `organizer2@example.com`, `customer1..5@example.com`), 5 danh mục, 4 địa điểm tổ chức, 8 sự kiện (5 `published`, 1 `draft`, 1 `pending`, 1 `cancelled`) với 16 loại vé, 4 đơn hàng (2 `paid`, 1 `pending`, 1 `cancelled`) và 3 vé điện tử (1 `used`, 2 `valid`).

#### 5.3. Hướng Dẫn Cài Đặt & Chuyển Đổi Hệ Quản Trị CSDL
- File [`README.md`](../README.md) và `.env.example` hướng dẫn chi tiết cho Windows, Linux/macOS và chuyển đổi sang MySQL (XAMPP/Laragon).

#### 5.4. Tổ Chức Mã Nguồn & Tính Đầy Đủ
- Mã nguồn, file cấu hình môi trường (`.env.example`), thư viện phụ thuộc (`vendor/`), CSDL SQLite (`database/database.sqlite`), liên kết `public/storage`, tài liệu và sơ đồ đã được tổ chức đầy đủ.
