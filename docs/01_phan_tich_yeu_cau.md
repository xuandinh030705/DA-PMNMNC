# TÀI LIỆU PHÂN TÍCH YÊU CẦU PHẦN MỀM (SRS)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

---

### 1. Giới thiệu và Mục tiêu dự án
#### 1.1. Mục tiêu dự án
Dự án nhằm xây dựng một nền tảng web hiện đại, tin cậy và tiện lợi phục vụ công tác tổ chức, phân phối và kiểm soát vé cho các sự kiện (hội thảo, ca nhạc, triển lãm, thể thao...). Hệ thống kết nối liền mạch giữa:
- **Người tham dự (Customer)**: Dễ dàng tìm kiếm sự kiện, chọn loại vé, đặt vé trực tuyến và nhận vé điện tử nhanh chóng.
- **Ban tổ chức (Organizer)**: Đăng ký sự kiện, quản lý loại vé và số lượng, kiểm soát doanh thu và soát vé (check-in) tại cổng sự kiện.
- **Quản trị viên (Admin)**: Phê duyệt sự kiện, quản trị danh mục/địa điểm và người dùng, giám sát toàn diện hoạt động của hệ thống.

#### 1.2. Phạm vi dự án
- **Trong phạm vi (In-Scope)**:
  - Xem, tìm kiếm theo tiêu đề, lọc theo danh mục/thành phố/khoảng thời gian, sắp xếp theo thời gian hoặc giá vé.
  - Quản lý xác thực: Đăng ký, đăng nhập (giới hạn tần suất throttle), đăng xuất, cập nhật hồ sơ cá nhân, phân quyền đa vai trò (Admin, Organizer, Customer).
  - Quy trình đặt vé: Giữ chỗ trong 15 phút, giao dịch nguyên tử (Database Transaction + Lock For Update), chống bán vượt số lượng vé (overbooking).
  - Thanh toán giả lập (Mock Payment) qua Chuyển khoản ngân hàng, Ví MoMo, Thẻ quốc tế/nội địa; tự động kích hoạt vé, sinh mã vé duy nhất và mã QR có chữ ký số HMAC-SHA256.
  - Tự động gửi Email xác nhận thanh toán (`OrderPaidMail`) đính kèm vé PDF tạo động trong bộ nhớ RAM qua Mailpit / Log.
  - Tải vé điện tử định dạng PDF khổ A5 on-the-fly (`barryvdh/laravel-dompdf`), bảo vệ quyền sở hữu qua `OrderPolicy`.
  - Hủy đơn và xử lý hoàn trả số lượng vé khi đơn hết hạn (Lazy check hoặc Schedule Command).
  - Quản lý sự kiện: Tạo nháp (draft), gửi duyệt (pending), phê duyệt/từ chối/hủy sự kiện; quản lý loại vé kèm khu vực khán đài (`zone`) và quyền lợi (`description`).
  - Soát vé (Check-in) linh hoạt tại cổng: Quét mã QR bằng Camera (webcam/điện thoại qua thư viện `html5-qrcode` chạy offline) kết hợp ô nhập tay dự phòng; xác thực chữ ký số HMAC-SHA256 chống làm giả vé và ghi nhận nhân viên soát vé (`checked_in_by`).
  - Quản trị viên hủy sự kiện có cơ chế cascade tự động hủy các đơn hàng Pending hoàn vé và chuyển trạng thái vé Valid sang Cancelled.
  - Bảng điều khiển thống kê (Dashboard) trực quan cho Organizer và Admin.
- **Ngoài phạm vi (Out-of-Scope)**:
  - Tích hợp cổng thanh toán thực tế với ngân hàng (chỉ mô phỏng quy trình thanh toán giả lập nhằm phục vụ kiểm thử và bảo vệ đồ án).
  - Thiết bị máy quét mã vạch chuyên dụng phần cứng đắt tiền (hệ thống tận dụng camera webcam/điện thoại có sẵn qua trình duyệt web).

---

### 2. Đối tượng sử dụng và Ma trận phân quyền (Actors & Roles)

| Vai trò (Role) | Mô tả | Quyền hạn chính |
|---|---|---|
| **Khách vãng lai (Guest)** | Người dùng chưa đăng nhập | - Xem danh sách sự kiện, tìm kiếm, lọc, sắp xếp.<br>- Xem chi tiết sự kiện và các loại vé.<br>- Đăng ký tài khoản, Đăng nhập. |
| **Khách hàng (Customer)** | Người dùng mua vé | - Đặt vé sự kiện (tối thiểu 1 vé, tối đa theo quy định từng loại).<br>- Giữ chỗ 15 phút, thanh toán giả lập và nhận email xác nhận (`OrderPaidMail`) đính kèm vé PDF.<br>- Xem lịch sử đơn hàng, chi tiết đơn hàng, vé điện tử cá nhân (kèm mã QR ký số HMAC-SHA256) và tải vé PDF khổ A5.<br>- Hủy đơn hàng đang ở trạng thái Pending.<br>- Cập nhật thông tin cá nhân. |
| **Ban tổ chức (Organizer)** | Đơn vị tổ chức sự kiện | - Tạo, cập nhật sự kiện của mình (chỉ khi ở trạng thái draft).<br>- Tạo, sửa, xóa các loại vé kèm khu vực chỗ ngồi (`zone`) và mô tả quyền lợi (`description`).<br>- Gửi yêu cầu duyệt sự kiện lên Admin.<br>- Quản lý và xem danh sách đơn hàng thuộc sự kiện của mình.<br>- Soát vé (Check-in) khách tham dự bằng Camera quét mã QR (`html5-qrcode` offline) hoặc nhập tay mã vé; hệ thống xác thực chữ ký HMAC-SHA256 và lưu nhân viên soát vé (`checked_in_by`).<br>- Xem Dashboard thống kê vé đã bán và doanh thu. |
| **Quản trị viên (Admin)** | Người điều hành hệ thống | - Phê duyệt (Approve), Từ chối (Reject), Hủy (Cancel) sự kiện (tự động cascade hủy đơn `pending` hoàn vé và chuyển vé `valid` sang `cancelled`).<br>- Quản lý danh mục (Categories) và địa điểm (Venues) - chặn xóa nếu có ràng buộc dữ liệu.<br>- Quản lý người dùng: Xem, sửa, khóa/kích hoạt tài khoản, thay đổi quyền hạn.<br>- Xem toàn bộ đơn hàng trên hệ thống (kèm thông tin khu vực vé và nhân viên đã soát vé).<br>- Xem Dashboard thống kê tổng thể toàn hệ thống. |

