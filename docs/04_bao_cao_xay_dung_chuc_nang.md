# BÁO CÁO MỤC 3: XÂY DỰNG CHỨC NĂNG HỆ THỐNG (CHUẨN ĐẦU RA PLO3)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

- **Mã tài liệu**: FUNC-ETMS-v1.0 (Final Release)
- **Nền tảng**: Laravel 13 (PHP 8.3+) / Blade / Bootstrap 5.3 CDN
- **File Word đính kèm**: [04_bao_cao_xay_dung_chuc_nang.docx](file:///d:/NMNC/event-ticket/docs/04_bao_cao_xay_dung_chuc_nang.docx)
- **Chuẩn đầu ra**: PLO3 - Xây dựng và phát triển chức năng phần mềm

---

### BẢNG ĐÁNH GIÁ 7 TIÊU CHÍ MỤC 3 (PLO3)

| Tiêu Chí | Nội Dung Tiêu Chí Đánh Giá | Mức Độ Đạt Được | Minh Chứng Kỹ Thuật Trong Dự Án |
| :---: | :--- | :---: | :--- |
| **3.1** | Hoàn thành các chức năng CRUD chính | **100%** | CRUD Sự kiện, Hạng vé, Đơn hàng, Danh mục, Địa điểm, Người dùng |
| **3.2** | Tìm kiếm, lọc, sắp xếp hoặc phân trang dữ liệu | **100%** | Tìm kiếm từ khóa, lọc danh mục/thành phố/ngày, sắp xếp giá/ngày, phân trang 9/10/15 |
| **3.3** | Áp dụng Laravel MVC: route, controller, model, blade | **100%** | 4 route files độc lập (62 routes), Service layer, 8 Models relations, 2 Master layouts |
| **3.4** | Validation form nhập liệu | **100%** | 12 FormRequests độc lập, thông báo lỗi tiếng Việt UTF-8 chuẩn |
| **3.5** | Đăng nhập, đăng xuất, xác thực người dùng nếu phù hợp | **100%** | AuthController, băm Bcrypt, Throttle giới hạn 5 lần/phút, Remember me, Hồ sơ cá nhân |
| **3.6** | Giao diện Blade rõ ràng, dễ sử dụng, có layout/template phù hợp | **100%** | `layouts/app.blade.php`, `layouts/panel.blade.php`, partials alerts/errors, Bootstrap 5 CDN |
| **3.7** | Chức năng hoạt động ổn định, ít lỗi khi thao tác | **100%** | Khóa bi quan `lockForUpdate()`, soát vé 5 bước, xử lý ngoại lệ `BusinessException` |

---

### CHI TIẾT HIỆN THỰC HÓA 7 TIÊU CHÍ

#### 3.1. Hoàn Thành Các Chức Năng CRUD Chính
- **Sự kiện (Events CRUD)**: Ban tổ chức và Admin tạo sự kiện, tải ảnh banner lưu storage, xem chi tiết, cập nhật nội dung, chuyển đổi trạng thái (`draft` -> `pending` -> `published`/`cancelled`), hỗ trợ xóa mềm (`softDeletes`) khi đã có đơn hàng.
- **Hạng vé (Ticket Types CRUD)**: Ban tổ chức tạo các loại vé (VIP, Standard, Early Bird), cập nhật giá tiền VNĐ, điều chỉnh số lượng phát hành, giới hạn vé mỗi đơn và thời gian mở/đóng bán.
- **Đơn hàng (Orders CRUD)**: Khách hàng đặt vé, thanh toán giả lập, hủy đơn; Admin tra cứu và xem chi tiết đơn hàng toàn sàn.
- **Danh mục & Địa điểm (Categories & Venues CRUD)**: Admin thêm, sửa, xóa với cơ chế `RESTRICT` chặn xóa khi đang có sự kiện liên kết.
- **Người dùng (Users CRUD)**: Admin quản lý danh sách, tìm kiếm, chỉnh sửa vai trò (Role), kích hoạt hoặc khóa tài khoản (`is_active`).

#### 3.2. Tìm Kiếm, Lọc, Sắp Xếp & Phân Trang Dữ Liệu
- **Tìm kiếm đa điều kiện**: Khách hàng tìm kiếm theo từ khóa tiêu đề/mô tả sự kiện (`?search=...`), lọc theo danh mục (`?category=...`), lọc theo thành phố (`?city=...`), và lọc theo khoảng thời gian tổ chức (`?date_from=...&date_to=...`).
- **Sắp xếp**: Sắp xếp sự kiện theo ngày bắt đầu gần nhất (`start_time ASC`) hoặc theo giá vé thấp nhất (`withMin('ticketTypes', 'price')`).
- **Phân trang chuẩn mực**: Sử dụng `paginate()` kết hợp `withQueryString()` giữ nguyên bộ lọc: Storefront 9 sự kiện/trang dạng card lưới, Admin & Organizer 10-15 dòng/trang dạng bảng. Áp dụng `Paginator::useBootstrapFive()` tại `AppServiceProvider`.

#### 3.3. Áp Dụng Chuẩn Mực Mô Hình Laravel MVC
- **Routes (62 Routes)**: Tách thành 4 tệp độc lập `auth.php`, `storefront.php`, `organizer.php`, `admin.php`.
- **Controllers**: Tổ chức theo namespace `App\Http\Controllers\Auth`, `Storefront`, `Organizer`, `Admin`. Áp dụng tư tưởng Skinny Controller, trích xuất logic giao dịch phức tạp sang Service Layer (`OrderService`, `TicketCheckinService`).
- **Models**: 8 Eloquent models trong `app/Models/` định nghĩa quan hệ 2 chiều (`belongsTo`, `hasMany`, `hasManyThrough`), scopes truy vấn cục bộ và casting Enums.
- **Views**: Hệ thống Blade template kế thừa phân cấp, tái sử dụng partials và components.

#### 3.4. Validation Form Nhập Liệu (12 Form Requests)
Tách toàn bộ kiểm tra hợp lệ dữ liệu sang 12 Form Requests độc lập trong `app/Http/Requests/`:
```php
// app/Http/Requests/Organizer/EventRequest.php
public function rules(): array {
    return [
        'title' => ['required', 'string', 'max:255'],
        'category_id' => ['required', 'exists:categories,id'],
        'venue_id' => ['required', 'exists:venues,id'],
        'start_time' => ['required', 'date', 'after:now'],
        'end_time' => ['required', 'date', 'after:start_time'],
        'banner' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
    ];
}
```

#### 3.5. Đăng Nhập, Đăng Xuất & Xác Thực Người Dùng
- Mật khẩu băm Bcrypt 12 rounds an toàn.
- Chống brute-force: Giới hạn tối đa 5 lần đăng nhập sai trong 1 phút (`RateLimiter::hit(...)`), tự động khóa tạm thời và thông báo số giây phải chờ.
- Hỗ trợ `Remember Me` sinh token ngẫu nhiên lưu trong cột `remember_token`.
- Khách hàng và Ban tổ chức có thể xem và cập nhật hồ sơ cá nhân, đổi mật khẩu tại `/profile`.

#### 3.6. Giao Diện Blade Rõ Ràng, Có Layout Phù Hợp
- 2 Master Layouts: `layouts/app.blade.php` (Storefront cho khách hàng) và `layouts/panel.blade.php` (Dashboard Admin & Organizer có Sidebar).
- Partials dùng chung: `partials/alerts.blade.php` (thông báo flash message tự tắt sau 5s); `partials/form-errors.blade.php` (danh sách lỗi nhập liệu).
- Toàn bộ giao diện dùng Bootstrap 5.3 CDN và Bootstrap Icons CDN (Zero-node build, không cần npm).

#### 3.7. Chức Năng Hoạt Động Ổn Định, Ít Lỗi Khi Thao Tác
- **Khóa bi quan (`lockForUpdate`)**: Chống bán vượt số lượng vé (Race Condition / Overselling).
- **Tự động thu hồi vé**: Hủy đơn sau 15 phút chưa thanh toán và hoàn trả vé vào kho (`orders:expire-pending`).
- **Soát vé 5 bước**: `TicketCheckinService` xác thực nghiêm ngặt ngăn chặn vé giả, vé hủy, vé đã dùng.
- **Xử lý ngoại lệ**: Lớp `BusinessException` hiển thị thông báo lỗi nghiệp vụ thân thiện, không làm sập ứng dụng.
