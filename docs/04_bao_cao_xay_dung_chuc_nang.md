# BÁO CÁO MỤC 3: XÂY DỰNG CHỨC NĂNG HỆ THỐNG (CHUẨN ĐẦU RA PLO3)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

- **Mã tài liệu**: FUNC-ETMS-v2.0
- **Nền tảng**: Laravel 13 (PHP 8.3+) / Blade / Bootstrap 5.3 CDN / MySQL 8.0
- **File Word đính kèm**: [04_bao_cao_xay_dung_chuc_nang.docx](./04_bao_cao_xay_dung_chuc_nang.docx)
- **Chuẩn đầu ra**: PLO3 - Xây dựng và phát triển chức năng phần mềm (đối chiếu `tailieu/2.jpg`)

---

### BẢNG ĐÁNH GIÁ 7 TIÊU CHÍ MỤC 3 (PLO3)

| Tiêu Chí | Nội Dung Tiêu Chí Đánh Giá | Mức Độ Đạt Được | Minh Chứng Kỹ Thuật Trong Dự Án |
| :---: | :--- | :---: | :--- |
| **3.1** | Hoàn thành các chức năng CRUD chính | **100%** | CRUD Sự kiện, Hạng vé (kèm `zone`, `description`), Đơn hàng, Danh mục, Địa điểm, Người dùng |
| **3.2** | Tìm kiếm, lọc, sắp xếp hoặc phân trang dữ liệu | **100%** | Tìm kiếm `q`, lọc `category`/`city`/`from`/`to`, sắp xếp `sort`, phân trang 9/10/12 |
| **3.3** | Áp dụng Laravel MVC: route, controller, model, blade | **100%** | 4 route files (**58 app routes**), 4 Services, `OrderPaidMail`, 8 Models + 4 Enums, 2 Master layouts |
| **3.4** | Validation form nhập liệu | **100%** | 11 FormRequests độc lập, thông báo lỗi tiếng Việt UTF-8 chuẩn |
| **3.5** | Đăng nhập, đăng xuất, xác thực người dùng nếu phù hợp | **100%** | `AuthController`, băm Bcrypt, middleware `throttle:5,1`, `CheckActive`, Remember me, Hồ sơ cá nhân |
| **3.6** | Giao diện Blade rõ ràng, dễ sử dụng, có layout/template phù hợp | **100%** | `layouts/app.blade.php`, `layouts/panel.blade.php`, `pdf/ticket`, `emails/order-paid`, Bootstrap 5 CDN |
| **3.7** | Chức năng hoạt động ổn định, ít lỗi khi thao tác | **100%** | `lockForUpdate()`, QR HMAC-SHA256, PDF RAM, Camera Check-in 6 bước, Admin hủy cascade, 103 tests pass |

---

### CHI TIẾT HIỆN THỰC HÓA 7 TIÊU CHÍ

#### 3.1. Hoàn Thành Các Chức Năng CRUD Chính
- **Sự kiện (Events CRUD)**: Ban tổ chức tạo sự kiện nháp, tải ảnh banner lưu disk `public`, xem chi tiết, cập nhật khi ở trạng thái `draft`, xóa bản nháp (`SoftDeletes`), gửi duyệt (`pending`); Admin xem chi tiết, phê duyệt (`published`), từ chối (`draft`) hoặc hủy (`cancelled` với cascade tự hủy đơn `pending` hoàn vé & hủy vé `valid`).
- **Hạng vé (Ticket Types CRUD)**: Ban tổ chức tạo/sửa/xóa các loại vé (`VIP`, `Standard`, ...), thiết lập khu vực chỗ ngồi (`zone`), mô tả quyền lợi (`description`), giá bán VNĐ, số lượng phát hành (không nhỏ hơn `sold`), giới hạn mỗi đơn (`max_per_order`) và thời gian mở/đóng bán.
- **Đơn hàng & Vé điện tử (Orders & Tickets)**: Khách hàng đặt vé giữ chỗ 15 phút, thanh toán giả lập, nhận email `OrderPaidMail` kèm PDF đính kèm, xem ảnh mã QR ký số HMAC-SHA256, tải vé PDF khổ A5 (`/my-tickets/{ticket}/pdf`), hủy đơn `pending`; Organizer và Admin tra cứu, lọc và xem chi tiết đơn hàng (đều hiển thị `zone`, `description` và nhân viên soát vé `checkedInBy`).
- **Danh mục & Địa điểm (Categories & Venues CRUD)**: Admin thêm, sửa, xóa kèm kiểm tra tồn tại sự kiện và ràng buộc khóa ngoại `RESTRICT`.
- **Người dùng (Users)**: Admin quản lý danh sách, tìm kiếm/lọc theo vai trò, cập nhật vai trò (`UserRole`) và khóa/mở khóa tài khoản (`is_active`).