---

### 3. Dữ liệu cần quản lý
1. **Người dùng (Users)**: Tên, Email, Mật khẩu mã hóa, Số điện thoại, Vai trò (admin, organizer, customer), Trạng thái kích hoạt (is_active).
2. **Danh mục sự kiện (Categories)**: Tên danh mục, Đường dẫn thân thiện (Slug duy nhất), Mô tả.
3. **Địa điểm tổ chức (Venues)**: Tên địa điểm, Địa chỉ chi tiết, Tỉnh/Thành phố, Sức chứa tối đa (Capacity).
4. **Sự kiện (Events)**: Người tổ chức (FK), Danh mục (FK), Địa điểm (FK), Tiêu đề, Slug duy nhất, Mô tả chi tiết, Ảnh banner, Thời gian bắt đầu, Thời gian kết thúc, Trạng thái (draft, pending, published, cancelled), Thời gian xóa mềm (soft delete).
5. **Loại vé (Ticket Types)**: Sự kiện (FK), Tên loại vé (VIP, Standard, Early Bird...), Khu vực/Vị trí ngồi (`zone`), Quyền lợi mô tả (`description`), Giá bán (VNĐ), Tổng số lượng phát hành, Số lượng đã bán/đang giữ chỗ, Thời gian mở bán, Thời gian kết thúc bán, Giới hạn tối đa trên 1 đơn hàng (max_per_order).
6. **Đơn hàng (Orders)**: Khách hàng (FK), Mã đơn hàng duy nhất (ORD-XXXXXXXX), Tên khách hàng, Email, Số điện thoại, Tổng tiền (VNĐ), Trạng thái đơn (pending, paid, cancelled), Phương thức thanh toán, Thời điểm thanh toán, Thời điểm hết hạn giữ chỗ (expires_at).
7. **Chi tiết đơn hàng (Order Items)**: Đơn hàng (FK), Loại vé (FK), Số lượng đặt, Đơn giá tại thời điểm mua, Thành tiền.
8. **Vé điện tử (Tickets)**: Chi tiết đơn hàng (FK), Mã vé duy nhất (TKT-XXXXXXXXXX), Chuỗi payload QR (`qr_payload`), Chữ ký số HMAC-SHA256 (`qr_signature`), Trạng thái vé (valid, used, cancelled), Thời điểm check-in (`checked_in_at`), Người soát vé (`checked_in_by` trỏ tới users.id).

---

### 4. Yêu cầu chức năng (Functional Requirements - FR)

