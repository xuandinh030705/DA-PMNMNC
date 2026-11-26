# KỊCH BẢN DEMO VÀ BỘ CÂU HỎI VẤN ĐÁP BẢO VỆ ĐỒ ÁN
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN

- **Nhóm thực hiện**: **Nhóm 8 — Lớp 68PM2**
- **Thành viên**: Hồ Đăng Quang (`0021568`), Tống Xuân Định (`0008268`), Nguyễn Hữu Quý (`0022968`)

---

### PHẦN 1: KỊCH BẢN DEMO HỆ THỐNG (8 - 10 PHÚT)

#### Chuẩn bị trước khi demo:
1. Kiểm tra `php.ini` đã bật `extension=gd` và `extension=pdo_mysql`; kết nối **MySQL 8.0** (hoặc SQLite dự phòng).
2. *(Tùy chọn test Email trực quan)* Bật `mailpit.exe` ở cửa sổ riêng, cấu hình `.env`: `MAIL_MAILER=smtp`, `MAIL_HOST=127.0.0.1`, `MAIL_PORT=1025`, và mở sẵn `http://localhost:8025` *(nếu demo offline không bật Mailpit, giữ `MAIL_MAILER=log` vẫn chạy an toàn không lỗi)*.
3. Khởi động server tại thư mục dự án:
   ```bash
   php artisan serve
   ```
4. Mở trình duyệt tại **`http://localhost:8000`** *(bắt buộc dùng `localhost` hoặc HTTPS để trình duyệt cấp quyền mở webcam khi quét QR)*.
5. Danh sách tài khoản demo (Mật khẩu chung cho tất cả tài khoản: `password`):
   - **Quản trị viên (Admin)**: `admin@example.com`
   - **Ban tổ chức (Organizer)**: `organizer1@example.com`, `organizer2@example.com`
   - **Khách hàng (Customer)**: `customer1@example.com` (hoặc đăng ký mới)

---

#### TIẾN TRÌNH TRÌNH DIỄN THEO TỪNG VAI TRÒ

#### Chặng 1: Khách vãng lai & Khách hàng mua vé, QR, PDF & Email (~3.5 phút — Phụ trách: Hồ Đăng Quang)
1. **Khách vãng lai (Guest)**:
   - Truy cập trang chủ `http://localhost:8000/`.
   - Giới thiệu giao diện Storefront (Bootstrap 5.3 Zero-node): Thanh tìm kiếm `q`, bộ lọc Danh mục, Thành phố, Khoảng thời gian, bộ sắp xếp (Ngày tổ chức, Giá vé tăng/giảm).
   - Bấm vào một sự kiện để xem trang chi tiết: Trình bày thông tin địa điểm, sức chứa, mô tả chia đoạn rõ ràng, và bảng loại vé hiển thị **khu vực (`zone`)** kèm **quyền lợi (`description`)** và số lượng còn lại.
2. **Đăng nhập, Đặt vé & Giữ chỗ 15 phút**:
   - Đăng nhập tài khoản `customer1@example.com` / `password`.
   - Chọn số lượng vé (ví dụ: 2 vé VIP), nhấn **"Đặt Vé & Giữ Chỗ (15 Phút)"** → giới thiệu mã đơn `ORD-XXXXXXXX` ở trạng thái `Pending` và giải thích `DB::transaction() + lockForUpdate()` chống overselling.
3. **Thanh toán giả lập, Email PDF & Vé điện tử QR**:
   - Chọn phương thức thanh toán (`Chuyển khoản` / `MoMo` / `Thẻ`) và bấm **"Xác Nhận Thanh Toán"**.
   - Đơn chuyển sang `Paid`, tự động sinh mã vé `TKT-XXXXXXXXXX` kèm payload JSON và chữ ký số **HMAC-SHA256**.
   - Mở **Mailpit** (`http://localhost:8025`) cho Hội đồng xem email xác nhận `OrderPaidMail` vừa gửi tới, đính kèm sẵn file vé PDF.
   - Quay lại mục **"Vé của tôi"** (`/my-tickets`) hoặc **"Chi tiết đơn hàng"**: chỉ ra ảnh **mã QR Code** hiển thị trực tiếp, sau đó bấm **"Tải vé PDF"** để tải file `ve-TKT-XXXXXXXXXX.pdf` khổ A5 tiếng Việt chuẩn Unicode (sinh trực tiếp trong RAM).

---

