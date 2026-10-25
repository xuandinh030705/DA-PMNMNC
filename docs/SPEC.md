# SPEC NGHIỆP VỤ - HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN

## Vai trò
- guest: xem/tìm/lọc sự kiện, xem chi tiết, đăng ký, đăng nhập.
- customer: đặt vé, thanh toán giả lập, xem đơn và vé của mình, hủy đơn pending, sửa hồ sơ.
- organizer: CRUD sự kiện của mình + loại vé, gửi duyệt, xem đơn của sự kiện mình, check-in vé, dashboard thống kê.
- admin: duyệt/từ chối/hủy sự kiện, CRUD danh mục + địa điểm, quản lý người dùng (khóa/mở, đổi vai trò), xem mọi đơn, dashboard thống kê.

## Database
users: id, name, email(unique), password, phone(null), role(admin|organizer|customer, default customer), is_active(bool, default true), remember_token, timestamps
categories: id, name(unique), slug(unique), description(null), timestamps
venues: id, name, address, city, capacity(unsigned int), timestamps
events: id, organizer_id(FK users), category_id(FK), venue_id(FK), title, slug(unique), description(text), banner_path(null), start_time, end_time, status(draft|pending|published|cancelled, default draft), timestamps, softDeletes
ticket_types: id, event_id(FK cascade), name, price(decimal 12,0), quantity(unsigned int), sold(unsigned int, default 0), sale_start, sale_end, max_per_order(unsigned int, default 10), timestamps
orders: id, user_id(FK), order_code(unique), customer_name, customer_email, customer_phone, total_amount(decimal 12,0), status(pending|paid|cancelled), payment_method(null), paid_at(null), expires_at(null), timestamps
order_items: id, order_id(FK cascade), ticket_type_id(FK), quantity, unit_price(decimal 12,0), subtotal(decimal 12,0), timestamps
tickets: id, order_item_id(FK cascade), ticket_code(unique), status(valid|used|cancelled, default valid), checked_in_at(null), timestamps
Index: events(status,start_time), events(category_id), orders(user_id,status).
Quan hệ: User 1-n Event (organizer), User 1-n Order; Category 1-n Event; Venue 1-n Event; Event 1-n TicketType; Order 1-n OrderItem; TicketType 1-n OrderItem; OrderItem 1-n Ticket.

## Quy tắc nghiệp vụ
- Vòng đời sự kiện: draft -> (organizer gửi duyệt) pending -> (admin duyệt) published. Admin có thể reject (pending -> draft) hoặc cancel (-> cancelled). Organizer chỉ sửa khi draft; chỉ gửi duyệt khi có >= 1 loại vé và start_time > hiện tại.
- Chỉ mua được khi: event published, chưa bắt đầu, loại vé trong khoảng sale_start..sale_end, còn vé (quantity - sold > 0).
- Đặt vé: mỗi đơn >= 1 vé; mỗi loại <= max_per_order và <= số còn lại. Xử lý trong DB::transaction + lockForUpdate trên ticket_types để không bán vượt. Tăng sold ngay khi tạo đơn pending (giữ chỗ), expires_at = now + 15 phút. order_code dạng "ORD-" + 8 ký tự in hoa, unique (retry nếu trùng).
- Đơn pending hết hạn hoặc bị hủy -> status cancelled và TRẢ LẠI sold. Kiểm tra lazy khi xem/thanh toán + artisan command orders:expire-pending.
- Thanh toán giả lập (chọn payment_method: bank_transfer|momo|card, bấm "Thanh toán"): đơn -> paid, set paid_at, sinh Ticket cho từng vé với ticket_code unique (retry nếu trùng), tất cả trong transaction. Đơn paid khách không được hủy.
- Check-in (organizer): nhập ticket_code. Báo lỗi rõ ràng khi: không tồn tại / đơn chưa paid / vé đã dùng / vé đã hủy / vé không thuộc sự kiện của organizer này. Hợp lệ -> status used + checked_in_at.
- Không xóa category/venue đang có event. Không xóa event đã có đơn paid (dùng cancel); event dùng softDeletes.
- Tiền tệ VND (number_format, hậu tố "đ"), ngày giờ d/m/Y H:i, timezone Asia/Ho_Chi_Minh, APP_LOCALE=vi. Validation message tự viết tiếng Việt trong Form Request (messages() + attributes()).
- Không cần QR code; hiển thị vé dạng thẻ có ticket_code (font monospace) và nút in.

## Routes (name là hợp đồng). routes/web.php chỉ require 4 file: auth.php, storefront.php, organizer.php, admin.php
auth.php: GET /register (register), POST /register (register.store), GET /login (login), POST /login (login.store, throttle:5,1), POST /logout (logout), GET /profile (profile.edit), PUT /profile (profile.update). Redirect sau đăng nhập theo role: admin -> admin.dashboard, organizer -> organizer.dashboard, customer -> home.
storefront.php:
- GET / (home): tìm theo q (title), lọc category, city, from, to; sắp xếp sort=date_asc|date_desc|price_asc|price_desc; paginate(9)->withQueryString(); chỉ sự kiện published, chưa kết thúc.
- GET /events/{event:slug} (events.show): event không published -> 404.
- Nhóm [auth, active]: POST /events/{event:slug}/checkout (checkout.store); GET /orders (orders.index); GET /orders/{order:order_code} (orders.show); POST /orders/{order:order_code}/pay (orders.pay); POST /orders/{order:order_code}/cancel (orders.cancel); GET /my-tickets (tickets.index).
organizer.php: prefix organizer, name organizer., middleware [auth, active, role:organizer]: dashboard; Route::resource events; POST events/{event}/submit (events.submit); ticket-types lồng shallow trong events (create, store, edit, update, destroy); orders index + show (chỉ đơn thuộc sự kiện của mình); GET checkin (checkin.form), POST checkin (checkin.store).
admin.php: prefix admin, name admin., middleware [auth, active, role:admin]: dashboard; resource categories, venues (bỏ show); users: index, edit, update, POST users/{user}/toggle; events: index, show, POST events/{event}/approve | reject | cancel; orders: index, show.
Middleware alias: role (nhận nhiều role, vd role:admin,organizer) và active (tài khoản bị khóa -> logout + báo lỗi). Policy: EventPolicy, TicketTypePolicy, OrderPolicy (chặn truy cập chéo dữ liệu người khác -> 403).

## Dữ liệu mẫu (seeder)
1 admin (admin@example.com), 2 organizer (organizer1@example.com, organizer2@example.com), 5 customer (customer1..5@example.com); mật khẩu tất cả là "password". 5 category, 4 venue, ~8 event (đa số published, có 1 draft, 1 pending, 1 cancelled), mỗi event 2-3 loại vé, một số đơn paid (có ticket, có ticket used), một số pending và cancelled.
