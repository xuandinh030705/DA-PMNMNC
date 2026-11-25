# SPEC NGHIỆP VỤ - HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN

## Vai trò
- guest: xem/tìm/lọc sự kiện, xem chi tiết (gồm khu vực `zone` & quyền lợi `description`), đăng ký, đăng nhập.
- customer: đặt vé, thanh toán giả lập, nhận email xác nhận (`OrderPaidMail`) đính kèm vé PDF, xem đơn và vé của mình (kèm ảnh mã QR ký số HMAC), tải vé PDF khổ A5, hủy đơn pending, sửa hồ sơ.
- organizer: CRUD sự kiện của mình + loại vé (kèm `zone`, `description`), gửi duyệt, xem đơn của sự kiện mình, check-in vé bằng camera QR (`html5-qrcode` offline) hoặc nhập tay, dashboard thống kê.
- admin: duyệt/từ chối/hủy sự kiện (cascade hủy đơn pending hoàn vé & hủy vé valid), CRUD danh mục + địa điểm, quản lý người dùng (khóa/mở, đổi vai trò), xem mọi đơn (kèm `zone`, `description`, `checked_in_by`), dashboard thống kê.

## Database
users: id, name, email(unique), password, phone(null), role(admin|organizer|customer, default customer), is_active(bool, default true), remember_token, timestamps
categories: id, name(unique), slug(unique), description(null), timestamps
venues: id, name, address, city, capacity(unsigned int), timestamps
events: id, organizer_id(FK users), category_id(FK), venue_id(FK), title, slug(unique), description(text), banner_path(null), start_time, end_time, status(draft|pending|published|cancelled, default draft), timestamps, softDeletes
ticket_types: id, event_id(FK cascade), name, zone(string 100, null), description(text, null), price(decimal 12,0), quantity(unsigned int), sold(unsigned int, default 0), sale_start, sale_end, max_per_order(unsigned int, default 10), timestamps
orders: id, user_id(FK), order_code(unique), customer_name, customer_email, customer_phone, total_amount(decimal 12,0), status(pending|paid|cancelled), payment_method(null), paid_at(null), expires_at(null), timestamps
order_items: id, order_id(FK cascade), ticket_type_id(FK), quantity, unit_price(decimal 12,0), subtotal(decimal 12,0), timestamps
tickets: id, order_item_id(FK cascade), ticket_code(unique), qr_payload(text, null), qr_signature(string 255 lưu hex 64 ký tự HMAC-SHA256, null), status(valid|used|cancelled, default valid), checked_in_at(null), checked_in_by(FK users, nullOnDelete), timestamps
Index: events(status,start_time), events(category_id), orders(user_id,status).
Quan hệ: User 1-n Event (organizer), User 1-n Order, User 1-n Ticket (checked_in_by); Category 1-n Event; Venue 1-n Event; Event 1-n TicketType; Order 1-n OrderItem; TicketType 1-n OrderItem; OrderItem 1-n Ticket.
Enum casting trên Models: `UserRole`, `EventStatus`, `OrderStatus`, `TicketStatus`.

