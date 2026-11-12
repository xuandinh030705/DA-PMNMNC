# BÁO CÁO MỤC 3: XÂY DỰNG CHỨC NĂNG HỆ THỐNG (CHUẨN ĐẦU RA PLO3)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

- **Mã tài liệu**: FUNC-ETMS-v1.1
- **Nền tảng**: Laravel 13 (PHP 8.3+) / Blade / Bootstrap 5.3 CDN
- **File Word đính kèm**: [04_bao_cao_xay_dung_chuc_nang.docx](./04_bao_cao_xay_dung_chuc_nang.docx)
- **Chuẩn đầu ra**: PLO3 - Xây dựng và phát triển chức năng phần mềm (đối chiếu `tailieu/2.jpg`)

---

### BẢNG ĐÁNH GIÁ 7 TIÊU CHÍ MỤC 3 (PLO3)

| Tiêu Chí | Nội Dung Tiêu Chí Đánh Giá | Mức Độ Đạt Được | Minh Chứng Kỹ Thuật Trong Dự Án |
| :---: | :--- | :---: | :--- |
| **3.1** | Hoàn thành các chức năng CRUD chính | **100%** | CRUD Sự kiện, Hạng vé, Đơn hàng, Danh mục, Địa điểm, Người dùng |
| **3.2** | Tìm kiếm, lọc, sắp xếp hoặc phân trang dữ liệu | **100%** | Tìm kiếm `q`, lọc `category`/`city`/`from`/`to`, sắp xếp `sort`, phân trang 9/10/12 |
| **3.3** | Áp dụng Laravel MVC: route, controller, model, blade | **100%** | 4 route files độc lập (57 app routes), Service layer, 8 Models relations, 2 Master layouts |
| **3.4** | Validation form nhập liệu | **100%** | 11 FormRequests độc lập, thông báo lỗi tiếng Việt UTF-8 chuẩn |
| **3.5** | Đăng nhập, đăng xuất, xác thực người dùng nếu phù hợp | **100%** | `AuthController`, băm Bcrypt, middleware `throttle:5,1`, Remember me, Hồ sơ cá nhân |
| **3.6** | Giao diện Blade rõ ràng, dễ sử dụng, có layout/template phù hợp | **100%** | `layouts/app.blade.php`, `layouts/panel.blade.php`, partials alerts/errors, Bootstrap 5 CDN |
| **3.7** | Chức năng hoạt động ổn định, ít lỗi khi thao tác | **100%** | Khóa bi quan `lockForUpdate()`, soát vé 5 bước, `orders:expire-pending` theo lịch, `BusinessException` |

---

### CHI TIẾT HIỆN THỰC HÓA 7 TIÊU CHÍ

#### 3.1. Hoàn Thành Các Chức Năng CRUD Chính
- **Sự kiện (Events CRUD)**: Ban tổ chức tạo sự kiện nháp, tải ảnh banner lưu disk `public`, xem chi tiết, cập nhật khi ở trạng thái `draft`, xóa bản nháp (`SoftDeletes`), gửi duyệt (`pending`); Admin xem chi tiết, phê duyệt (`published`), từ chối (`draft`) hoặc hủy (`cancelled`).
- **Hạng vé (Ticket Types CRUD)**: Ban tổ chức tạo các loại vé (`VIP`, `Standard`, ...), cập nhật giá tiền VNĐ, số lượng phát hành (không nhỏ hơn `sold`), giới hạn vé mỗi đơn (`max_per_order`) và thời gian mở/đóng bán.
- **Đơn hàng (Orders)**: Khách hàng đặt vé giữ chỗ, thanh toán giả lập, hủy đơn `pending`; Organizer và Admin tra cứu, lọc và xem chi tiết đơn hàng.
- **Danh mục & Địa điểm (Categories & Venues CRUD)**: Admin thêm, sửa, xóa kèm kiểm tra `$category->events()->exists()` / `$venue->events()->exists()` và ràng buộc khóa ngoại `RESTRICT`.
- **Người dùng (Users)**: Admin quản lý danh sách, tìm kiếm/lọc theo vai trò, cập nhật vai trò và khóa/mở khóa tài khoản (`is_active`).

