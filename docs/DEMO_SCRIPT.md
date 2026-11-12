# KỊCH BẢN DEMO VÀ BỘ CÂU HỎI VẤN ĐÁP BẢO VỆ ĐỒ ÁN
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN

- **Nhóm thực hiện**: **Nhóm 8 — Lớp 68PM2**
- **Thành viên**: Hồ Đăng Quang (`0021568`), Tống Xuân Định (`0008268`), Nguyễn Hữu Quý (`0022968`)

---

### PHẦN 1: KỊCH BẢN DEMO HỆ THỐNG (8 - 10 PHÚT)

#### Chuẩn bị trước khi demo:
1. Mở terminal tại thư mục dự án và chạy server:
   ```bash
   php artisan serve
   ```
2. Mở trình duyệt tại địa chỉ: `http://localhost:8000`
3. Danh sách tài khoản demo (Mật khẩu chung cho tất cả tài khoản: `password`):
   - **Quản trị viên (Admin)**: `admin@example.com`
   - **Ban tổ chức (Organizer)**: `organizer1@example.com`, `organizer2@example.com`
   - **Khách hàng (Customer)**: `customer1@example.com` (hoặc đăng ký mới)

---

#### TIẾN TRÌNH TRÌNH DIỄN THEO TỪNG VAI TRÒ

#### Chặng 1: Khách vãng lai & Khách hàng mua vé (Thời lượng: ~3.5 phút — Phụ trách: Hồ Đăng Quang)
1. **Khách vãng lai (Guest)**:
   - Truy cập trang chủ `http://localhost:8000/`.
   - Giới thiệu giao diện hiện đại: Thanh tìm kiếm, bộ lọc Danh mục, Thành phố, Khoảng thời gian, bộ sắp xếp (Ngày tổ chức, Giá vé tăng/giảm).
   - Thử tìm kiếm sự kiện với từ khóa (ví dụ: "Âm nhạc" hoặc "Công nghệ"), lọc theo thành phố "Hà Nội" hoặc "Hồ Chí Minh".
   - Bấm vào một sự kiện để xem trang chi tiết: Giới thiệu thông tin thời gian, địa điểm, sức chứa, mô tả và bảng các loại vé đang mở bán kèm số lượng còn lại.
2. **Đăng nhập & Đặt vé**:
   - Bấm "Đăng nhập" với tài khoản `customer1@example.com` / `password`.
   - Quay lại trang chi tiết sự kiện, chọn số lượng vé (ví dụ: 2 vé VIP).
   - Bấm nút **"Đặt vé ngay"**: Hệ thống khởi tạo đơn hàng với trạng thái `Pending` và hiển thị đồng hồ đếm ngược 15 phút giữ chỗ.
   - Trình bày mã đơn hàng định dạng `ORD-XXXXXXXX`.
3. **Thanh toán giả lập**:
   - Chọn phương thức thanh toán: "Chuyển khoản ngân hàng" hoặc "Ví điện tử MoMo".
   - Nhấn nút **"Xác nhận thanh toán"**: Đơn hàng chuyển sang trạng thái `Paid` (Đã thanh toán), hiển thị ngày giờ thanh toán.
   - Chuyển sang mục **"Vé của tôi"** (`/my-tickets`):
     - Hiển thị danh sách vé điện tử đã mua.
     - Mỗi vé có mã độc nhất `TKT-XXXXXXXXXX` dạng font Monospace nổi bật.
     - Bấm nút **"In vé"** để hiển thị giao diện xem trước bản in sạch sẽ, chuyên nghiệp.

---

#### Chặng 2: Ban tổ chức sự kiện (Organizer) (Thời lượng: ~3.5 phút — Phụ trách: Tống Xuân Định)
1. **Đăng nhập Ban tổ chức**:
   - Đăng xuất tài khoản khách hàng, đăng nhập bằng `organizer1@example.com` / `password`.
   - Hệ thống tự động chuyển hướng đến **Dashboard Ban tổ chức** (`/organizer`):
     - Trình bày các thẻ số liệu: Tổng sự kiện, Tổng vé đã bán, Doanh thu ước tính.
     - Bảng danh sách các sự kiện kèm tiến độ bán vé.