#### 3.2. Tìm Kiếm, Lọc, Sắp Xếp & Phân Trang Dữ Liệu
- **Tìm kiếm đa điều kiện trên Storefront (`StorefrontController@index`)**: Tìm theo tiêu đề (`?q=...`), lọc theo danh mục (`?category=...`), thành phố (`?city=...`), khoảng thời gian (`?from=...&to=...`).
- **Sắp xếp (`?sort=...`)**: Hỗ trợ `date_asc`, `date_desc`, `price_asc`, `price_desc` kết hợp `withMin('ticketTypes as min_price', 'price')`.
- **Phân trang chuẩn mực**: Dùng `paginate()->withQueryString()` (9 sự kiện/trang ở Storefront, 12 vé/trang ở `tickets.index`, 10 dòng/trang ở Admin/Organizer) với `Paginator::useBootstrapFive()`.

#### 3.3. Áp Dụng Chuẩn Mực Mô Hình Laravel MVC
- **Routes (58 app routes)**: Tách thành 4 tệp độc lập `auth.php`, `storefront.php`, `organizer.php`, `admin.php` nạp từ `routes/web.php`.
- **Controllers, Services & Mailable**: Tổ chức theo 4 namespaces (`Auth`, `Storefront`, `Organizer`, `Admin`); tách nghiệp vụ phức tạp sang 4 Services (`OrderService`, `TicketCheckinService`, `QrTicketService`, `TicketPdfService`) và Mailable `OrderPaidMail`.
- **Models & Enums**: 8 Eloquent models trong `app/Models/` kết hợp 4 Backed Enums (`UserRole`, `EventStatus`, `OrderStatus`, `TicketStatus`), quan hệ 2 chiều, scopes và accessors.
- **Views**: Kế thừa phân cấp từ `layouts/app.blade.php`, `layouts/panel.blade.php` cùng view chuyên biệt cho PDF (`pdf/ticket.blade.php`) và Email (`emails/order-paid.blade.php`).

#### 3.4. Validation Form Nhập Liệu (11 Form Requests)
Toàn bộ validation được tách sang 11 Form Requests độc lập trong `app/Http/Requests/` (Auth: 3, Storefront: 2, Organizer: 3, Admin: 3), khai báo đủ `rules()`, `attributes()` và `messages()` bằng tiếng Việt có dấu (gồm validate `zone` max 100, `description` max 1000 trong `TicketTypeRequest` và payload QR trong `CheckinRequest`).

#### 3.5. Đăng Nhập, Đăng Xuất & Xác Thực Người Dùng
- Mật khẩu băm Bcrypt qua cast `'hashed'` trên `User`.
- Chống brute-force bằng `throttle:5,1` trên `POST /login` (`login.store`).
- Kiểm tra `is_active` ngay khi đăng nhập và trên mỗi request qua middleware `CheckActive`.
- Hỗ trợ `Remember Me` và trang hồ sơ cá nhân `/profile` (kiểm tra `current_password` khi đổi mật khẩu).

#### 3.6. Giao Diện Blade Rõ Ràng, Có Layout Phù Hợp
- 2 Master Layouts đồng bộ CSS (`layouts/app.blade.php`, `layouts/panel.blade.php`) và 2 partials dùng chung (`alerts`, `form-errors`).
- Toàn bộ giao diện dùng Bootstrap 5.3 CDN & Bootstrap Icons CDN (Zero-node build), còn chức năng quét Camera QR tích hợp thư viện `public/js/html5-qrcode.min.js` chạy offline.

#### 3.7. Chức Năng Hoạt Động Ổn Định, Ít Lỗi Khi Thao Tác
- **Khóa bi quan (`lockForUpdate()`)**: Chống overselling khi đặt vé, chống trùng lặp khi thanh toán/check-in, đảm bảo an toàn khi Admin hủy sự kiện cascade.
- **Thu hồi vé hết hạn 15 phút**: Kết hợp Lazy Check và Artisan command `orders:expire-pending` (lập lịch mỗi phút trong `routes/console.php`).
- **Soát vé 6 bước & QR HMAC-SHA256**: Xác thực mã vé, chữ ký HMAC-SHA256 (`hash_equals()`), trạng thái `paid`, quyền Organizer, trạng thái chưa hủy/chưa dùng, và lưu nhân viên soát vé `checked_in_by`.
- **Xuất PDF trong RAM & Chịu lỗi Email**: `TicketPdfService` tạo PDF khổ A5 trong bộ nhớ không ghi đĩa; `OrderController@pay` bọc `try/catch` khi gửi `OrderPaidMail` để lỗi SMTP không làm hỏng đơn đã thanh toán.
- **Xử lý ngoại lệ**: `BusinessException` tự render thông báo tiếng Việt thân thiện thay vì lỗi 500.