#### 3.2. Tìm Kiếm, Lọc, Sắp Xếp & Phân Trang Dữ Liệu
- **Tìm kiếm đa điều kiện trên Storefront (`StorefrontController@index`)**: Tìm theo tiêu đề (`?q=...`), lọc theo danh mục (`?category=...`), lọc theo thành phố (`?city=...`), lọc theo khoảng thời gian (`?from=...&to=...`).
- **Sắp xếp (`?sort=...`)**: Hỗ trợ `date_asc`, `date_desc`, `price_asc`, `price_desc` kết hợp `withMin('ticketTypes as min_price', 'price')`.
- **Phân trang chuẩn mực**: Sử dụng `paginate()->withQueryString()` (9 sự kiện/trang ở Storefront, 12 vé/trang ở `tickets.index`, 10 dòng/trang ở các bảng quản trị) với `Paginator::useBootstrapFive()` tại `AppServiceProvider`.

#### 3.3. Áp Dụng Chuẩn Mực Mô Hình Laravel MVC
- **Routes (57 app routes)**: Tách thành 4 tệp độc lập `auth.php`, `storefront.php`, `organizer.php`, `admin.php` được nạp từ `routes/web.php`.
- **Controllers & Services**: Tổ chức theo namespace `App\Http\Controllers\Auth`, `Storefront`, `Organizer`, `Admin`; tách nghiệp vụ sang `OrderService` và `TicketCheckinService`.
- **Models**: 8 Eloquent models trong `app/Models/` định nghĩa quan hệ 2 chiều, scopes cục bộ và accessors.
- **Views**: Hệ thống Blade template kế thừa phân cấp từ `layouts/app.blade.php` và `layouts/panel.blade.php`.

#### 3.4. Validation Form Nhập Liệu (11 Form Requests)
Toàn bộ validation được tách sang 11 Form Requests độc lập trong `app/Http/Requests/` (Auth: 3, Storefront: 2, Organizer: 3, Admin: 3), mỗi class đều khai báo đủ `rules()`, `attributes()` và `messages()` bằng tiếng Việt có dấu.

#### 3.5. Đăng Nhập, Đăng Xuất & Xác Thực Người Dùng
- Mật khẩu băm Bcrypt qua `Hash::make()` và cast `'hashed'` trên `User`.
- Chống brute-force bằng route middleware `throttle:5,1` trên route `POST /login` (`login.store`).
- Kiểm tra `is_active` ngay khi đăng nhập và trên mỗi request qua middleware `CheckActive`.
- Hỗ trợ `Remember Me` và trang hồ sơ cá nhân `/profile` (đổi họ tên, SĐT, mật khẩu kiểm tra `current_password`).

#### 3.6. Giao Diện Blade Rõ Ràng, Có Layout Phù Hợp
- 2 Master Layouts đồng bộ CSS: `layouts/app.blade.php` (Storefront) và `layouts/panel.blade.php` (Admin & Organizer Sidebar).
- Partials dùng chung: `partials/alerts.blade.php`, `partials/form-errors.blade.php`.
- Toàn bộ giao diện dùng Bootstrap 5.3 CDN và Bootstrap Icons CDN (Zero-node build).

#### 3.7. Chức Năng Hoạt Động Ổn Định, Ít Lỗi Khi Thao Tác
- **Khóa bi quan (`lockForUpdate`)**: Chống bán vượt số lượng vé khi nhiều người đặt đồng thời.
- **Thu hồi vé hết hạn 15 phút**: Kết hợp Lazy Check (`checkAndExpire` khi xem đơn và xử lý hủy an toàn khi khách bấm thanh toán muộn) cùng Artisan command `orders:expire-pending` đã đăng ký lịch chạy mỗi phút trong `routes/console.php`.
- **Soát vé 5 bước**: `TicketCheckinService` ngăn chặn vé không tồn tại, đơn chưa thanh toán, vé không thuộc Organizer, vé đã hủy hoặc đã dùng.
- **Xử lý ngoại lệ**: Lớp `BusinessException` tự render redirect kèm thông báo lỗi thân thiện.