2. **Quy trình tạo sự kiện & Quản lý loại vé**:
   - Vào menu "Sự kiện của tôi" -> Bấm **"Tạo sự kiện mới"**.
   - Nhập tiêu đề, chọn danh mục, địa điểm, thời gian bắt đầu/kết thúc (ở tương lai), mô tả, upload ảnh banner sự kiện.
   - Bấm "Lưu nháp": Sự kiện được tạo ở trạng thái `Draft`.
   - Nhấn "Quản lý loại vé": Thêm loại vé mới (ví dụ: Vé Tiêu chuẩn, giá 150.000đ, số lượng 100 vé, thời gian mở/đóng bán).
   - Bấm nút **"Gửi duyệt sự kiện"**: Trạng thái sự kiện chuyển thành `Pending` chờ Admin phê duyệt.
3. **Soát vé tại cửa sự kiện (Check-in)**:
   - Truy cập chức năng **"Soát vé (Check-in)"** (`/organizer/checkin`).
   - Copy một mã vé hợp lệ vừa mua ở Chặng 1 và dán vào ô kiểm tra -> Bấm **"Kiểm tra vé"**:
     - Hệ thống báo: "Check-in thành công! Vé hợp lệ." kèm tên sự kiện, loại vé và khách hàng.
   - Thử bấm check-in lại chính mã vé đó -> Hệ thống báo lỗi: "Vé đã được sử dụng vào lúc [thời gian]."
   - Thử nhập mã vé ngẫu nhiên sai -> Hệ thống báo: "Mã vé không tồn tại trong hệ thống."

---

#### Chặng 3: Quản trị viên hệ thống (Admin) & Kiểm thử (Thời lượng: ~2–3 phút — Phụ trách: Nguyễn Hữu Quý)
1. **Đăng nhập Quản trị viên**:
   - Đăng xuất, đăng nhập bằng `admin@example.com` / `password`.
   - Hệ thống tự động chuyển hướng đến **Admin Dashboard** (`/admin`):
     - Thống kê tổng số người dùng theo vai trò, sự kiện theo từng trạng thái, tổng doanh thu toàn sàn từ các đơn đã thanh toán.
2. **Phê duyệt sự kiện**:
   - Vào mục "Quản lý sự kiện" (`/admin/events`).
   - Lọc danh sách sự kiện ở trạng thái `Pending` (sự kiện vừa được Organizer gửi duyệt).
   - Bấm "Xem chi tiết" và nhấn **"Duyệt sự kiện"** (`Approve`): Sự kiện chuyển sang `Published` và ngay lập tức hiển thị công khai ở Trang chủ.
3. **Quản lý Danh mục & Địa điểm**:
   - Vào mục "Danh mục": Thử xóa một danh mục đang có sự kiện liên kết -> Hệ thống từ chối xóa và hiển thị thông báo lỗi tiếng Việt bảo đảm toàn vẹn dữ liệu.
4. **Quản lý Người dùng**:
   - Vào mục "Người dùng" (`/admin/users`): Thử tính năng Khóa / Mở khóa tài khoản khách hàng (`is_active`).

---

### PHẦN 2: BỘ 15 CÂU HỎI VẤN ĐÁP THƯỜNG GẶP KÈM GỢI Ý TRẢ LỜI

#### Nhóm 1: Cơ sở dữ liệu & Kiến trúc (Database & Architecture)
1. **Câu 1: Làm thế nào để hệ thống ngăn chặn tình trạng bán vượt số lượng vé (Overbooking) khi có nhiều người cùng mua vé tại một thời điểm?**
   - *Gợi ý trả lời*: Hệ thống sử dụng cơ chế Khóa bi quan (`Pessimistic Locking`) với phương thức `lockForUpdate()` trên bảng `ticket_types` bên trong một `DB::transaction()`. Khi người dùng đặt vé, dòng dữ liệu của loại vé đó được khóa lại trong giao dịch để kiểm tra số lượng tồn (`quantity - sold >= số lượng đặt`), sau đó tăng ngay cột `sold` (giữ chỗ) trước khi commit transaction.

