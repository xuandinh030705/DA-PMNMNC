# BÁO CÁO NGHIỆM THU ĐỒ ÁN - MỤC 1 & MỤC 2 (PLO1, PLO2)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

- **Mã tài liệu**: CRIT-ETMS-v2.0
- **Nền tảng**: Laravel 13 (PHP 8.3+, kiểm thử trên PHP 8.5.9) / MySQL 8.0 (InnoDB) & SQLite dự phòng
- **File Word đính kèm**: [03_bao_cao_tieu_chi_do_an.docx](./03_bao_cao_tieu_chi_do_an.docx)
- **Đối chiếu**: Bám sát đầy đủ 6 tiêu chí của Mục 1 (PLO1) và Mục 2 (PLO2) trong rubric `tailieu/1.jpg`.

---

### BẢNG TỔNG QUAN ĐÁNH GIÁ 6 TIÊU CHÍ (MỤC 1 & MỤC 2)

| STT | Mục & PLO | Nội Dung Tiêu Chí Đánh Giá | Mức Độ Đạt Được | Minh Chứng Mã Nguồn & Tài Liệu |
| :---: | :---: | :--- | :---: | :--- |
| **1** | **Mục 1 (PLO1)** | Xác định đối tượng người dùng, vai trò, dữ liệu cần quản lý, quy trình nghiệp vụ, phạm vi và ràng buộc | **100% Hoàn thành** | [`docs/01_phan_tich_yeu_cau.md`](./01_phan_tich_yeu_cau.md), [`docs/SPEC.md`](./SPEC.md), `UserRole` Enum, BR-01..05 |
| **2** | **Mục 2 (PLO2)** | Thiết kế CSDL hợp lý, có ERD và quan hệ giữa các bảng | **100% Hoàn thành** | [`docs/02_thiet_ke_co_so_du_lieu.md`](./02_thiet_ke_co_so_du_lieu.md), [`docs/diagrams/05_erd.puml`](./diagrams/05_erd.puml), [`DRAWIO_UML_PROMPTS.md`](./diagrams/DRAWIO_UML_PROMPTS.md) |
| **3** | **Mục 2 (PLO2)** | Sử dụng migration, model và relationship đúng chuẩn Laravel | **100% Hoàn thành** | **12 Migrations** (7 bảng nghiệp vụ + 2 nâng cấp QR/`zone`), **8 Eloquent Models**, **4 Enums**, Eager Loading, `lockForUpdate()` |
| **4** | **Mục 2 (PLO2)** | Thiết kế phân quyền, vai trò người dùng nếu hệ thống cần | **100% Hoàn thành** | Middleware `CheckRole`, `CheckActive`, 3 Policies (`EventPolicy`, `TicketTypePolicy`, `OrderPolicy`) bảo vệ cả tải PDF |
| **5** | **Mục 2 (PLO2)** | Tổ chức route, controller, view rõ ràng, dễ bảo trì | **100% Hoàn thành** | 4 route files tách biệt (**58 app routes**), 4 Services, `OrderPaidMail`, 11 FormRequests, 2 Master Blade layouts |
| **6** | **Mục 2 (PLO2)** | Tài liệu/sơ đồ thiết kế: use case, ERD, sơ đồ chức năng | **Đầy đủ mã nguồn UML** | 8 file PlantUML chính + 6 sequence + 8 PNG + [`DRAWIO_UML_PROMPTS.md`](./diagrams/DRAWIO_UML_PROMPTS.md) |

---

### TIÊU CHÍ 1 (MỤC 1 - PLO1): XÁC ĐỊNH ĐỐI TƯỢNG NGƯỜI DÙNG, VAI TRÒ, DỮ LIỆU CẦN QUẢN LÝ, QUY TRÌNH NGHIỆP VỤ, PHẠM VI VÀ RÀNG BUỘC