| Mã FR | Tên chức năng | Vai trò | Mô tả chi tiết |
|---|---|---|---|
| **FR-01** | Đăng ký tài khoản | Guest | Đăng ký tài khoản mới với vai trò mặc định là Customer. Validate email không trùng, mật khẩu tối thiểu 8 ký tự. |
| **FR-02** | Đăng nhập / Đăng xuất | Guest, User | Xác thực email/password. Áp dụng Rate Limiting (5 lần/phút). Tự động phân hướng về dashboard tương ứng theo vai trò. Chặn đăng nhập với tài khoản bị khóa. |
| **FR-03** | Quản lý hồ sơ cá nhân | User | Cập nhật họ tên, số điện thoại, đổi mật khẩu cá nhân. |
| **FR-04** | Tìm kiếm & Lọc sự kiện | Tất cả | Tìm kiếm theo từ khóa trong tiêu đề; lọc theo Danh mục, Tỉnh/Thành phố, Khoảng thời gian; sắp xếp theo ngày diễn ra hoặc giá vé; phân trang 9 mục/trang. |
| **FR-05** | Xem chi tiết sự kiện | Tất cả | Xem thông tin chi tiết sự kiện, địa điểm, sức chứa, thời gian và danh sách các loại vé còn mở bán. Sự kiện chưa published trả về 404 với khách. |
| **FR-06** | Đặt vé & Giữ chỗ | Customer | Chọn số lượng vé theo từng loại. Validate tồn kho, giới hạn max_per_order. Thực hiện Transaction + Lock For Update. Tăng số lượng `sold` và tạo đơn Pending với thời gian giữ chỗ 15 phút. |
| **FR-07** | Thanh toán giả lập | Customer | Chọn phương thức thanh toán (`bank_transfer`, `momo`, `card`). Trong giao dịch có `lockForUpdate()`, chuyển đơn sang `paid`, ghi nhận `paid_at`, tự động sinh mã vé duy nhất `TKT-XXXXXXXXXX` kèm `qr_payload` JSON và chữ ký số `qr_signature` (HMAC-SHA256); sau khi hoàn tất giao dịch tự động gửi email `OrderPaidMail` đính kèm vé PDF động. |
| **FR-08** | Hủy đơn hàng | Customer | Cho phép khách hàng chủ động hủy đơn Pending. Hoàn trả số lượng `sold` về kho vé và chuyển đơn thành `cancelled`. Đơn `paid` không được phép hủy. |
| **FR-09** | Xử lý đơn hết hạn | Hệ thống / Customer | Tự động hoàn trả vé và chuyển trạng thái cancelled khi đơn pending quá 15 phút (kiểm tra lazy khi xem đơn hoặc qua lệnh artisan `orders:expire-pending`). |
| **FR-10** | Quản lý vé của tôi | Customer | Xem danh sách vé đã mua, hiển thị ảnh mã QR ký số HMAC, mã vé monospace, nút in vé và nút tải vé PDF. |
| **FR-11** | CRUD Sự kiện | Organizer | Tạo mới, xem, sửa sự kiện của mình. Upload ảnh banner sự kiện (kiểm tra kích thước, định dạng). Chỉ được chỉnh sửa sự kiện khi ở trạng thái `draft`. |
| **FR-12** | Quản lý Loại vé | Organizer | Thêm, sửa, xóa loại vé thuộc sự kiện của mình; hỗ trợ nhập khu vực (`zone`) và quyền lợi (`description`). Ràng buộc thời gian bán, số lượng vé không nhỏ hơn số lượng đã bán. |
| **FR-13** | Gửi duyệt sự kiện | Organizer | Chuyển trạng thái sự kiện từ `draft` sang `pending`. Điều kiện: có ít nhất 1 loại vé và thời gian bắt đầu sự kiện phải trong tương lai. |
| **FR-14** | Quản lý đơn hàng sự kiện | Organizer | Xem danh sách các đơn hàng và vé đã mua thuộc sự kiện do mình tổ chức. |
| **FR-15** | Soát vé (Check-in) | Organizer | Soát vé qua 2 phương thức: quét mã QR bằng Camera (webcam/điện thoại qua thư viện `html5-qrcode` offline) hoặc nhập mã vé bằng tay. Thực hiện 6 bước kiểm tra: mã vé tồn tại, chữ ký HMAC hợp lệ, đơn đã paid, thuộc sự kiện của mình, vé chưa hủy, vé chưa từng check-in. Cập nhật `used`, lưu `checked_in_at` và `checked_in_by`. |
| **FR-16** | Dashboard Ban tổ chức | Organizer | Thống kê số lượng sự kiện, tổng số vé đã bán, tổng doanh thu theo sự kiện, tối ưu truy vấn tránh N+1. |
| **FR-17** | Quản lý Danh mục & Địa điểm | Admin | CRUD danh mục và địa điểm tổ chức. Kiểm tra ràng buộc toàn vẹn: Không cho phép xóa danh mục hoặc địa điểm đang có sự kiện liên kết. |
| **FR-18** | Quản lý Người dùng | Admin | Xem danh sách, tìm kiếm, lọc theo vai trò. Khóa/mở khóa tài khoản người dùng (`is_active`). Thay đổi vai trò người dùng (không được tự khóa hoặc hạ quyền chính mình). |
| **FR-19** | Phê duyệt Sự kiện | Admin | Xem chi tiết sự kiện chờ duyệt. Thực hiện Duyệt (`published`), Từ chối (`draft`), hoặc Hủy bỏ (`cancelled`). Khi hủy sự kiện, tự động cascade hủy các đơn `pending` hoàn vé và hủy vé `valid`. |
| **FR-20** | Quản trị Đơn hàng toàn hệ thống | Admin | Xem toàn bộ đơn hàng trong hệ thống, lọc theo trạng thái (pending, paid, cancelled), hiển thị thông tin nhân viên đã soát vé, phân trang. |
| **FR-21** | Dashboard Quản trị viên | Admin | Thống kê tổng số người dùng theo vai trò, sự kiện theo trạng thái, tổng doanh thu toàn sàn từ đơn `paid`, top sự kiện bán chạy nhất. |
| **FR-22** | Xuất vé điện tử PDF động | Customer | Tải vé PDF khổ A5 sinh động trong bộ nhớ RAM qua `barryvdh/laravel-dompdf`, tích hợp ảnh QR code, font DejaVu Sans Unicode tiếng Việt; bảo vệ truy cập qua `OrderPolicy`. |
| **FR-23** | Gửi email xác nhận kèm vé | Hệ thống | Tự động kích hoạt Mailable `OrderPaidMail` gửi email thông báo thanh toán thành công kèm toàn bộ file vé PDF tạo động trong RAM; tích hợp Mailpit và fallback ghi Log. |
| **FR-24** | Quét QR bằng Camera | Organizer | Nhúng khung camera quét mã trực tiếp trên trình duyệt bằng thư viện JS local (`html5-qrcode.min.js`), tự động submit form soát vé không cần thao tác gõ phím. |
| **FR-25** | Cascade Hủy sự kiện | Admin | Trong transaction có khóa `lockForUpdate()`, khi Admin hủy sự kiện sẽ tự động hủy các đơn hàng `pending` (trả lại số lượng `sold` cho các loại vé) và chuyển tất cả vé `valid` sang `cancelled`. |

