# BÁO CÁO NGHIỆM THU ĐỒ ÁN - MỤC 1 & MỤC 2 (PLO1, PLO2)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

- **Mã tài liệu**: CRIT-ETMS-v1.1
- **Nền tảng**: Laravel 13 (PHP 8.3+) / SQLite & MySQL
- **File Word đính kèm**: [03_bao_cao_tieu_chi_do_an.docx](./03_bao_cao_tieu_chi_do_an.docx)
- **Đối chiếu**: Bám sát đầy đủ 6 tiêu chí của Mục 1 (PLO1) và Mục 2 (PLO2) trong rubric `tailieu/1.jpg`.

---

### BẢNG TỔNG QUAN ĐÁNH GIÁ 6 TIÊU CHÍ (MỤC 1 & MỤC 2)

| STT | Mục & PLO | Nội Dung Tiêu Chí Đánh Giá | Mức Độ Đạt Được | Minh Chứng Mã Nguồn & Tài Liệu |
| :---: | :---: | :--- | :---: | :--- |
| **1** | **Mục 1 (PLO1)** | Xác định đối tượng người dùng, vai trò, dữ liệu cần quản lý, quy trình nghiệp vụ, phạm vi và ràng buộc | **100% Hoàn thành** | [`docs/01_phan_tich_yeu_cau.md`](./01_phan_tich_yeu_cau.md), [`docs/SPEC.md`](./SPEC.md), `User::ROLE_*`, BR-01..04 |
| **2** | **Mục 2 (PLO2)** | Thiết kế CSDL hợp lý, có ERD và quan hệ giữa các bảng | **100% Hoàn thành** | [`docs/diagrams/05_erd.puml`](./diagrams/05_erd.puml), [`05_erd.png`](./diagrams/images/05_erd.png), [`02_thiet_ke_co_so_du_lieu.md`](./02_thiet_ke_co_so_du_lieu.md) |
| **3** | **Mục 2 (PLO2)** | Sử dụng migration, model và relationship đúng chuẩn Laravel | **100% Hoàn thành** | 10 Migrations (7 nghiệp vụ), 8 Eloquent Models, Eager Loading, Pessimistic Locking |
| **4** | **Mục 2 (PLO2)** | Thiết kế phân quyền, vai trò người dùng nếu hệ thống cần | **100% Hoàn thành** | Middleware `CheckRole`, `CheckActive`, 3 Policies (`EventPolicy`, `TicketTypePolicy`, `OrderPolicy`) |
| **5** | **Mục 2 (PLO2)** | Tổ chức route, controller, view rõ ràng, dễ bảo trì | **100% Hoàn thành** | 4 route files tách biệt (57 app routes), Service Layer, 11 FormRequests, 2 Master Blade layouts |
| **6** | **Mục 2 (PLO2)** | Tài liệu/sơ đồ thiết kế: use case, ERD, sơ đồ chức năng | **100% Hoàn thành** | 8 file PlantUML (`.puml`) và 8 ảnh PNG tương ứng trong [`docs/diagrams/images/`](./diagrams/images/) |

---

### TIÊU CHÍ 1 (MỤC 1 - PLO1): XÁC ĐỊNH ĐỐI TƯỢNG NGƯỜI DÙNG, VAI TRÒ, DỮ LIỆU CẦN QUẢN LÝ, QUY TRÌNH NGHIỆP VỤ, PHẠM VI VÀ RÀNG BUỘC