#### Chặng 2: Ban tổ chức sự kiện (Organizer) & Camera Check-in (~3.5 phút — Phụ trách: Tống Xuân Định)
1. **Đăng nhập Ban tổ chức**:
   - Đăng xuất, đăng nhập bằng `organizer1@example.com` / `password` → vào **Dashboard Ban tổ chức** (`/organizer`), giới thiệu các thẻ thống kê tổng sự kiện, vé đã bán, doanh thu.
2. **Tạo sự kiện & Loại vé có Zone / Quyền lợi**:
   - Vào **"Sự kiện của tôi" > "Tạo sự kiện mới"**, nhập thông tin và upload ảnh banner → lưu bản nháp (`Draft`).
   - Thêm loại vé mới: nhập thêm trường **Khu vực / Vị trí (`zone`)** và **Quyền lợi (`description`)**, giá vé, số lượng, thời gian mở/đóng bán.
   - Bấm **"Gửi duyệt sự kiện"** → trạng thái chuyển thành `Pending`.
3. **Soát vé tại cổng bằng Camera QR & Nhập tay (`/organizer/checkin`)**:
   - Bấm nút **"Bật camera"** (`html5-qrcode.min.js` chạy 100% offline), đưa mã QR của vé PDF vừa tải ở Chặng 1 trước camera (hoặc thử nhập/dán mã).
   - Hệ thống tự giải mã JSON payload, kiểm tra **6 tầng bảo mật** (kể cả chữ ký số HMAC-SHA256) và hiển thị thẻ xanh **CHECK-IN THÀNH CÔNG** kèm khu vực (`zone`), thời điểm check-in, huy hiệu **HMAC-SHA256 hợp lệ** và tên **Nhân viên soát vé (`checked_in_by`)**.
   - Thử quét lại chính vé đó lần 2 → hệ thống chặn ngay: `"Vé này đã được sử dụng..."`.
   - Thử gửi payload JSON bị sửa mã vé (hoặc nhập mã không tồn tại) → hệ thống phát hiện chữ ký HMAC không khớp và từ chối check-in.

---

#### Chặng 3: Quản trị viên (Admin), Cascade Hủy sự kiện & Kiểm thử CI (~2–3 phút — Phụ trách: Nguyễn Hữu Quý)
1. **Đăng nhập Quản trị viên**:
   - Đăng xuất, đăng nhập `admin@example.com` / `password` → vào **Admin Dashboard** (`/admin`), giới thiệu thống kê người dùng, sự kiện, doanh thu toàn sàn và top sự kiện bán chạy.
2. **Phê duyệt & Hủy sự kiện Cascade**:
   - Vào `/admin/events`, mở sự kiện `Pending` vừa tạo ở Chặng 2 và bấm **"Phê Duyệt Sự Kiện"** → sự kiện công khai lên Storefront.
   - Trình bày tính năng **Hủy sự kiện Cascade**: mở một sự kiện có đơn `pending`/vé `valid` và bấm **"Hủy Sự Kiện"** → hệ thống thực thi trong transaction: tự hủy các đơn `pending` (hoàn trả `sold`), đồng thời vô hiệu hóa toàn bộ vé `valid` sang `cancelled` (giữ nguyên vé `used`).
   - Mở chi tiết đơn hàng tại `/admin/orders/{code}` để cho thấy đầy đủ `zone`, `description` và thông tin **Nhân viên soát vé (`checkedInBy`)**.
3. **Toàn vẹn dữ liệu, Kiểm thử tự động & CI**:
   - Thử xóa danh mục đang có sự kiện → bị chặn (`RESTRICT`). Thử khóa/mở khóa user tại `/admin/users`.
   - Chạy trực tiếp trên terminal: `php vendor/bin/pint --test` và `php artisan test` (**103/103 tests PASSED, 411 assertions**).
   - Mở tab **GitHub Actions** trên trình duyệt để minh chứng quy trình CI tự động kiểm tra Pint + migrate/seed + PHPUnit trên container **MySQL 8.0**.

---

### PHẦN 2: BỘ CÂU HỎI VẤN ĐÁP THƯỜNG GẶP KÈM GỢI Ý TRẢ LỜI

#### Nhóm 1: Cơ sở dữ liệu & Kiến trúc (Database & Architecture)
1. **Câu 1: Làm thế nào để hệ thống ngăn chặn tình trạng bán vượt số lượng vé (Overbooking) khi có nhiều người cùng mua vé tại một thời điểm?**
   - *Gợi ý trả lời*: Hệ thống sử dụng Khóa bi quan (`Pessimistic Locking`) với `lockForUpdate()` trên bảng `ticket_types` bên trong `DB::transaction()` ở `OrderService::createOrder()`. Trên InnoDB của MySQL 8, dòng bản ghi loại vé bị khóa ở mức hàng (`row-level lock`) để kiểm tra `quantity - sold >= qty` và cộng ngay `sold` trước khi commit.