---

### 5. Yêu cầu phi chức năng (Non-Functional Requirements - NFR)

- **NFR-01 (Hiệu năng & Tối ưu truy vấn)**: Tối ưu truy vấn CSDL với Eager Loading (`with`, `withCount`, `withSum`, `withMin`), ngăn chặn triệt để lỗi N+1 Query. Sử dụng Index hợp lý trên các cột thường xuyên tìm kiếm và lọc (`events(status, start_time)`, `events(category_id)`, `orders(user_id, status)`).
- **NFR-02 (Tính toàn vẹn & Đồng thời)**: Áp dụng cơ chế Khóa bi quan (`DB::table('ticket_types')->lockForUpdate()`) kết hợp `DB::transaction()` khi đặt vé và thanh toán nhằm ngăn chặn xung đột bán vượt quá số lượng vé tồn kho trong môi trường đa luồng.
- **NFR-03 (Bảo mật)**:
  - Chống CSRF trên toàn bộ các biểu mẫu với directive `@csrf`.
  - Chống XSS bằng cơ chế escape tự động của Blade `{{ $variable }}` (không sử dụng `{!! !!}` với dữ liệu người dùng nhập).
  - Bảo vệ chống làm giả mã QR bằng chữ ký số **HMAC-SHA256** (`hash_hmac('sha256', qr_payload, APP_KEY)`) và đối chiếu hằng thời gian (`hash_equals()`).
  - Kiểm soát truy cập chặt chẽ thông qua Middleware `role`, `active` và các Policy (`EventPolicy`, `TicketTypePolicy`, `OrderPolicy` — bảo vệ cả route tải vé PDF `/my-tickets/{ticket}/pdf`, chặn IDOR bằng HTTP 403).
  - Mật khẩu người dùng được băm an toàn bằng thuật toán Bcrypt.
- **NFR-04 (Giao diện & Trải nghiệm người dùng)**: Giao diện xây dựng theo chuẩn Bootstrap 5.3 hiện đại, Responsive đa kích thước màn hình, thông báo Flash Message (thành công, cảnh báo, lỗi) rõ ràng, hiển thị lỗi validation tiếng Việt chi tiết dưới từng trường nhập liệu.
- **NFR-05 (Tính khả dụng, Hạ tầng & Tương thích)**: Sử dụng **MySQL 8.0 Community Server** (engine InnoDB, charset `utf8mb4`, collation `utf8mb4_unicode_ci`) làm CSDL chính để đảm bảo khóa dòng `lockForUpdate()` chuẩn xác, tương thích XAMPP/Laragon/Docker và hỗ trợ SQLite in-memory cho kiểm thử nhanh. Vận hành ổn định khi demo offline: thư viện camera `public/js/html5-qrcode.min.js` lưu cục bộ, PDF tạo động trong RAM qua `barryvdh/laravel-dompdf` (yêu cầu PHP `extension=gd`), email mặc định `MAIL_MAILER=log` (và hỗ trợ Mailpit ở môi trường dev). Tích hợp **GitHub Actions CI** tự động kiểm tra Pint và PHPUnit trên MySQL 8.

---

### 6. Quy trình nghiệp vụ chính (Business Workflows)

#### 6.1. Quy trình Tạo và Phê duyệt sự kiện
```
[Organizer: Tạo Draft] -> [Thêm loại vé (kèm zone, description)] -> [Gửi duyệt] -> (Status: Pending) -> [Admin xem xét]
      │                                                                                                     │
      │                                                                     ┌───────────────────────────────┴───────────────────┐
      │                                                                     ▼                                                   ▼
      │                                                              [Admin Duyệt]                                       [Admin Từ chối]
      │                                                                     │                                                   │
      ▼                                                                     ▼                                                   ▼
(Chỉ sửa khi Draft)                                                (Status: Published)                                    (Status: Draft)
                                                                            │
                                                                            ▼
                                                                   [Admin Hủy sự kiện]
                                                                   (Cascade hủy đơn pending & vé valid)
                                                                            │
                                                                            ▼
                                                                   (Status: Cancelled)
```
1. **Bước 1**: Ban tổ chức (Organizer) đăng nhập, chọn "Tạo sự kiện mới", điền thông tin cơ bản (tiêu đề, danh mục, địa điểm, thời gian, mô tả, ảnh banner). Sự kiện được khởi tạo ở trạng thái `draft`.
2. **Bước 2**: Ban tổ chức thêm ít nhất 01 loại vé (tên loại vé, khu vực chỗ ngồi `zone`, mô tả quyền lợi `description`, số lượng, giá tiền, thời gian mở bán, giới hạn `max_per_order`).
3. **Bước 3**: Khi hoàn tất thông tin và thời gian bắt đầu sự kiện còn ở tương lai, Ban tổ chức bấm "Gửi duyệt". Trạng thái chuyển thành `pending`.
4. **Bước 4**: Quản trị viên (Admin) vào danh sách sự kiện chờ duyệt, xem nội dung:
   - Nếu đạt yêu cầu: Admin bấm "Duyệt" -> Trạng thái chuyển thành `published` và hiển thị trên Storefront.
   - Nếu cần bổ sung: Admin bấm "Từ chối" -> Trạng thái quay về `draft` kèm thông báo để Organizer sửa đổi.
   - Nếu vi phạm hoặc sự cố: Admin bấm "Hủy sự kiện" -> Trạng thái chuyển thành `cancelled`, đồng thời trong transaction tự động cascade hủy các đơn hàng `pending` (hoàn lại `sold`) và chuyển toàn bộ vé `valid` sang `cancelled`.