2. **Câu 2: Cơ chế giữ chỗ 15 phút hoạt động như thế nào? Nếu người dùng không thanh toán thì xử lý ra sao?**
   - *Gợi ý trả lời*: Khi tạo đơn hàng `Pending`, hệ thống thiết lập cột `expires_at = now()->addMinutes(15)`. Hệ thống áp dụng 2 cơ chế:
     - *Lazy check*: Khi khách hàng truy cập đơn hàng hoặc bấm thanh toán, hệ thống kiểm tra nếu `now() > expires_at` thì tự động hủy đơn và hoàn lại số lượng `sold`.
     - *Scheduled Artisan Command*: Lệnh `php artisan orders:expire-pending` được lập lịch chạy định kỳ để quét và hủy các đơn quá hạn, giải phóng vé cho người khác mua.

3. **Câu 3: Tại sao lại chọn cột trạng thái kiểu string thay vì kiểu enum trực tiếp trong database?**
   - *Gợi ý trả lời*: Cột string kết hợp với hằng số (Class Constants hoặc PHP Enum) trong Laravel giúp hệ thống linh hoạt hơn khi cần mở rộng thêm trạng thái mới mà không cần chạy migration sửa schema bảng (vốn rất rủi ro trên CSDL lớn trong môi trường production). Đồng thời, kiểu string đảm bảo tương thích tuyệt đối giữa cả SQLite và MySQL.

4. **Câu 4: Dự án đã tối ưu các truy vấn CSDL để phòng tránh lỗi N+1 Query như thế nào?**
   - *Gợi ý trả lời*: Ở Trang chủ và danh sách sự kiện, em sử dụng Eager Loading: `Event::with(['category', 'venue', 'ticketTypes'])`. Để sắp xếp theo giá vé thấp nhất, em sử dụng phương thức `withMin('ticketTypes as min_price', 'price')` thay vì lặp qua từng quan hệ. Ở trang thống kê Dashboard, em dùng `withCount()` và `withSum()` để tính toán trực tiếp từ DB.

5. **Câu 5: Tại sao bảng sự kiện (events) lại áp dụng SoftDeletes thay vì xóa vật lý?**
   - *Gợi ý trả lời*: Vì sự kiện là thực thể trung tâm, liên quan mật thiết đến các loại vé, đơn hàng và vé đã thanh toán của khách hàng. Xóa vật lý sẽ gây mất mát dữ liệu lịch sử tài chính và vi phạm khóa ngoại. SoftDeletes giúp ẩn sự kiện khỏi hệ thống mà vẫn lưu vết kế toán đầy đủ.

---

#### Nhóm 2: Bảo mật & Phân quyền (Security & Authorization)
6. **Câu 6: Hệ thống phân quyền đa vai trò (Multi-role) được cài đặt như thế nào trong Laravel?**
   - *Gợi ý trả lời*: Hệ thống quản lý trường `role` trong bảng `users` (admin, organizer, customer). Em tạo custom middleware alias `role` nhận tham số (ví dụ: `role:organizer` hoặc `role:admin,organizer`). Đồng thời, em dùng middleware `active` để lập tức đăng xuất và chặn các tài khoản bị Admin khóa (`is_active = false`).

7. **Câu 7: Làm sao ngăn chặn người dùng truy cập trái phép hoặc sửa dữ liệu của người khác (IDOR / Horizontal Privilege Escalation)?**
   - *Gợi ý trả lời*: Em sử dụng các `Policy` của Laravel (`EventPolicy`, `TicketTypePolicy`, `OrderPolicy`). Ví dụ, `EventPolicy` kiểm tra `$user->id === $event->organizer_id` trước khi cho phép chỉnh sửa sự kiện, và `OrderPolicy` kiểm tra `$user->id === $order->user_id` trước khi cho xem hoặc thanh toán đơn hàng. Nếu không thỏa mãn, Laravel sẽ trả về mã lỗi HTTP 403 Forbidden.

8. **Câu 8: Hệ thống phòng chống tấn công CSRF và XSS như thế nào?**
   - *Gợi ý trả lời*:
     - Với CSRF: Toàn bộ form gửi dữ liệu đều chứa `@csrf`, Laravel tự động tạo và xác thực CSRF Token qua middleware `ValidateCsrfToken`.
     - Với XSS: Tất cả dữ liệu hiển thị trên Blade view đều dùng cú pháp `{{ $data }}` để tự động qua hàm `htmlspecialchars()`, không sử dụng `{!! !!}` với dữ liệu người dùng nhập.

