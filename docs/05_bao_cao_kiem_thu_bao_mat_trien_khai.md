# BÁO CÁO MỤC 4 & 5: KIỂM THỬ, BẢO MẬT & TRIỂN KHAI (CHUẨN ĐẦU RA PLO3)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

- **Mã tài liệu**: TEST-DEPLOY-ETMS-v1.0 (Final Release)
- **Nền tảng**: Laravel 13 (PHP 8.3+) / SQLite & MySQL
- **File Word đính kèm**: [05_bao_cao_kiem_thu_bao_mat_trien_khai.docx](file:///d:/NMNC/event-ticket/docs/05_bao_cao_kiem_thu_bao_mat_trien_khai.docx)
- **Chuẩn đầu ra**: PLO3 - Kiểm thử, bảo mật và triển khai phần mềm

---

### BẢNG ĐÁNH GIÁ 8 TIÊU CHÍ MỤC 4 & 5 (PLO3)

| Nhóm | Tiêu Chí | Nội Dung Tiêu Chí Đánh Giá | Mức Độ | Minh Chứng Kỹ Thuật |
| :---: | :---: | :--- | :---: | :--- |
| **Kiểm thử** | **4.1** | Kiểm tra dữ liệu đầu vào và thông báo lỗi rõ ràng | **100%** | 12 FormRequests, hiển thị lỗi dưới từng ô input form |
| **Kiểm thử** | **4.2** | Xử lý lỗi: trống, sai định dạng, trùng mã, không tìm thấy | **100%** | Unique validation, ModelNotFound 404, BusinessException |
| **Bảo mật** | **4.3** | Áp dụng CSRF, middleware, auth, phân quyền | **100%** | 100% `@csrf`, CheckRole, CheckActive, EventPolicy, OrderPolicy |
| **Kiểm thử** | **4.4** | Minh chứng kiểm thử chức năng | **100%** | 33 Feature tests passed (82 assertions), `docs/testing/*` |
| **Triển khai** | **5.1** | Project chạy được trên môi trường Laravel chuẩn | **100%** | PHP 8.3+, Laravel 13, zero-node build Bootstrap CDN |
| **Triển khai** | **5.2** | Database, migration, seed/script dữ liệu mẫu | **100%** | 7 migrations, DatabaseSeeder nạp 8 users, 8 events, 4 orders |
| **Triển khai** | **5.3** | Hướng dẫn cài đặt và chạy hệ thống | **100%** | README.md hướng dẫn Windows, Linux, macOS, switch MySQL |
| **Triển khai** | **5.4** | Mã nguồn tổ chức rõ, nộp đầy đủ, không thiếu file quan trọng | **100%** | Cây thư mục chuẩn Laravel, Git repo sạch sẽ, `.env.example` |

---

### MỤC 4: KIỂM THỬ & BẢO MẬT HỆ THỐNG

#### 4.1. Kiểm Tra Dữ Liệu Đầu Vào & Thông Báo Lỗi Rõ Ràng
- Dữ liệu gửi lên từ các phương thức POST/PUT đều đi qua Form Requests trước khi vào Controller.
- Khi có lỗi, hệ thống giữ lại giá trị cũ (`old()`) và gửi lại `$errors`.
- Giao diện Blade hiển thị trực quan bằng CSS `.is-invalid` và `.invalid-feedback` của Bootstrap 5 ngay dưới trường lỗi.

#### 4.2. Xử Lý Các Trường Hợp Lỗi Phổ Biến
- **Trống / Sai định dạng**: Bắt buộc qua các rules `required`, `email`, `numeric`, `date`, `min`, `max`.
- **Trùng lặp**: Rule `unique:users,email` chặn trùng email; slug tự động sinh và chống trùng; mã đơn `ORD-` và mã vé `TKT-` sinh ngẫu nhiên và kiểm tra tồn tại qua vòng lặp `while`.
- **Không tìm thấy dữ liệu**: Route Model Binding ném ngoại lệ `ModelNotFoundException` và chuyển hướng đến trang lỗi 404 thân thiện (`resources/views/errors/404.blade.php`).
- **Lỗi nghiệp vụ**: Ném qua `BusinessException` (mua quá số lượng khả dụng, đơn hết hạn) và hiển thị flash message màu đỏ.

#### 4.3. Bảo Mật Toàn Diện: CSRF, Middleware, Auth & Phân Quyền
- **Bảo vệ CSRF**: 100% các thẻ `<form>` thực hiện hành động POST, PUT, DELETE đều nhúng `@csrf`.
- **Middleware Tầng Định Tuyến**:
  - `CheckRole`: Chặn khách hàng truy cập `/organizer/*` hoặc `/admin/*` (trả về lỗi 403 Forbidden).
  - `CheckActive`: Kiểm tra ngay lập tức trạng thái `is_active`. Nếu bị khóa, tự động hủy phiên (Logout) và đẩy về trang đăng nhập.
- **Policies Tầng Đối Tượng (Chống IDOR)**: `EventPolicy`, `TicketTypePolicy`, `OrderPolicy` đảm bảo mỗi người dùng chỉ thao tác trên tài nguyên của mình.

#### 4.4. Minh Chứng Kiểm Thử Chức Năng (33 Feature Tests)
Toàn bộ hệ thống vượt qua 33 ca kiểm thử tự động (82 assertions, 0 failures):
```text
   PASS  Tests\Feature\AdminManagementTest (7 tests)
   PASS  Tests\Feature\AuthTest (5 tests)
   PASS  Tests\Feature\AuthorizationTest (5 tests)
   PASS  Tests\Feature\CheckinTest (5 tests)
   PASS  Tests\Feature\OrderAndPaymentTest (6 tests)
   PASS  Tests\Feature\StorefrontTest (5 tests)

  Tests:    33 passed (82 assertions)
  Duration: 1.25s
```

---

### MỤC 5: TRIỂN KHAI DỰ ÁN

#### 5.1. Môi Trường Chạy Chuẩn & Triển Khai Không Cần Node.js
- Yêu cầu môi trường: PHP >= 8.2 (khuyến nghị 8.3), Composer 2.x, SQLite 3 hoặc MySQL 8.0+.
- Triển khai Zero-Node: Toàn bộ CSS/JS dùng qua CDN (Bootstrap 5.3 & Bootstrap Icons), loại bỏ hoàn toàn `npm install` hay `npm run build`, khởi chạy ngay lập tức.

#### 5.2. Cơ Sở Dữ Liệu & Nạp Dữ Liệu Mẫu (Seed Data)
- Lệnh nạp dữ liệu một bước: `php artisan migrate:fresh --seed --force`
- Dữ liệu mẫu khởi tạo: 8 tài khoản (1 Admin, 2 Organizer, 3 Customer, 2 tài khoản bị khóa), 5 danh mục, 5 địa điểm tổ chức, 8 sự kiện với 16 loại vé, 4 đơn hàng và 3 vé điện tử đã kích hoạt.

#### 5.3. Hướng Dẫn Cài Đặt & Chuyển Đổi Hệ Quản Trị CSDL
- Tệp [README.md](file:///d:/NMNC/event-ticket/README.md) hướng dẫn chi tiết từng câu lệnh khởi chạy trên Windows (PowerShell/CMD), Linux và macOS.
- Hướng dẫn chuyển đổi sang MySQL (XAMPP/Laragon) qua cấu hình biến `.env`:
  `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=event_ticket`.

#### 5.4. Tổ Chức Mã Nguồn & Tính Đầy Đủ
- Dự án được bàn giao 100% đầy đủ tệp tin, được quản lý chặt chẽ bằng Git repository với lịch sử commit rõ ràng, `.env.example` đầy đủ cấu hình mẫu, không thiếu bất kỳ file nào.