#### 6.2. Quy trình Đặt vé, Giữ chỗ, Thanh toán & Phát hành vé QR/PDF/Email
```
[Khách hàng xem sự kiện (hiển thị zone & description)] ──► [Chọn số lượng vé] ──► [Bấm Đặt vé]
                                                                                          │
                                                                                          ▼ (DB::transaction + lockForUpdate)
                                                                               [Kiểm tra tồn kho & Max/Order]
                                                                                          │
                                                                       ┌──────────────────┴──────────────────┐
                                                                       ▼ Đủ điều kiện                        ▼ Không đủ
                                                                [Tăng sold loại vé]                    [Báo lỗi tồn kho]
                                                                [Tạo đơn Pending]
                                                                [Giữ chỗ 15 phút]
                                                                       │
                                                        ┌──────────────┴──────────────┐
                                                        ▼                             ▼
                                             [Khách chọn Thanh toán]        [Hết 15p hoặc Khách Hủy]
                                                        │                             │
                                                        ▼ (DB::transaction)           ▼
                                             [Đơn Paid + Sinh Ticket]       [Chuyển trạng thái Cancelled]
                                             [Sinh qr_payload & HMAC]       [Hoàn trả sold về loại vé]
                                                        │
                                                        ▼ (Sau commit)
                                             [Gửi OrderPaidMail + đính kèm PDF trong RAM]
                                                        │
                                                        ▼
                                             [Khách xem QR & tải vé PDF A5 (/my-tickets/{ticket}/pdf)]
```
1. **Bước 1**: Khách hàng chọn sự kiện đang mở bán (`published`, chưa diễn ra, trong khung giờ bán vé, còn vé), xem khu vực (`zone`) và quyền lợi (`description`) của từng hạng vé.
2. **Bước 2**: Khách hàng chọn số lượng vé cho từng loại vé (tối thiểu 1 vé, tối đa theo giới hạn `max_per_order` và số lượng tồn khả dụng) rồi nhấn "Đặt vé".
3. **Bước 3**: Hệ thống mở `DB::transaction()` và thực hiện `lockForUpdate()` trên các bản ghi `ticket_types` tương ứng:
   - Nếu một trong các loại vé không đủ tồn hoặc quá giới hạn: Transaction rollback, ném ngoại lệ thông báo tiếng Việt.
   - Nếu hợp lệ: Tăng trường `sold` của các loại vé, khởi tạo bản ghi `orders` (trạng thái `pending`, mã `ORD-XXXXXXXX`, `expires_at = now + 15 phút`) và các `order_items`.
4. **Bước 4**: Khách hàng được chuyển đến màn hình thanh toán đơn hàng.
   - **Nhánh A (Thanh toán)**: Khách chọn phương thức thanh toán giả lập (Chuyển khoản, MoMo, Thẻ ngân hàng) và bấm "Xác nhận thanh toán". Trong `DB::transaction()` + `lockForUpdate()`, hệ thống kiểm tra đơn còn hiệu lực, cập nhật `status = paid`, ghi nhận `paid_at`, tự động sinh các bản ghi `tickets` với mã độc nhất `TKT-XXXXXXXXXX`, payload QR dạng JSON `{"v":1,"ticket_code":"TKT-..."}` và chữ ký số `qr_signature = hash_hmac('sha256', qr_payload, APP_KEY)`. Sau khi giao dịch hoàn tất, `OrderController@pay` gửi email `OrderPaidMail` (bọc `try/catch` để lỗi mailer không ảnh hưởng đơn đã thanh toán) kèm toàn bộ file vé PDF tạo động trong RAM. Khách hàng có thể xem ảnh QR và tải vé PDF bất cứ lúc nào tại "Chi tiết đơn hàng" và "Vé của tôi".
   - **Nhánh B (Hết hạn hoặc Chủ động Hủy)**: Nếu khách hàng nhấn "Hủy đơn" hoặc quá 15 phút mà chưa thanh toán, trạng thái đơn chuyển thành `cancelled`, số lượng vé đã giữ chỗ được trừ trả lại vào cột `sold` của `ticket_types`.

