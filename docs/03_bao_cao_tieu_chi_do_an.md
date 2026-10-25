# BÁO CÁO NGHIỆM THU ĐỒ ÁN THEO 5 TIÊU CHÍ ĐÁNH GIÁ
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

- **Mã tài liệu**: CRIT-ETMS-v1.0 (Final Release)
- **Nền tảng**: Laravel 13 (PHP 8.3+) / SQLite & MySQL
- **File Word đính kèm**: [03_bao_cao_tieu_chi_do_an.docx](file:///d:/NMNC/event-ticket/docs/03_bao_cao_tieu_chi_do_an.docx)
- **Đối chiếu**: Bám sát 5 tiêu chuẩn kỹ thuật cốt lõi trong phiếu đánh giá đồ án.

---

### BẢNG TỔNG QUAN ĐÁNH GIÁ 5 TIÊU CHÍ

| STT | Nội Dung Tiêu Chí Đánh Giá | Mức Độ Đạt Được | Minh Chứng Mã Nguồn & Tài Liệu |
| :---: | :--- | :---: | :--- |
| **Tiêu chí 1** | Xác định đối tượng người dùng, vai trò, dữ liệu cần quản lý, quy trình nghiệp vụ, phạm vi và ràng buộc | **100% Hoàn thành** | `docs/01_phan_tich_yeu_cau.md`, `app/Enums/UserRole.php`, Quy tắc nghiệp vụ BR-01..04 |
| **Tiêu chí 2** | Thiết kế CSDL hợp lý, có ERD và quan hệ giữa các bảng | **100% Hoàn thành** | `docs/diagrams/05_erd.puml`, `docs/02_thiet_ke_co_so_du_lieu.md`, CSDL chuẩn 3NF |
| **Tiêu chí 3** | Sử dụng migration, model và relationship đúng chuẩn Laravel | **100% Hoàn thành** | 7 Migrations, 8 Eloquent Models quan hệ 2 chiều, Eager Loading, Pessimistic Locking |
| **Tiêu chí 4** | Thiết kế phân quyền, vai trò người dùng nếu hệ thống cần | **100% Hoàn thành** | Middleware `CheckRole`, `CheckActive`, 3 Policies (`EventPolicy`, `OrderPolicy`), Blade `@can` |
| **Tiêu chí 5** | Tổ chức route, controller, view rõ ràng, dễ bảo trì | **100% Hoàn thành** | 4 route files tách biệt (62 routes), Service Pattern, 12 FormRequests, Blade CDN zero-npm |

---

### TIÊU CHÍ 1: XÁC ĐỊNH ĐỐI TƯỢNG NGƯỜI DÙNG, VAI TRÒ, DỮ LIỆU CẦN QUẢN LÝ, QUY TRÌNH NGHIỆP VỤ, PHẠM VI VÀ RÀNG BUỘC

#### 1.1. Đối tượng Người dùng & Vai trò (User Roles)
Hệ thống xác định rõ 4 đối tượng tác tử tham gia vào quy trình, được định nghĩa bằng PHP Enum (`app/Enums/UserRole.php`):
1. **Khách vãng lai (Guest)**: Người dùng chưa đăng nhập; xem danh sách sự kiện đang mở bán, tìm kiếm từ khóa, lọc theo danh mục/địa điểm, xem chi tiết sự kiện và bảng giá các loại vé, đăng ký/đăng nhập.
2. **Khách hàng (Customer - `role: customer`)**: Người mua vé; đặt vé giữ chỗ 15 phút, thanh toán giả lập (Chuyển khoản, MoMo, Thẻ), xem lịch sử đơn hàng, nhận vé điện tử kèm mã QR và in vé cá nhân.
3. **Ban tổ chức (Organizer - `role: organizer`)**: Đơn vị tổ chức sự kiện; Dashboard thống kê doanh thu, quản lý sự kiện (Tạo nháp -> Thêm vé -> Gửi duyệt), quản lý đơn hàng của sự kiện và soát vé (Check-in) tại cổng.
4. **Quản trị viên (Admin - `role: admin`)**: Quản trị toàn hệ thống; Dashboard tài chính và tăng trưởng, quản lý danh mục (Categories), địa điểm (Venues), quản lý người dùng (Khóa/Mở khóa tài khoản), phê duyệt hoặc hủy bỏ sự kiện.

#### 1.2. Dữ liệu cần Quản lý (8 Thực thể cốt lõi)
- `users`: Tài khoản, email duy nhất, mật khẩu mã hóa Bcrypt, vai trò, trạng thái kích hoạt `is_active`.
- `categories`: Danh mục sự kiện, tên và slug URL thân thiện duy nhất.
- `venues`: Địa điểm tổ chức, địa chỉ, thành phố, sức chứa tối đa.
- `events`: Thông tin sự kiện, thời gian bắt đầu/kết thúc, trạng thái duyệt (`draft`, `pending`, `published`, `cancelled`), ảnh banner, soft delete.
- `ticket_types`: Các hạng vé (VIP, Standard), giá bán, số lượng phát hành, số vé đã bán/giữ chỗ (`sold`), thời gian mở/đóng bán, giới hạn vé mỗi đơn.
- `orders`: Đơn hàng, mã `ORD-XXXXXXXX` duy nhất, thông tin khách nhận vé, tổng tiền, phương thức thanh toán, trạng thái (`pending`, `paid`, `cancelled`), thời hạn giữ chỗ 15 phút (`expires_at`).
- `order_items`: Chi tiết dòng vé đặt mua, đơn giá lúc mua (đóng băng giá), số lượng, thành tiền.
- `tickets`: Vé điện tử phát hành sau thanh toán, mã tra cứu/QR `TKT-XXXXXXXXXX` duy nhất, trạng thái (`valid`, `used`, `cancelled`), thời điểm quét check-in tại cổng.

#### 1.3. Các Quy trình Nghiệp vụ Trọng yếu
- **Quy trình 1: Vòng đời Sự kiện**: Ban tổ chức tạo bản nháp (`draft`) -> Thiết lập các loại vé -> Nhấn gửi duyệt (`pending`) -> Admin kiểm duyệt: nếu hợp lệ thì phê duyệt (`published`) để hiển thị lên sàn, nếu không đạt thì từ chối (`draft`) kèm lý do.
- **Quy trình 2: Đặt vé, Giữ chỗ và Thanh toán**: Khách hàng chọn vé -> Giao dịch `DB::transaction()` kết hợp khóa bi quan `lockForUpdate()` trên `ticket_types` -> Sinh mã đơn `ORD-XXXXXXXX`, tăng tạm thời số `sold` và cho giữ chỗ 15 phút -> Khách thanh toán -> Đơn chuyển sang `paid`, tự động kích hoạt vé điện tử `TKT-XXXXXXXXXX`.
- **Quy trình 3: Soát vé Check-in 5 bước**: Ban tổ chức quét mã hoặc nhập mã vé tại `/organizer/checkin` -> `TicketCheckinService` thực thi 5 bước kiểm tra: (1) Tồn tại mã vé? (2) Đơn hàng đã thanh toán? (3) Vé đã bị hủy? (4) Vé đã được check-in trước đó? (5) Vé có thuộc sự kiện do Organizer này quản lý không? -> Hợp lệ cập nhật `status = 'used'` và `checked_in_at = now()`.

#### 1.4. Phạm vi & Ràng buộc Nghiệp vụ
- **Phạm vi**: Hoàn chỉnh 100% các chức năng quản lý, phân quyền, giữ chỗ an toàn và soát vé; sử dụng Mock Payment Gateway mô phỏng cổng thanh toán trực tuyến.
- **Ràng buộc nghiệp vụ (Business Rules)**:
  - *BR-01*: Email, slug, mã đơn `ORD-`, mã vé `TKT-` duy nhất 100%.
  - *BR-02*: `end_time > start_time > now()`; thời gian mở bán trước thời gian đóng bán; giữ chỗ cố định 15 phút.
  - *BR-03*: Chặn xóa danh mục/địa điểm đang có sự kiện (Restrict); Sự kiện có đơn hàng chỉ cho phép xóa mềm (Soft Delete); Chặn Admin tự khóa tài khoản hoặc hạ quyền của chính mình.
  - *BR-04*: Đơn vị tiền tệ VNĐ có dấu phân cách hàng nghìn, múi giờ `Asia/Ho_Chi_Minh`, giao diện tiếng Việt UTF-8.

---

### TIÊU CHÍ 2: THIẾT KẾ CSDL HỢP LÝ, CÓ ERD VÀ QUAN HỆ GIỮA CÁC BẢNG

#### 2.1. Chuẩn hóa CSDL (3NF) & Sơ đồ ERD
Cơ sở dữ liệu đạt chuẩn 3NF loại bỏ hoàn toàn các phụ thuộc bắc cầu và lặp dữ liệu:
- Sơ đồ quan hệ thực thể (ERD) được mô tả chi tiết tại [docs/diagrams/05_erd.puml](file:///d:/NMNC/event-ticket/docs/diagrams/05_erd.puml) và [docs/02_thiet_ke_co_so_du_lieu.md](file:///d:/NMNC/event-ticket/docs/02_thiet_ke_co_so_du_lieu.md).
- **Mối quan hệ giữa các bảng**:
  - `users` (1) ── (N) `events`: `organizer_id` (CASCADE)
  - `categories` (1) ── (N) `events`: `category_id` (RESTRICT - Chặn xóa danh mục có sự kiện)
  - `venues` (1) ── (N) `events`: `venue_id` (RESTRICT - Chặn xóa địa điểm có sự kiện)
  - `events` (1) ── (N) `ticket_types`: `event_id` (CASCADE)
  - `users` (1) ── (N) `orders`: `user_id` (CASCADE)
  - `orders` (1) ── (N) `order_items`: `order_id` (CASCADE)
  - `ticket_types` (1) ── (N) `order_items`: `ticket_type_id` (CASCADE)
  - `order_items` (1) ── (N) `tickets`: `order_item_id` (CASCADE)

#### 2.2. Chiến lược Đánh chỉ mục (Indexing Strategy)
- **Unique Indexes**: `users(email)`, `categories(slug)`, `events(slug)`, `orders(order_code)`, `tickets(ticket_code)`.
- **Composite Indexes**:
  - `events(status, start_time)`: Tối ưu bộ lọc sự kiện đang mở bán theo ngày.
  - `orders(user_id, status)`: Tối ưu trang quản lý đơn hàng của khách.
- **Foreign Key Indexes**: Đánh chỉ mục toàn bộ các cột `_id` phục vụ JOIN và Eager Loading.

---

### TIÊU CHÍ 3: SỬ DỤNG MIGRATION, MODEL VÀ RELATIONSHIP ĐÚNG CHUẨN LARAVEL

#### 3.1. Migrations Chuẩn mực (7 Files)
Khai báo bằng anonymous class, kiểu dữ liệu chặt chẽ và ràng buộc khóa ngoại rõ ràng:
```php
// database/migrations/2026_10_25_000003_create_events_table.php
Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
    $table->foreignId('venue_id')->constrained('venues')->onDelete('restrict');
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('description');
    $table->string('banner_path')->nullable();
    $table->dateTime('start_time');
    $table->dateTime('end_time');
    $table->string('status')->default('draft');
    $table->timestamps();
    $table->softDeletes();

    $table->index(['status', 'start_time']);
});
```

#### 3.2. Eloquent Models & Quan hệ Hai Chiều
Tất cả 8 Models trong `app/Models/` đều định nghĩa đầy đủ `$fillable`, `$casts`, Enums, Scopes và Relationships:
- `Event`: `belongsTo(User)`, `belongsTo(Category)`, `belongsTo(Venue)`, `hasMany(TicketType)`.
- `TicketType`: `belongsTo(Event)`, `hasMany(OrderItem)`, Accessor `available_quantity`.
- `Order`: `belongsTo(User)`, `hasMany(OrderItem)`, `hasManyThrough(Ticket)`.
- `OrderItem`: `belongsTo(Order)`, `belongsTo(TicketType)`, `hasMany(Ticket)`.
- `Ticket`: `belongsTo(OrderItem)`, Scopes `valid()`, `used()`.
- `User`: `hasMany(Event)`, `hasMany(Order)`, Helpers `isAdmin()`, `isOrganizer()`.

#### 3.3. Tối ưu Truy vấn & Khóa Bi Quan (Pessimistic Locking)
- **Chống N+1 Query**: 100% các trang danh sách đều dùng Eager Loading `with(['category', 'venue', 'ticketTypes'])` và `withCount()`.
- **Khóa bi quan (`lockForUpdate()`)**: Trong `app/Services/OrderService.php`, dòng vé được khóa độc quyền trong `DB::transaction()` khi kiểm tra tồn kho, triệt tiêu nguy cơ bán vượt số lượng vé (race condition).

---

### TIÊU CHÍ 4: THIẾT KẾ PHÂN QUYỀN, VAI TRÒ NGƯỜI DÙNG NẾU HỆ THỐNG CẦN

Hệ thống triển khai mô hình Phân quyền Đa tầng (Multi-tier Authorization):

#### 4.1. Middleware Tầng Định Tuyến
- `CheckRole`: Hỗ trợ kiểm tra đơn quyền và đa quyền (ví dụ: `role:admin`, `role:organizer,admin`). Chặn truy cập trái quyền và trả về `403 Forbidden`.
- `CheckActive`: Kiểm tra cột `is_active`. Nếu tài khoản bị Admin khóa, tự động ngắt phiên (Logout) và redirect về trang đăng nhập kèm thông báo tiếng Việt.

#### 4.2. Policies Tầng Đối tượng (Chống Lỗ hổng IDOR)
- `EventPolicy`: Organizer chỉ được xem, sửa, gửi duyệt sự kiện của chính mình (`$user->id === $event->organizer_id`). Admin có toàn quyền duyệt.
- `TicketTypePolicy`: Chỉ cho phép chỉnh sửa loại vé thuộc sự kiện của Organizer sở hữu.
- `OrderPolicy`: Khách hàng chỉ được xem, thanh toán hoặc hủy đơn hàng của chính tài khoản mình sở hữu.

#### 4.3. Tự Vệ Quản Trị & Giao Diện Blade
- Trong `UserController`, hệ thống chặn Admin tự khóa tài khoản hoặc tự hạ quyền quản trị của chính mình.
- Tầng Blade sử dụng `@can`, `@auth`, `@guest` và menu phân cấp theo vai trò hiển thị chính xác cho từng đối tượng.

---

### TIÊU CHÍ 5: TỔ CHỨC ROUTE, CONTROLLER, VIEW RÕ RÀNG, DỄ BẢO TRÌ

#### 5.1. Tổ chức Tuyến đường (Routes) Module hóa
Tách biệt thành 4 file route chuyên trách trong thư mục `routes/` (tổng cộng 62 routes):
- `routes/auth.php`: Đăng ký, đăng nhập (throttle 5 lần/phút), đăng xuất, hồ sơ cá nhân.
- `routes/storefront.php`: Trang chủ, tìm kiếm, chi tiết sự kiện, đặt vé checkout, thanh toán, in vé cá nhân.
- `routes/organizer.php`: Prefix `/organizer`, Dashboard, CRUD sự kiện, quản lý hạng vé, đơn hàng sự kiện, soát vé Check-in.
- `routes/admin.php`: Prefix `/admin`, Dashboard tổng quan, quản lý danh mục, địa điểm, người dùng, phê duyệt sự kiện.

#### 5.2. Tổ chức Controller & Service Pattern
- Controller được phân theo Namespace: `App\Http\Controllers\Auth`, `Storefront`, `Organizer`, `Admin`.
- **Tách nghiệp vụ sang Service (Skinny Controller, Fat Service)**:
  - `OrderService.php`: Xử lý giao dịch đặt vé trong `DB::transaction()`, khóa `lockForUpdate()`, thanh toán mock, hủy đơn và hoàn trả vé.
  - `TicketCheckinService.php`: Xử lý 5 bước kiểm tra mã vé tại cổng kiểm soát.
- **12 Form Requests độc lập**: Toàn bộ logic validation được tách sang `app/Http/Requests/` với thông báo lỗi tiếng Việt chuẩn có dấu.

#### 5.3. Tổ chức Views & Blade Templates
- **2 Master Layouts**: `layouts/app.blade.php` (Storefront khách hàng) và `layouts/panel.blade.php` (Dashboard Admin & Organizer có Sidebar).
- **Partials dùng chung**: `partials/alerts.blade.php` (flash messages), `partials/form-errors.blade.php` (lỗi validation).
- **Trang lỗi chuẩn hóa**: `errors/403.blade.php`, `404.blade.php`, `419.blade.php`, `500.blade.php`.
- **Giao diện**: Bootstrap 5.3 CDN + Bootstrap Icons CDN, zero-node build, tải nhanh và responsive trên mọi màn hình.

---

### KẾT QUẢ KIỂM THỬ MINH CHỨNG TOÀN DIỆN
- **Tổng số Feature Tests**: `33/33 tests PASSED (100%)`
- **Số Assertions**: `82 assertions, 0 failures`
- **Chi tiết**: Xem tại [docs/testing/test-cases.md](file:///d:/NMNC/event-ticket/docs/testing/test-cases.md) và file Word [docs/03_bao_cao_tieu_chi_do_an.docx](file:///d:/NMNC/event-ticket/docs/03_bao_cao_tieu_chi_do_an.docx).