2. **Câu 2: Cơ chế giữ chỗ 15 phút hoạt động như thế nào? Nếu người dùng không thanh toán thì xử lý ra sao?**
   - *Gợi ý trả lời*: Khi tạo đơn `Pending`, hệ thống gán `expires_at = now()->addMinutes(15)`. Khi quá hạn, vé được thu hồi qua 2 cơ chế:
     - *Lazy check*: Khi xem hoặc bấm thanh toán đơn quá hạn (`OrderService::checkAndExpire` và `payOrder`), hệ thống tự hủy đơn và hoàn trả `sold`.
     - *Scheduled Command*: Lệnh `php artisan orders:expire-pending` được đăng ký chạy mỗi phút trong `routes/console.php`.

3. **Câu 3: Tại sao lại chọn cột trạng thái kiểu string thay vì kiểu enum trực tiếp trong database?**
   - *Gợi ý trả lời*: Cột `varchar` trong DB kết hợp với **PHP Backed Enum** (`EventStatus`, `OrderStatus`, `TicketStatus`, `UserRole`) qua `casts()` trên Model giúp mở rộng trạng thái mà không cần migration `ALTER TABLE` phức tạp, đồng thời tương thích 100% giữa MySQL 8 và SQLite `:memory:` khi chạy PHPUnit.

4. **Câu 4: Dự án đã tối ưu các truy vấn CSDL để phòng tránh lỗi N+1 Query như thế nào?**
   - *Gợi ý trả lời*: Danh sách sự kiện/đơn hàng dùng `with(['category', 'venue'])`, `withCount()`, `withMin()`, `withSum()`. Ở trang chi tiết đơn hàng (`Storefront\OrderController@show`, `Organizer\OrderController@show`, `Admin\OrderController@show`), hệ thống eager load đầy đủ `tickets.orderItem.ticketType.event.venue` và `tickets.checkedInBy` (được kiểm chứng tự động bằng test hồi quy so sánh số query giữa đơn 1 vé và 6 vé trong `OrderAndPaymentTest`).

5. **Câu 5: Tại sao bảng sự kiện (`events`) lại áp dụng `SoftDeletes` thay vì xóa vật lý?**
   - *Gợi ý trả lời*: Để bảo toàn lịch sử đơn hàng, doanh thu và vé đã phát hành khi sự kiện bị xóa hoặc hủy, tránh làm đứt gãy toàn vẹn tham chiếu khóa ngoại và dữ liệu kế toán.

---

#### Nhóm 2: Bảo mật, QR HMAC & Phân quyền (Security & Authorization)
6. **Câu 6: Hệ thống phân quyền đa vai trò (Multi-role) được cài đặt như thế nào trong Laravel?**
   - *Gợi ý trả lời*: Dùng enum `UserRole` trên `users.role` kết hợp Middleware `CheckRole` (`role:admin`, `role:organizer`) và `CheckActive` (tự động `Auth::logout()` + hủy session khi tài khoản bị khóa `is_active = false`).

7. **Câu 7: Làm sao ngăn chặn người dùng truy cập trái phép hoặc tải trộm vé PDF của người khác (IDOR)?**
   - *Gợi ý trả lời*: Áp dụng 3 Policies (`EventPolicy`, `TicketTypePolicy`, `OrderPolicy`). Riêng route tải vé `GET /my-tickets/{ticket}/pdf` kiểm tra `OrderPolicy::view` (chỉ chủ đơn, Organizer sở hữu sự kiện hoặc Admin mới được tải; người lạ hoặc Organizer khác nhận HTTP `403 Forbidden`).

8. **Câu 8: Hệ thống làm thế nào để chống giả mạo hoặc tự ý sửa đổi mã QR trên vé điện tử?**
   - *Gợi ý trả lời*: Mỗi vé khi tạo sẽ sinh `qr_payload` dạng JSON `{"v":1,"ticket_code":"TKT-..."}` và chữ ký `qr_signature = hash_hmac('sha256', qr_payload, config('app.key'))`. Khi quét QR check-in, `TicketCheckinService` gọi `QrTicketService::verifySignature()` dùng hàm so sánh hằng thời gian `hash_equals()` để phát hiện mọi thay đổi dù chỉ 1 ký tự và từ chối check-in ngay lập tức.