## Quy tắc nghiệp vụ
- Vòng đời sự kiện: draft -> (organizer gửi duyệt) pending -> (admin duyệt) published. Admin có thể reject (pending -> draft) hoặc cancel (-> cancelled). Organizer chỉ sửa khi draft; chỉ gửi duyệt khi có >= 1 loại vé và start_time > hiện tại.
- Loại vé (`TicketTypeRequest`): hỗ trợ thêm 2 cột tùy chọn `zone` (nullable, string, max 100) và `description` (nullable, string, max 1000), hiển thị trên Organizer (`events/show`, `ticket-types/create|edit`), Storefront (`events/show`), Admin (`events/show`, `orders/show`) và vé PDF.
- Chỉ mua được khi: event published, chưa bắt đầu, loại vé trong khoảng sale_start..sale_end, còn vé (quantity - sold > 0).
- Đặt vé: mỗi đơn >= 1 vé; mỗi loại <= max_per_order và <= số còn lại. Xử lý trong DB::transaction + lockForUpdate trên ticket_types để không bán vượt. Tăng sold ngay khi tạo đơn pending (giữ chỗ), expires_at = now + 15 phút. order_code dạng "ORD-" + 8 ký tự in hoa, unique (retry nếu trùng).
- Đơn pending hết hạn hoặc bị hủy -> status cancelled và TRẢ LẠI sold. Kiểm tra lazy khi xem/thanh toán + artisan command orders:expire-pending.
- Thanh toán giả lập (chọn payment_method: bank_transfer|momo|card, bấm "Thanh toán"):
  - Trong `DB::transaction() + lockForUpdate()` (`OrderService::payOrder`): đơn -> paid, set paid_at, xóa expires_at, sinh Ticket cho từng vé với `ticket_code` unique (`TKT-` + 10 ký tự), `qr_payload` JSON `{"v":1,"ticket_code":"TKT-..."}` và `qr_signature = hash_hmac('sha256', qr_payload, APP_KEY)`.
  - Sau khi transaction hoàn tất (`OrderController@pay`): gửi `OrderPaidMail` (`resources/views/emails/order-paid.blade.php`) đính kèm toàn bộ vé PDF tạo động trong RAM qua `TicketPdfService::output()` (bọc `try/catch` + `report()` để lỗi mail transport không ảnh hưởng đơn đã thanh toán). Đơn paid khách không được hủy.
- Check-in (`TicketCheckinService::checkIn` + `Organizer\CheckinController`):
  - Hỗ trợ 2 cách nhập liệu trên `organizer/checkin/form.blade.php`: quét mã QR bằng Camera (`public/js/html5-qrcode.min.js` chạy offline, yêu cầu `http://localhost:8000` hoặc HTTPS) hoặc nhập tay mã `TKT-...` (tự động chuẩn hóa chữ hoa).
  - Xử lý trong `DB::transaction() + lockForUpdate()` trên bảng `tickets` để chống check-in trùng đồng thời.
  - Kiểm tra 6 tầng: (1) tồn tại mã vé -> (2) chữ ký số HMAC-SHA256 hợp lệ qua `hash_equals()` nếu đầu vào là JSON QR payload -> (3) đơn hàng đã `paid` -> (4) vé thuộc sự kiện của organizer đang đăng nhập (hoặc admin) -> (5) vé chưa `cancelled` -> (6) vé chưa `used`.
  - Hợp lệ -> cập nhật `status = used`, `checked_in_at = now()`, `checked_in_by = $organizer->id` và flash mảng `success_ticket` (`ticket_code`, `event_title`, `ticket_type_name`, `zone`, `customer_name`, `customer_phone`, `checked_in_at`, `checked_in_by`, `qr_signature_valid`, `order_code`). Quét QR lỗi không điền ngược chuỗi JSON thô vào ô nhập tay.
- Không xóa category/venue đang có event. Không xóa event đã có đơn paid (dùng cancel); khi Admin cancel event sẽ tự động cascade hủy đơn pending hoàn vé và hủy vé valid trong `DB::transaction() + lockForUpdate()`; event dùng softDeletes.
- Tiền tệ VND (number_format, hậu tố "đ"), ngày giờ d/m/Y H:i, timezone Asia/Ho_Chi_Minh, APP_LOCALE=vi. Validation message tự viết tiếng Việt trong Form Request (messages() + attributes()).
- Vé điện tử QR & PDF:
  - `QrTicketService` (`endroid/qr-code`): sinh ảnh QR PNG/SVG Data URI từ `qr_payload` (có fallback từ `ticket_code`), xác thực chữ ký HMAC-SHA256 và giải mã JSON payload.
  - `TicketPdfService` (`barryvdh/laravel-dompdf`): render view `resources/views/pdf/ticket.blade.php` (bố cục `<table>`, font `DejaVu Sans` Unicode, khổ `A5 portrait`) hoàn toàn trong RAM (không lưu file PDF trên đĩa), tên file tải `ve-{ticket_code}.pdf`.
  - Route `GET /my-tickets/{ticket}/pdf` (`tickets.pdf`) kiểm tra quyền qua `OrderPolicy@view` (chủ đơn, organizer của sự kiện, admin; người lạ -> 403).