#### 6.3. Quy trình Soát vé tại cửa (Camera QR & Nhập tay)
```
[Organizer mở trang Check-in]
        │
        ├──► [Bật Camera (html5-qrcode.min.js offline)] ──► Quét JSON qr_payload ──┐
        │                                                                          ├──► [POST /organizer/checkin]
        └──► [Nhập tay mã vé TKT-XXXXXXXXXX (tự viết hoa)] ────────────────────────┘              │
                                                                                                  ▼ (DB::transaction + lockForUpdate)
                    ┌────────────────┬──────────────────┬──────────────┬───────────────┬──────────────────┬──────────────────┐
                    ▼                ▼                  ▼              ▼               ▼                  ▼                  ▼
             Payload lỗi /     Không tồn tại    Sai chữ ký HMAC    Chưa Paid     Sai sự kiện BTC     Vé đã hủy         Vé đã dùng
             rỗng                               (chỉ với QR)
                    │                │                  │              │               │                  │                  │
                    ▼                ▼                  ▼              ▼               ▼                  ▼                  ▼
               [Báo lỗi]        [Báo lỗi]          [Báo lỗi]      [Báo lỗi]       [Báo lỗi]          [Báo lỗi]          [Báo lỗi]
                                                                                                                             │
                                                                                                                             ▼ Hợp lệ 6/6 bước
                                                                                                                 [Cập nhật status: used]
                                                                                                                 [Lưu checked_in_at & checked_in_by]
                                                                                                                 [Hiển thị thẻ kết quả soát vé]
```
1. **Bước 1**: Ban tổ chức truy cập chức năng "Soát vé (Check-in)" tại `/organizer/checkin` (mở qua `http://localhost:8000` hoặc HTTPS để trình duyệt cấp quyền camera).
2. **Bước 2**: Ban tổ chức chọn một trong hai cách nhập liệu:
   - **Quét Camera QR**: Bấm "Bật camera", thư viện `html5-qrcode` cục bộ (`public/js/html5-qrcode.min.js`) giải mã chuỗi JSON `{"v":1,"ticket_code":"TKT-..."}`, tự động dừng camera, điền giá trị và submit form.
   - **Nhập tay dự phòng**: Gõ mã vé (ví dụ: `TKT-ABC123XYZ` hoặc chữ thường `tkt-abc123xyz` sẽ được tự động chuẩn hóa in hoa) và bấm "Xác Nhận Check-in".
3. **Bước 3**: `TicketCheckinService::checkIn()` phân giải đầu vào, mở `DB::transaction()` và khóa dòng vé bằng `lockForUpdate()` rồi thực hiện kiểm tra **6 tầng**:
   - **(1) Tồn tại mã vé**: Không tìm thấy trong DB -> Báo lỗi: `"Mã vé '...' không tồn tại trong hệ thống."`
   - **(2) Xác thực chữ ký số QR (HMAC-SHA256)**: Nếu đầu vào là JSON QR payload, đối chiếu `hash_equals(hash_hmac('sha256', payload, APP_KEY), ticket.qr_signature)`. Nếu sai lệch/bị sửa -> Báo lỗi: `"Chữ ký số của mã QR không hợp lệ. Vé này có thể đã bị chỉnh sửa hoặc làm giả."`
   - **(3) Đơn hàng đã thanh toán**: Nếu đơn chưa `paid` -> Báo lỗi: `"Đơn hàng của vé này (...) chưa hoàn tất thanh toán."`
   - **(4) Quyền sở hữu sự kiện**: Nếu sự kiện không thuộc Organizer đang đăng nhập (và không phải Admin) -> Báo lỗi: `"Vé này không thuộc bất kỳ sự kiện nào do bạn quản lý."`
   - **(5) Vé chưa bị hủy**: Nếu `status == 'cancelled'` -> Báo lỗi: `"Vé này đã bị hủy, không còn giá trị sử dụng."`
   - **(6) Vé chưa từng check-in**: Nếu `status == 'used'` -> Báo lỗi: `"Vé này đã được sử dụng (check-in) vào lúc [H:i - d/m/Y]."`
4. **Bước 4**: Khi vượt qua cả 6 bước, hệ thống cập nhật `status = 'used'`, `checked_in_at = now()`, `checked_in_by = $organizer->id` và flash mảng thông tin tóm tắt (`success_ticket`) để hiển thị thẻ kết quả gồm: mã vé, tên sự kiện, loại vé, khu vực (`zone`), thông tin khách hàng, thời điểm check-in, tên nhân viên soát vé (`checked_in_by`) và huy hiệu xác thực chữ ký HMAC-SHA256. Khi quét QR thất bại, hệ thống không điền ngược chuỗi JSON thô vào ô nhập tay.

#### 6.4. Quy trình Xuất vé PDF động & Gửi Email xác nhận
1. **Sinh mã QR (`QrTicketService`)**: Dùng `endroid/qr-code` mã hóa `qr_payload` (hoặc sinh fallback từ `ticket_code` nếu thiếu) thành ảnh PNG base64 Data URI (khi có `extension=gd`) hoặc SVG Data URI để hiển thị trên Storefront và nhúng trực tiếp vào mẫu vé PDF.
2. **Tạo PDF on-the-fly (`TicketPdfService`)**: Dùng `barryvdh/laravel-dompdf` kết xuất view `resources/views/pdf/ticket.blade.php` (bố cục `<table>` tương thích DomPDF, font `DejaVu Sans` hiển thị chuẩn tiếng Việt Unicode, khổ giấy `A5 portrait`) hoàn toàn trong bộ nhớ RAM (`$pdf->output()`), **không ghi file PDF ra ổ đĩa**.
3. **Bảo vệ quyền tải vé (`GET /my-tickets/{ticket}/pdf`)**: `Storefront\TicketController@downloadPdf` kiểm tra ủy quyền bằng `OrderPolicy::view` (chủ đơn hàng, Ban tổ chức của sự kiện hoặc Admin được phép tải; người dùng khác nhận HTTP `403 Forbidden`), trả về file tải tên `ve-{ticket_code}.pdf`.
4. **Gửi Email tự động (`OrderPaidMail`)**: Sau khi thanh toán thành công tại `OrderController@pay`, hệ thống gửi email dùng template `resources/views/emails/order-paid.blade.php` kèm toàn bộ vé PDF đính kèm trong RAM (`Attachment::fromData`). Ở môi trường phát triển kiểm thử trực quan qua **Mailpit** (`127.0.0.1:1025`), còn cấu hình mặc định trong `.env.example` là `MAIL_MAILER=log` để đảm bảo vận hành 100% offline.