#### 1.1. Đối tượng Người dùng & Vai trò (User Roles)
Hệ thống xác định rõ 4 đối tượng tác tử tham gia quy trình, quản lý chặt chẽ qua `users.role` và Backed Enum [`app/Enums/UserRole.php`](../app/Enums/UserRole.php):
1. **Khách vãng lai (Guest)**: Xem danh sách sự kiện đang mở bán, tìm kiếm từ khóa (`q`), lọc theo danh mục/thành phố/ngày, sắp xếp theo thời gian/giá vé, xem chi tiết sự kiện kèm khu vực (`zone`) và quyền lợi (`description`), đăng ký/đăng nhập.
2. **Khách hàng (Customer - `role: customer`)**: Đặt vé giữ chỗ 15 phút, thanh toán giả lập, nhận email xác nhận (`OrderPaidMail`) đính kèm PDF vé, xem lịch sử đơn hàng, nhận vé điện tử kèm **mã QR ký số HMAC-SHA256**, tải vé **PDF khổ A5**, tự hủy đơn `pending`, sửa hồ sơ.
3. **Ban tổ chức (Organizer - `role: organizer`)**: Dashboard thống kê, quản lý vòng đời sự kiện (Tạo nháp → Thêm vé kèm `zone` & `description` → Gửi duyệt), theo dõi đơn hàng thuộc sự kiện của mình và **soát vé (Check-in) bằng Camera QR (`html5-qrcode` offline) hoặc nhập tay**.
4. **Quản trị viên (Admin - `role: admin`)**: Dashboard toàn sàn, quản lý danh mục & địa điểm (ràng buộc `RESTRICT`), quản lý người dùng (khóa/mở khóa, đổi vai trò), phê duyệt/từ chối/hủy sự kiện (**cascade hủy đơn `pending` hoàn vé & hủy vé `valid`**), tra cứu mọi đơn hàng kèm nhân viên soát vé (`checkedInBy`).

#### 1.2. Dữ liệu cần Quản lý (8 Thực thể cốt lõi)
- `users`: Tài khoản, email duy nhất, mật khẩu Bcrypt, vai trò (`UserRole`), trạng thái kích hoạt `is_active`.
- `categories`: Danh mục sự kiện, tên và slug duy nhất.
- `venues`: Địa điểm tổ chức, địa chỉ, thành phố, sức chứa tối đa.
- `events`: Sự kiện, thời gian bắt đầu/kết thúc, trạng thái duyệt (`EventStatus`), ảnh banner, xóa mềm (`SoftDeletes`).
- `ticket_types`: Các hạng vé, khu vực (`zone`), mô tả quyền lợi (`description`), giá bán VNĐ, số lượng phát hành, số đã bán/giữ chỗ (`sold`), thời gian mở/đóng bán, giới hạn `max_per_order`.
- `orders`: Đơn hàng, mã `ORD-XXXXXXXX` duy nhất, thông tin khách nhận vé, tổng tiền, phương thức thanh toán, trạng thái (`OrderStatus`), hạn giữ chỗ 15 phút (`expires_at`).
- `order_items`: Chi tiết dòng vé đặt mua, đơn giá tại thời điểm đặt, số lượng, thành tiền.
- `tickets`: Vé điện tử phát hành sau thanh toán, mã `TKT-XXXXXXXXXX` duy nhất, payload JSON `qr_payload`, chữ ký số HMAC-SHA256 `qr_signature`, trạng thái (`TicketStatus`), thời điểm check-in `checked_in_at`, khóa ngoại nhân viên soát vé `checked_in_by`.

#### 1.3. Các Quy trình Nghiệp vụ Trọng yếu
- **Quy trình 1: Vòng đời Sự kiện & Cascade Hủy**: Organizer tạo `draft` → thêm loại vé (`zone`, `description`) → gửi duyệt (`pending`) → Admin duyệt (`published`), từ chối (`draft`) hoặc hủy (`cancelled`). Khi Admin hủy sự kiện, `Admin\EventController::cancel()` trong transaction sẽ tự động hủy đơn `pending` hoàn trả `sold` và chuyển vé `valid` sang `cancelled`.
- **Quy trình 2: Đặt vé, Giữ chỗ 15 phút, Thanh toán, QR, PDF & Email**: `OrderService::createOrder()` dùng `DB::transaction() + lockForUpdate()` trên `ticket_types` → tạo đơn `pending` giữ chỗ 15 phút → thanh toán `payOrder()` sinh vé kèm `qr_payload` & `qr_signature` HMAC-SHA256 → `OrderController@pay` gửi `OrderPaidMail` đính kèm PDF khổ A5 tạo động trong RAM → khách xem QR và tải PDF qua `/my-tickets/{ticket}/pdf`.
- **Quy trình 3: Soát vé Check-in 6 bước (Camera QR & Nhập tay)**: Organizer quét QR bằng camera (`public/js/html5-qrcode.min.js`) hoặc nhập tay tại `/organizer/checkin` → `TicketCheckinService` khóa dòng vé (`lockForUpdate()`) và kiểm tra 6 bước: (1) tồn tại mã vé → (2) chữ ký HMAC-SHA256 hợp lệ (`hash_equals`) khi quét QR → (3) đơn hàng đã `paid` → (4) thuộc sự kiện của Organizer → (5) vé chưa `cancelled` → (6) vé chưa `used` → cập nhật `used`, `checked_in_at` và `checked_in_by`.