## Hạ tầng & Kiểm thử
- CSDL chính: MySQL 8.0 (`InnoDB`, `utf8mb4_unicode_ci`) theo `.env.example` (tương thích XAMPP/Laragon/Docker; PHPUnit hỗ trợ cả SQLite `:memory:` và MySQL 8.0).
- PHP Extension bắt buộc: `gd` (sinh PNG QR & DomPDF), `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip`.
- Email: `.env.example` mặc định `MAIL_MAILER=log` (chạy offline an toàn); dev dùng Mailpit (`MAIL_MAILER=smtp`, `127.0.0.1:1025`).
- CI/CD: `.github/workflows/ci.yml` tự động chạy `vendor/bin/pint --test`, `php artisan migrate:fresh --seed --force` và `php artisan test` trên MySQL 8.0.

## Routes (name là hợp đồng). routes/web.php chỉ require 4 file: auth.php, storefront.php, organizer.php, admin.php
auth.php: GET /register (register), POST /register (register.store), GET /login (login), POST /login (login.store, throttle:5,1), POST /logout (logout), GET /profile (profile.edit), PUT /profile (profile.update). Redirect sau đăng nhập theo role: admin -> admin.dashboard, organizer -> organizer.dashboard, customer -> home.
storefront.php:
- GET / (home): tìm theo q (title), lọc category, city, from, to; sắp xếp sort=date_asc|date_desc|price_asc|price_desc; paginate(9)->withQueryString(); chỉ sự kiện published, chưa kết thúc.
- GET /events/{event:slug} (events.show): event không published -> 404; hiển thị zone & description từng loại vé.
- Nhóm [auth, active]: POST /events/{event:slug}/checkout (checkout.store); GET /orders (orders.index); GET /orders/{order:order_code} (orders.show); POST /orders/{order:order_code}/pay (orders.pay); POST /orders/{order:order_code}/cancel (orders.cancel); GET /my-tickets (tickets.index); GET /my-tickets/{ticket}/pdf (tickets.pdf).
organizer.php: prefix organizer, name organizer., middleware [auth, active, role:organizer]: dashboard; Route::resource events; POST events/{event}/submit (events.submit); ticket-types lồng shallow trong events (create, store, edit, update, destroy); orders index + show (chỉ đơn thuộc sự kiện của mình); GET checkin (checkin.form), POST checkin (checkin.store).
admin.php: prefix admin, name admin., middleware [auth, active, role:admin]: dashboard; resource categories, venues (bỏ show); users: index, edit, update, POST users/{user}/toggle; events: index, show, POST events/{event}/approve | reject | cancel; orders: index, show.
Middleware alias: role (nhận nhiều role, vd role:admin,organizer) và active (tài khoản bị khóa -> logout + báo lỗi). Policy: EventPolicy, TicketTypePolicy, OrderPolicy (chặn truy cập chéo dữ liệu người khác -> 403).

## Dữ liệu mẫu (seeder)
1 admin (admin@example.com), 2 organizer (organizer1@example.com, organizer2@example.com), 5 customer (customer1..5@example.com); mật khẩu tất cả là "password". 5 category, 4 venue, ~8 event (đa số published, có 1 draft, 1 pending, 1 cancelled), mỗi event 2-3 loại vé, một số đơn paid (có ticket, có ticket used), một số pending và cancelled.