#### 1.1. Đối tượng Người dùng & Vai trò (User Roles)
Hệ thống xác định rõ 4 đối tượng tác tử tham gia vào quy trình (quản lý qua `users.role` với các hằng số `User::ROLE_ADMIN`, `User::ROLE_ORGANIZER`, `User::ROLE_CUSTOMER` và enum [`app/Enums/UserRole.php`](../app/Enums/UserRole.php)):
1. **Khách vãng lai (Guest)**: Người dùng chưa đăng nhập; xem danh sách sự kiện đang mở bán, tìm kiếm từ khóa, lọc theo danh mục/thành phố/ngày, xem chi tiết sự kiện và bảng giá các loại vé, đăng ký/đăng nhập.
2. **Khách hàng (Customer - `role: customer`)**: Người mua vé; đặt vé giữ chỗ 15 phút, thanh toán giả lập (Chuyển khoản ngân hàng, MoMo, Thẻ), xem lịch sử đơn hàng, nhận thẻ vé điện tử với mã vé monospace `TKT-XXXXXXXXXX` và nút in vé cá nhân.
3. **Ban tổ chức (Organizer - `role: organizer`)**: Đơn vị tổ chức sự kiện; Dashboard thống kê doanh thu, quản lý vòng đời sự kiện (Tạo nháp -> Thêm vé -> Gửi duyệt), theo dõi đơn hàng thuộc sự kiện của mình và soát vé (Check-in) tại cổng.
4. **Quản trị viên (Admin - `role: admin`)**: Quản trị toàn hệ thống; Dashboard tổng quan doanh thu và đơn hàng, quản lý danh mục (Categories), địa điểm (Venues), quản lý người dùng (Khóa/Mở khóa tài khoản, đổi vai trò), phê duyệt/từ chối/hủy sự kiện.

#### 1.2. Dữ liệu cần Quản lý (8 Thực thể cốt lõi)
- `users`: Tài khoản, email duy nhất, mật khẩu mã hóa Bcrypt, vai trò, trạng thái kích hoạt `is_active`.
- `categories`: Danh mục sự kiện, tên và slug URL duy nhất.
- `venues`: Địa điểm tổ chức, địa chỉ, thành phố, sức chứa tối đa.
- `events`: Thông tin sự kiện, thời gian bắt đầu/kết thúc, trạng thái duyệt (`draft`, `pending`, `published`, `cancelled`), ảnh banner, soft delete.
- `ticket_types`: Các hạng vé, giá bán, số lượng phát hành, số vé đã bán/giữ chỗ (`sold`), thời gian mở/đóng bán, giới hạn vé mỗi đơn.
- `orders`: Đơn hàng, mã `ORD-XXXXXXXX` duy nhất, thông tin khách nhận vé, tổng tiền, phương thức thanh toán, trạng thái (`pending`, `paid`, `cancelled`), thời hạn giữ chỗ 15 phút (`expires_at`).
- `order_items`: Chi tiết dòng vé đặt mua, đơn giá lúc mua, số lượng, thành tiền.
- `tickets`: Vé điện tử phát hành sau thanh toán, mã tra cứu `TKT-XXXXXXXXXX` duy nhất, trạng thái (`valid`, `used`, `cancelled`), thời điểm check-in.

#### 1.3. Các Quy trình Nghiệp vụ Trọng yếu
- **Quy trình 1: Vòng đời Sự kiện**: Ban tổ chức tạo bản nháp (`draft`) -> Thiết lập các loại vé -> Nhấn gửi duyệt (`pending`) -> Admin kiểm duyệt: phê duyệt (`published`) để công khai lên Storefront, từ chối (`draft`) hoặc hủy (`cancelled`).
- **Quy trình 2: Đặt vé, Giữ chỗ và Thanh toán**: Khách hàng chọn vé -> Giao dịch `DB::transaction()` kết hợp khóa bi quan `lockForUpdate()` trên `ticket_types` -> Sinh mã đơn `ORD-XXXXXXXX`, tăng tạm thời số `sold` và cho giữ chỗ 15 phút -> Khách thanh toán -> Đơn chuyển sang `paid`, sinh các vé điện tử `TKT-XXXXXXXXXX`. Nếu quá hạn 15 phút hoặc khách hủy, đơn chuyển sang `cancelled` và hoàn trả `sold`.
- **Quy trình 3: Soát vé Check-in 5 bước**: Ban tổ chức nhập mã vé tại `/organizer/checkin` -> `TicketCheckinService` thực thi 5 bước kiểm tra: (1) Tồn tại mã vé? (2) Đơn hàng đã thanh toán (`paid`)? (3) Vé có thuộc sự kiện của Organizer này không? (4) Vé đã bị hủy chưa? (5) Vé đã được check-in trước đó chưa? -> Hợp lệ cập nhật `status = 'used'` và `checked_in_at = now()`.