#### 1.4. Phạm vi & Ràng buộc Nghiệp vụ
- **Phạm vi**: Đầy đủ quy trình nghiệp vụ, bảo mật chống giả vé QR HMAC-SHA256, xuất vé PDF động trong RAM, gửi email qua Mailpit/Log và quét camera offline; dùng Mock Payment Gateway để đảm bảo chạy ổn định khi bảo vệ.
- **Ràng buộc (BR-01..BR-05)**: Định danh duy nhất (`ORD-`, `TKT-`, email, slug) + chữ ký HMAC-SHA256 64 ký tự hex; `end_time > start_time > now()`, giữ chỗ 15 phút; `RESTRICT` trên danh mục/địa điểm, `SoftDeletes` trên sự kiện, Admin không tự khóa/tự hạ quyền; tiền tệ VNĐ, múi giờ `Asia/Ho_Chi_Minh`, tiếng Việt UTF-8; khóa bi quan cho đặt vé, thanh toán, check-in và hủy sự kiện.

---

### TIÊU CHÍ 2 (MỤC 2 - PLO2): THIẾT KẾ CSDL HỢP LÝ, CÓ ERD VÀ QUAN HỆ GIỮA CÁC BẢNG

#### 2.1. Chuẩn hóa CSDL (3NF) & Sơ đồ ERD
Cơ sở dữ liệu đạt chuẩn 3NF với 8 bảng nghiệp vụ (chi tiết tại [`docs/02_thiet_ke_co_so_du_lieu.md`](./02_thiet_ke_co_so_du_lieu.md) và [`DRAWIO_UML_PROMPTS.md`](./diagrams/DRAWIO_UML_PROMPTS.md)):
- `users` (1) ── (N) `events`: `organizer_id` (`CASCADE`)
- `categories` (1) ── (N) `events`: `category_id` (`RESTRICT`)
- `venues` (1) ── (N) `events`: `venue_id` (`RESTRICT`)
- `events` (1) ── (N) `ticket_types`: `event_id` (`CASCADE`)
- `users` (1) ── (N) `orders`: `user_id` (`CASCADE`)
- `orders` (1) ── (N) `order_items`: `order_id` (`CASCADE`)
- `ticket_types` (1) ── (N) `order_items`: `ticket_type_id` (`CASCADE`)
- `order_items` (1) ── (0..N) `tickets`: `order_item_id` (`CASCADE`)
- `users` (1) ── (0..N) `tickets`: `checked_in_by` (`SET NULL`)

#### 2.2. Chiến lược Đánh chỉ mục (Indexing Strategy)
- **Unique Indexes**: `users(email)`, `categories(name)`, `categories(slug)`, `events(slug)`, `orders(order_code)`, `tickets(ticket_code)`.
- **Composite & Lookup Indexes**: `events(status, start_time)`, `events(category_id)`, `orders(user_id, status)`.

---

### TIÊU CHÍ 3 (MỤC 2 - PLO2): SỬ DỤNG MIGRATION, MODEL VÀ RELATIONSHIP ĐÚNG CHUẨN LARAVEL