9. **Câu 9: Hệ thống phòng chống CSRF, XSS và Brute-force mật khẩu như thế nào?**
   - *Gợi ý trả lời*:
     - CSRF: 100% form `POST`/`PUT`/`DELETE` chứa `@csrf`.
     - XSS: Toàn bộ Blade view dùng `{{ ... }}` (không dùng `{!! !!}`).
     - Brute-force: Route `POST /login` gắn middleware `throttle:5,1` (tối đa 5 lần/phút).

---

#### Nhóm 3: Nghiệp vụ nâng cao & Triển khai (PDF, Email, Camera, CI)
10. **Câu 10: Quy trình thanh toán giả lập, tạo vé PDF động và gửi Email hoạt động ra sao?**
    - *Gợi ý trả lời*: `OrderService::payOrder()` chạy trong `DB::transaction() + lockForUpdate()` cập nhật đơn thành `paid`, sinh các `Ticket` kèm `qr_payload` & `qr_signature`. Sau khi commit thành công, `OrderController@pay` gửi `OrderPaidMail`, trong đó `TicketPdfService` render view `pdf/ticket.blade.php` (bố cục `<table>`, font `DejaVu Sans` Unicode, khổ A5, nhúng QR base64) hoàn toàn **trong RAM (`$pdf->output()`)** mà không lưu file ra ổ đĩa. Bước gửi mail được bọc `try/catch` để lỗi SMTP không làm mất trạng thái đã thanh toán của đơn.

11. **Câu 11: Quy trình soát vé Check-in 6 bước và quét Camera offline hoạt động như thế nào?**
    - *Gợi ý trả lời*: Giao diện `organizer/checkin/form.blade.php` nhúng `public/js/html5-qrcode.min.js` chạy 100% offline qua `http://localhost:8000` song song với ô nhập tay. Trong `DB::transaction() + lockForUpdate()` trên bảng `tickets`, `TicketCheckinService` kiểm tra lần lượt 6 tầng: (1) tồn tại mã vé → (2) chữ ký số HMAC-SHA256 hợp lệ nếu là JSON QR → (3) đơn hàng đã `paid` → (4) thuộc sự kiện của Organizer → (5) vé chưa `cancelled` → (6) vé chưa `used`. Đạt cả 6 bước sẽ cập nhật `status = used`, `checked_in_at = now()`, `checked_in_by = $organizer->id`.

12. **Câu 12: Khi Admin hủy sự kiện, các đơn hàng và vé liên quan được xử lý thế nào?**
    - *Gợi ý trả lời*: `Admin\EventController::cancel()` thực thi trong `DB::transaction()` với `lockForUpdate()`: chuyển sự kiện sang `cancelled`, tự động hủy các đơn `pending` liên quan và cộng trả lại số lượng `sold` (có bảo vệ `max(0, ...)` tránh âm), đồng thời chuyển toàn bộ vé `valid` sang `cancelled` (giữ nguyên các vé đã `used`).

13. **Câu 13: Làm sao đảm bảo hệ thống vẫn trình diễn trơn tru khi mất Internet hoặc không có máy chủ Email?**
    - *Gợi ý trả lời*: Thư viện quét QR `html5-qrcode.min.js` lưu cục bộ trong `public/js/`, QR Code sinh cục bộ bằng `endroid/qr-code`, PDF render cục bộ trong RAM bằng `barryvdh/laravel-dompdf`, còn email trong `.env.example` mặc định `MAIL_MAILER=log` (và bọc `try/catch` ở `OrderController@pay`) nên không bao giờ văng lỗi khi demo offline.

14. **Câu 14: Tại sao dự án dùng Laravel Blade + Bootstrap 5.3 CDN mà không cần Node.js / Vite build?**
    - *Gợi ý trả lời*: Giúp cấu trúc gọn nhẹ, giảm phụ thuộc môi trường khi chấm bài (chỉ cần PHP + Composer), đồng thời toàn bộ tính năng JS tương tác quan trọng như quét Camera QR đã được đóng gói cục bộ trong `public/js/`.

15. **Câu 15: Cơ sở dữ liệu chính của hệ thống là gì và có hỗ trợ chuyển đổi linh hoạt không?**
    - *Gợi ý trả lời*: Mặc định dự án cấu hình **MySQL 8.0 (`InnoDB`, `utf8mb4_unicode_ci`)** trong `.env.example` (chạy tốt trên MySQL Community, XAMPP, Laragon, Docker và GitHub Actions CI) để đảm bảo cơ chế khóa dòng `lockForUpdate()` chuẩn xác; khi cần chấm nhanh không cài DB server chỉ cần đổi 2 dòng trong `.env` sang `DB_CONNECTION=sqlite`.