#### 1.4. Phạm vi & Ràng buộc Nghiệp vụ
- **Phạm vi**: Hoàn chỉnh toàn bộ các chức năng quản lý, phân quyền, giữ chỗ an toàn và soát vé; sử dụng Mock Payment Gateway mô phỏng cổng thanh toán và thẻ vé mã monospace kèm nút in (không dùng camera quét QR phần cứng).
- **Ràng buộc nghiệp vụ (Business Rules)**:
  - *BR-01*: Email, slug, mã đơn `ORD-`, mã vé `TKT-` duy nhất 100%.
  - *BR-02*: `end_time > start_time > now()`; thời gian mở bán trước thời gian đóng bán; giữ chỗ cố định 15 phút.
  - *BR-03*: Chặn xóa danh mục/địa điểm đang có sự kiện (`RESTRICT`); sự kiện dùng `SoftDeletes`; chặn Admin tự khóa tài khoản hoặc tự hạ quyền chính mình.
  - *BR-04*: Đơn vị tiền tệ VNĐ phân cách hàng nghìn bằng dấu chấm (`number_format(..., 0, ',', '.') . ' đ'`), múi giờ `Asia/Ho_Chi_Minh`, giao diện tiếng Việt UTF-8.

---

### TIÊU CHÍ 2 (MỤC 2 - PLO2): THIẾT KẾ CSDL HỢP LÝ, CÓ ERD VÀ QUAN HỆ GIỮA CÁC BẢNG

#### 2.1. Chuẩn hóa CSDL (3NF) & Sơ đồ ERD
Cơ sở dữ liệu đạt chuẩn 3NF với 8 bảng nghiệp vụ:
- Sơ đồ ERD: [`docs/diagrams/05_erd.puml`](./diagrams/05_erd.puml) và ảnh xuất [`docs/diagrams/images/05_erd.png`](./diagrams/images/05_erd.png).
- **Mối quan hệ giữa các bảng**:
  - `users` (1) ── (N) `events`: `organizer_id` (`CASCADE`)
  - `categories` (1) ── (N) `events`: `category_id` (`RESTRICT`)
  - `venues` (1) ── (N) `events`: `venue_id` (`RESTRICT`)
  - `events` (1) ── (N) `ticket_types`: `event_id` (`CASCADE`)
  - `users` (1) ── (N) `orders`: `user_id` (`CASCADE`)
  - `orders` (1) ── (N) `order_items`: `order_id` (`CASCADE`)
  - `ticket_types` (1) ── (N) `order_items`: `ticket_type_id` (`CASCADE`)
  - `order_items` (1) ── (0..N) `tickets`: `order_item_id` (`CASCADE`)

#### 2.2. Chiến lược Đánh chỉ mục (Indexing Strategy)
- **Unique Indexes**: `users(email)`, `categories(name)`, `categories(slug)`, `events(slug)`, `orders(order_code)`, `tickets(ticket_code)`.
- **Composite & Lookup Indexes**: `events(status, start_time)`, `events(category_id)`, `orders(user_id, status)`.

---

### TIÊU CHÍ 3 (MỤC 2 - PLO2): SỬ DỤNG MIGRATION, MODEL VÀ RELATIONSHIP ĐÚNG CHUẨN LARAVEL

#### 3.1. Migrations Chuẩn mực (10 Files: 3 core + 7 nghiệp vụ)
Khai báo bằng anonymous class với kiểu dữ liệu chặt chẽ và ràng buộc khóa ngoại rõ ràng (`2026_10_25_000001` đến `000007`).

#### 3.2. Eloquent Models & Quan hệ Hai Chiều
8 Models trong `app/Models/` định nghĩa đầy đủ `$fillable`, `casts()`, Accessors, Scopes và Relationships:
- `Event`: `belongsTo(User, 'organizer_id')`, `belongsTo(Category)`, `belongsTo(Venue)`, `hasMany(TicketType)`, scopes `published()`, `upcoming()`, `notEnded()`, `search()`.
- `TicketType`: `belongsTo(Event)`, `hasMany(OrderItem)`, Accessor `remaining` (`max(0, quantity - sold)`), `isOnSale()`.
- `Order`: `belongsTo(User)`, `hasMany(OrderItem)`, `hasManyThrough(Ticket, OrderItem)`.
- `OrderItem`: `belongsTo(Order)`, `belongsTo(TicketType)`, `hasMany(Ticket)`.
- `Ticket`: `belongsTo(OrderItem)`, helpers `isValid()`, `isUsed()`, `isCancelled()`.
- `User`: `hasMany(Event, 'organizer_id')`, `hasMany(Order, 'user_id')`, helpers `isAdmin()`, `isOrganizer()`, `isCustomer()`, `isActive()`.
- `Category`, `Venue`: `hasMany(Event)`.