1. **Migrations (12 files):** 3 core Laravel + 7 nghiệp vụ (`2026_10_25_000001..000007`) + 2 migration nâng cấp (`2026_11_12_210058_add_qr_and_checkin_columns_to_tickets_table.php` và `2026_11_12_210059_add_zone_and_description_to_ticket_types_table.php`).
2. **8 Eloquent Models & 4 Backed Enums:** Đồng bộ `$fillable`, `casts()` (`UserRole`, `EventStatus`, `OrderStatus`, `TicketStatus`), Accessors (`remaining`, `description_paragraphs`), Scopes (`published()`, `upcoming()`, `notEnded()`, `search()`) và quan hệ hai chiều (gồm `Ticket::checkedInBy()`).
3. **Tối ưu Truy vấn & Khóa Bi Quan:** Eager Loading `with()`, `withCount()`, `withMin()`, `withSum()`, eager load `tickets.orderItem.ticketType.event.venue` & `tickets.checkedInBy` chống N+1 trên `orders.show`; `lockForUpdate()` trong `DB::transaction()` tại `OrderService`, `TicketCheckinService` và `Admin\EventController::cancel()`.

---

### TIÊU CHÍ 4 (MỤC 2 - PLO2): THIẾT KẾ PHÂN QUYỀN, VAI TRÒ NGƯỜI DÙNG

1. **Middleware:** `CheckRole` (`role:admin`, `role:organizer`), `CheckActive` (ngắt phiên tài khoản bị khóa), `throttle:5,1` trên route đăng nhập.
2. **Policies chống IDOR:** `EventPolicy`, `TicketTypePolicy`, `OrderPolicy` (kiểm tra quyền xem/thanh toán/hủy đơn và tải vé PDF `/my-tickets/{ticket}/pdf`, chặn truy cập chéo bằng HTTP 403).
3. **Tự vệ Quản trị:** Chặn Admin tự khóa hoặc tự hạ quyền chính mình tại `Admin\UserController`.

---

### TIÊU CHÍ 5 (MỤC 2 - PLO2): TỔ CHỨC ROUTE, CONTROLLER, VIEW RÕ RÀNG, DỄ BẢO TRÌ

- **4 file route tách biệt (58 application routes):** `routes/auth.php`, `routes/storefront.php`, `routes/organizer.php`, `routes/admin.php` + lịch chạy `orders:expire-pending` mỗi phút trong `routes/console.php`.
- **Controllers, Services, Mail & Requests:** 4 namespaces (`Auth`, `Storefront`, `Organizer`, `Admin`), 4 Services (`OrderService`, `TicketCheckinService`, `QrTicketService`, `TicketPdfService`), Mailable `OrderPaidMail`, 11 FormRequests tiếng Việt.
- **Blade Views:** 2 Master Layouts (`layouts/app`, `layouts/panel`), template `emails/order-paid` & `pdf/ticket` (khổ A5 font DejaVu Sans), partials dùng chung, 4 trang lỗi tùy biến (`403`, `404`, `419`, `500`), Bootstrap 5.3 CDN Zero-node.

---

### TIÊU CHÍ 6 (MỤC 2 - PLO2): TÀI LIỆU / SƠ ĐỒ THIẾT KẾ

- Có sẵn 8 sơ đồ PlantUML (`.puml`) + 8 ảnh PNG trong `docs/diagrams/images/` + 6 sơ đồ tuần tự chi tiết (`sequence_01..06.puml`).
- Đã chuẩn hóa toàn bộ bộ Prompt AI & mã PlantUML cập nhật 5 cột DB mới + luồng QR/PDF/Email/Camera/Cascade tại [`docs/diagrams/DRAWIO_UML_PROMPTS.md`](./diagrams/DRAWIO_UML_PROMPTS.md) để kết xuất sang `.drawio` và `.png`.

---

### KẾT QUẢ KIỂM THỬ MINH CHỨNG TOÀN DIỆN
- **Tổng số Tests**: `103/103 tests PASSED (100%)` (15 Unit tests + 88 Feature tests)
- **Số Assertions**: `411 assertions, 0 failures`
- **Code Style (`pint --test`)**: `PASSED (100%)`
- **Chi tiết**: Xem tại [`docs/testing/test-cases.md`](./testing/test-cases.md) và [`docs/testing/test-output.txt`](./testing/test-output.txt).