---

### 7. Quy tắc và Ràng buộc nghiệp vụ (Business Rules)
1. **BR-01 (Ràng buộc định danh, mã số & chữ ký vé)**:
   - Email người dùng phải là duy nhất trên toàn hệ thống.
   - Slug sự kiện và slug danh mục phải là duy nhất, được tự động sinh từ tiêu đề/tên và loại bỏ trùng lặp.
   - Mã đơn hàng (`order_code`) theo định dạng `ORD-` + 8 ký tự in hoa ngẫu nhiên, đảm bảo duy nhất (tự động thử lại nếu trùng lặp).
   - Mã vé (`ticket_code`) theo định dạng `TKT-` + 10 ký tự in hoa ngẫu nhiên, duy nhất trên toàn hệ thống; mỗi vé được gắn chuỗi `qr_payload` dạng JSON `{"v":1,"ticket_code":"TKT-..."}` và chữ ký `qr_signature` dài 64 ký tự hex (HMAC-SHA256 ký bằng `APP_KEY`).
   - File vé PDF xuất ra theo tên chuẩn `ve-{ticket_code}.pdf`, sinh động trong RAM, không lưu trữ vật lý trên đĩa.
2. **BR-02 (Ràng buộc thời gian)**:
   - Sự kiện chỉ được gửi duyệt khi thời gian bắt đầu (`start_time`) lớn hơn thời gian hiện tại và thời gian kết thúc (`end_time`) lớn hơn thời gian bắt đầu.
   - Thời gian mở bán vé (`sale_start`) phải nhỏ hơn thời gian đóng bán vé (`sale_end`).
   - Thời hạn giữ chỗ đơn hàng `pending` được cố định là 15 phút.
3. **BR-03 (Ràng buộc xóa và hủy dữ liệu)**:
   - Danh mục hoặc Địa điểm đang chứa sự kiện thì tuyệt đối không được phép xóa (phải yêu cầu chuyển đổi hoặc xóa sự kiện trước).
   - Sự kiện đã phát sinh đơn hàng đã thanh toán (`paid`) thì không được xóa vật lý mà chuyển sang hủy (`cancelled`), hỗ trợ xóa mềm (Soft Delete); khi Admin hủy sự kiện phải tự động cascade hủy đơn `pending` (trả lại `sold`) và hủy toàn bộ vé `valid`.
   - Admin không thể tự khóa tài khoản hoặc tự hạ quyền quản trị của chính mình.
4. **BR-04 (Ràng buộc tiền tệ và ngôn ngữ)**:
   - Đơn vị tiền tệ chính thức là Việt Nam Đồng (VNĐ), hiển thị có dấu phân cách hàng nghìn và hậu tố "đ".
   - Múi giờ hệ thống mặc định là `Asia/Ho_Chi_Minh`. Toàn bộ thông báo lỗi và giao diện hiển thị bằng tiếng Việt chuẩn.
5. **BR-05 (Ràng buộc soát vé QR & mô tả hạng vé)**:
   - Mọi thao tác soát vé đều chạy trong `DB::transaction()` + `lockForUpdate()` trên bản ghi vé nhằm chống check-in đồng thời hai lần; khi thành công bắt buộc lưu `checked_in_at` và `checked_in_by` (`FK users`, `nullOnDelete`).
   - Đầu vào dạng JSON bắt buộc vượt qua bước kiểm tra chữ ký số `qr_signature` trước khi cho phép check-in.
   - Các trường mở rộng của loại vé gồm `zone` (tối đa 100 ký tự, tùy chọn) và `description` (tối đa 1.000 ký tự, tùy chọn) hiển thị thống nhất trên Organizer, Storefront, Admin và vé PDF.

---

### 8. Giả định và Đánh giá rủi ro (Assumptions & Risks)

| Rủi ro tiềm ẩn | Mức độ | Biện pháp giảm thiểu |
|---|---|---|
| Bán vượt số lượng vé (Overbooking) khi nhiều người dùng cùng đặt vé cùng một giây | Cao | Sử dụng `DB::transaction()` kết hợp `lockForUpdate()` trên hàng `ticket_types` (InnoDB MySQL 8), tính toán `sold` ngay khi giữ chỗ. |
| Người dùng giữ chỗ ảo gây hết vé nhưng không thanh toán | Trung bình | Đặt giới hạn 15 phút cho đơn hàng `pending`. Tự động thu hồi vé thông qua lazy check và schedule command `orders:expire-pending`. |
| Làm giả hoặc chỉnh sửa nội dung mã QR để qua mặt cổng soát vé | Cao | Ký `qr_payload` bằng thuật toán HMAC-SHA256 dùng `APP_KEY` và kiểm tra bằng `hash_equals()` trong `TicketCheckinService`. |
| Quét trùng cùng một vé đồng thời từ nhiều thiết bị check-in | Cao | Khóa bi quan (`lockForUpdate()`) trên bảng `tickets` trong `DB::transaction()`, chỉ cho phép duy nhất 1 yêu cầu chuyển trạng thái `valid -> used`. |
| Lộ lọt thông tin đơn hàng hoặc tải trộm vé PDF của khách hàng khác (IDOR) | Cao | Áp dụng `OrderPolicy` cho cả màn hình đơn hàng và route `/my-tickets/{ticket}/pdf` (trả về HTTP 403 Forbidden). |
| Ban tổ chức soát nhầm vé của sự kiện khác | Trung bình | Kiểm tra chặt chẽ `organizer_id` của sự kiện chứa loại vé đối với tài khoản đang đăng nhập và lưu vết `checked_in_by`. |
| Mất kết nối Internet hoặc không có máy chủ SMTP khi bảo vệ đồ án | Trung bình | Tích hợp sẵn `public/js/html5-qrcode.min.js` chạy offline, DomPDF sinh vé trong RAM, `.env.example` mặc định `MAIL_MAILER=log` (bọc `try/catch` khi gửi mail) và hỗ trợ Mailpit nội bộ. |
| Khác biệt môi trường CSDL giữa các máy thành viên / máy chấm bài | Thấp | Chuẩn hóa MySQL 8.0 (`utf8mb4_unicode_ci`) trên cả máy local và GitHub Actions CI, đồng thời vẫn tương thích XAMPP/Laragon và SQLite in-memory cho PHPUnit. |