9. **Câu 9: Hệ thống phòng chống Brute-force mật khẩu tại trang đăng nhập như thế nào?**
   - *Gợi ý trả lời*: Tuyến route đăng nhập được gắn middleware `throttle:5,1`, giới hạn tối đa 5 lần thử đăng nhập sai trong vòng 1 phút cho mỗi IP/Email. Nếu vượt quá, người dùng sẽ bị tạm khóa thao tác đăng nhập.

---

#### Nhóm 3: Nghiệp vụ & Kỹ thuật lập trình (Business Logic & Coding)
10. **Câu 10: Logic xử lý thanh toán giả lập và sinh mã vé được thiết kế ra sao?**
    - *Gợi ý trả lời*: Logic được đóng gói trong `OrderService::payOrder()`. Toàn bộ thao tác cập nhật đơn hàng thành `paid`, ghi nhận `paid_at`, duyệt qua từng `order_item` để tạo số lượng bản ghi `tickets` tương ứng được đặt trong `DB::transaction()`. Mã vé được sinh ngẫu nhiên định dạng `TKT-XXXXXXXXXX` và kiểm tra chống trùng lặp trước khi lưu.

11. **Câu 11: Khi kiểm tra vé (Check-in), hệ thống xử lý những tình huống ngoại lệ nào?**
    - *Gợi ý trả lời*: `TicketCheckinService` thực hiện tuần tự 5 bước kiểm tra và ném `BusinessException` với thông báo tiếng Việt cụ thể:
      1. Mã vé không tồn tại trong hệ thống.
      2. Đơn hàng của vé chưa thanh toán.
      3. Vé đã được sử dụng trước đó (`status == 'used'`).
      4. Vé đã bị hủy (`status == 'cancelled'`).
      5. Vé không thuộc sự kiện do Organizer đang đăng nhập quản lý.
      Nếu vượt qua cả 5 bước, vé được cập nhật thành `used` và lưu `checked_in_at = now()`.

12. **Câu 12: Tại sao lại tạo class `BusinessException` riêng và nó hoạt động như thế nào?**
    - *Gợi ý trả lời*: `BusinessException` kế thừa từ `Exception`, có phương thức `render($request)` tùy biến trả về `back()->withInput()->with('error', $this->getMessage())`. Nhờ đó, khi tầng Service gặp vi phạm quy tắc nghiệp vụ, nó chỉ cần ném ngoại lệ với chuỗi thông báo tiếng Việt ngắn gọn, Controller không cần bắt try-catch thủ công mà giao diện vẫn nhận được flash message lỗi hiển thị đẹp mắt cho người dùng.

13. **Câu 13: Quy tắc ràng buộc khi xóa Danh mục (Category) hoặc Địa điểm (Venue) được cài đặt như thế nào?**
    - *Gợi ý trả lời*: Trong `CategoryController` và `VenueController`, trước khi thực hiện xóa, hệ thống gọi `$category->events()->exists()` hoặc `$venue->events()->exists()`. Nếu còn sự kiện liên kết, hệ thống chặn lại và trả về thông báo lỗi: "Không thể xóa danh mục/địa điểm này vì đang có sự kiện liên kết".

14. **Câu 14: Làm thế nào để đảm bảo hệ thống chạy tốt mà không cần cài đặt Node.js hay chạy npm/Vite?**
    - *Gợi ý trả lời*: Giao diện sử dụng thuần HTML/Blade kết hợp Bootstrap 5.3 CSS và Bootstrap Icons thông qua CDN tốc độ cao jsDelivr. Toàn bộ hiệu ứng, modal, collapse đều sử dụng Javascript bundle của Bootstrap qua CDN, không phụ thuộc vào `@vite` hay quá trình build frontend cục bộ, giúp dự án dễ dàng triển khai và chấm điểm trên bất kỳ máy nào có PHP.

15. **Câu 15: Nếu muốn chuyển đổi CSDL từ SQLite sang MySQL thì cần thực hiện những bước nào?**
    - *Gợi ý trả lời*: Chỉ cần 2 bước trong file cấu hình `.env`:
      1. Đổi `DB_CONNECTION=sqlite` thành `DB_CONNECTION=mysql`.
      2. Bỏ chú thích và điền thông số `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=event_ticket`, `DB_USERNAME=root`, `DB_PASSWORD=`.
      Sau đó chạy `php artisan migrate:fresh --seed` là toàn bộ cấu trúc và dữ liệu mẫu sẽ được tạo lập hoàn hảo trên MySQL.