#### 3.3. Tối ưu Truy vấn & Khóa Bi Quan (Pessimistic Locking)
- **Chống N+1 Query**: Sử dụng Eager Loading `with(['category', 'venue'])`, `withCount('ticketTypes')`, `withMin('ticketTypes as min_price', 'price')`, `withSum('ticketTypes as total_sold', 'sold')`.
- **Khóa bi quan (`lockForUpdate()`)**: Trong `app/Services/OrderService.php`, bản ghi `ticket_types` và `orders` được khóa trong `DB::transaction()` để chống bán vượt tồn kho.

---

### TIÊU CHÍ 4 (MỤC 2 - PLO2): THIẾT KẾ PHÂN QUYỀN, VAI TRÒ NGƯỜI DÙNG

1. **Middleware tầng Route**: `CheckRole` (hỗ trợ đa quyền như `role:admin`, `role:organizer`), `CheckActive` (tự động `Auth::logout()` và hủy session khi tài khoản bị khóa).
2. **Policies tầng Đối tượng (chống IDOR)**: `EventPolicy`, `TicketTypePolicy`, `OrderPolicy` (kiểm tra quyền sở hữu `organizer_id` / `user_id` và trạng thái hợp lệ).
3. **Tự vệ Quản trị & Blade**: Chặn Admin tự khóa hoặc tự hạ quyền chính mình trong `Admin\UserController`; giao diện hiển thị điều hướng theo vai trò.

---

### TIÊU CHÍ 5 (MỤC 2 - PLO2): TỔ CHỨC ROUTE, CONTROLLER, VIEW RÕ RÀNG, DỄ BẢO TRÌ

- **4 file route tách biệt (57 application routes)**: `routes/auth.php`, `routes/storefront.php`, `routes/organizer.php`, `routes/admin.php` được nạp trong `routes/web.php`, cùng tác vụ định kỳ trong `routes/console.php`.
- **Controllers & Service Layer**: Phân theo 4 namespace (`Auth`, `Storefront`, `Organizer`, `Admin`), tách nghiệp vụ giao dịch sang `OrderService` và `TicketCheckinService`, tách validation sang 11 FormRequests tiếng Việt.
- **Blade Views**: 2 Master Layouts (`layouts/app.blade.php`, `layouts/panel.blade.php`), 2 Partials dùng chung (`alerts`, `form-errors`), 4 trang lỗi tùy biến (`403`, `404`, `419`, `500`), Bootstrap 5.3 CDN không cần Node.js.

---

### TIÊU CHÍ 6 (MỤC 2 - PLO2): TÀI LIỆU / SƠ ĐỒ THIẾT KẾ (USE CASE, ERD, SƠ ĐỒ CHỨC NĂNG)

Toàn bộ 8 sơ đồ đã được xây dựng bằng PlantUML trong `docs/diagrams/` và kết xuất thành ảnh PNG độ phân giải cao trong `docs/diagrams/images/`:
- Use Case tổng quan & chi tiết 3 vai trò (`01_usecase_tong_quan.png` .. `04_usecase_quan_tri.png`)
- Sơ đồ ERD (`05_erd.png`)
- Sơ đồ phân rã chức năng WBS (`06_so_do_chuc_nang.png`)
- Sơ đồ hoạt động mua vé (`07_activity_mua_ve.png`)
- Sơ đồ tuần tự thanh toán (`08_sequence_thanh_toan.png`)

---

### KẾT QUẢ KIỂM THỬ MINH CHỨNG TOÀN DIỆN
- **Tổng số Tests**: `37/37 tests PASSED (100%)` (2 Unit tests + 35 Feature tests)
- **Số Assertions**: `104 assertions, 0 failures`
- **Chi tiết**: Xem tại [`docs/testing/test-cases.md`](./testing/test-cases.md) và [`docs/testing/test-output.txt`](./testing/test-output.txt).