---

### 9. Bảng truy vết yêu cầu (Traceability Matrix)

| Mã FR | Tên chức năng | Route Name | Controller & Action | View tương ứng |
|---|---|---|---|---|
| FR-01 | Đăng ký | `register.store` | `AuthController@register` | `resources/views/auth/register.blade.php` |
| FR-02 | Đăng nhập/Đăng xuất | `login.store`, `logout` | `AuthController@login`, `logout` | `resources/views/auth/login.blade.php` |
| FR-03 | Hồ sơ cá nhân | `profile.edit`, `profile.update`| `ProfileController@edit`, `update`| `resources/views/profile/edit.blade.php` |
| FR-04 | Tìm kiếm, lọc sự kiện | `home` | `StorefrontController@index` | `resources/views/storefront/index.blade.php` |
| FR-05 | Chi tiết sự kiện | `events.show` | `StorefrontController@show` | `resources/views/storefront/show.blade.php` |
| FR-06 | Đặt vé & Giữ chỗ | `checkout.store` | `OrderController@checkout` | `resources/views/storefront/orders/show.blade.php` |
| FR-07 | Thanh toán giả lập | `orders.pay` | `OrderController@pay` | `resources/views/storefront/orders/show.blade.php` |
| FR-08 | Hủy đơn hàng | `orders.cancel` | `OrderController@cancel` | `resources/views/storefront/orders/index.blade.php` |
| FR-09 | Xử lý đơn hết hạn | CLI / Lazy check | `OrderService::expireAllPendingOrders` | Console / Background |
| FR-10 | Danh sách vé của tôi | `tickets.index` | `TicketController@index` | `resources/views/storefront/tickets/index.blade.php` |
| FR-11 | Quản lý sự kiện | `organizer.events.*` | `Organizer\EventController` | `resources/views/organizer/events/*` |
| FR-12 | Quản lý loại vé | `organizer.events.ticket-types.*`| `Organizer\TicketTypeController` | `resources/views/organizer/ticket-types/*` |
| FR-13 | Gửi duyệt sự kiện | `organizer.events.submit` | `Organizer\EventController@submit` | `resources/views/organizer/events/index.blade.php` |
| FR-14 | Đơn hàng của Organizer| `organizer.orders.*` | `Organizer\OrderController` | `resources/views/organizer/orders/*` |
| FR-15 | Soát vé (Check-in) | `organizer.checkin.*` | `Organizer\CheckinController` | `resources/views/organizer/checkin/form.blade.php` |
| FR-16 | Dashboard Organizer | `organizer.dashboard` | `Organizer\DashboardController@index`| `resources/views/organizer/dashboard.blade.php` |
| FR-17 | Quản lý Category/Venue| `admin.categories.*`, `venues.*` | `Admin\CategoryController`, `VenueController` | `resources/views/admin/categories/*`, `venues/*` |
| FR-18 | Quản lý Người dùng | `admin.users.*`, `users.toggle` | `Admin\UserController` | `resources/views/admin/users/*` |
| FR-19 | Phê duyệt sự kiện | `admin.events.approve/reject/cancel` | `Admin\EventController` | `resources/views/admin/events/*` |
| FR-20 | Quản lý mọi đơn hàng | `admin.orders.*` | `Admin\OrderController` | `resources/views/admin/orders/*` |
| FR-21 | Dashboard Admin | `admin.dashboard` | `Admin\DashboardController@index` | `resources/views/admin/dashboard.blade.php` |
| FR-22 | Xuất vé PDF | `tickets.pdf` | `Storefront\TicketController@downloadPdf` | `resources/views/pdf/ticket.blade.php` |
| FR-23 | Email vé & đơn hàng | Mailable | `OrderPaidMail` (gọi tại `OrderController@pay`) | `resources/views/emails/order-paid.blade.php` |
| FR-24 | Soát vé Camera QR | `organizer.checkin.*` | `Organizer\CheckinController` + `TicketCheckinService` | `resources/views/organizer/checkin/form.blade.php` |
| FR-25 | Cascade Hủy sự kiện | `admin.events.cancel` | `Admin\EventController@cancel` | `resources/views/admin/events/show.blade.php` |
