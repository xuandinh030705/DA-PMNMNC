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
  - Thanh toán giả lập (Mock Payment) qua Chuyển khoản ngân hàng, Ví MoMo, Thẻ quốc tế/nội địa; tự động kích hoạt vé và sinh mã vé duy nhất.
  - Hủy đơn và xử lý hoàn trả số lượng vé khi đơn hết hạn (Lazy check hoặc Schedule Command).
  - Quản lý sự kiện: Tạo nháp (draft), gửi duyệt (pending), phê duyệt/từ chối/hủy sự kiện; quản lý loại vé.
  - Soát vé (Check-in) theo mã vé độc nhất, kiểm tra tính hợp lệ và cập nhật thời gian soát vé.
  - Bảng điều khiển thống kê (Dashboard) trực quan cho Organizer và Admin.
- **Ngoài phạm vi (Out-of-Scope)**:
  - Tích hợp cổng thanh toán thực tế với ngân hàng (chỉ mô phỏng quy trình thanh toán).
  - Quét mã QR bằng Camera phần cứng (hệ thống cung cấp mã vé monospace chuẩn hoá để nhập soát vé và in thẻ vé).

---

### 2. Đối tượng sử dụng và Ma trận phân quyền (Actors & Roles)

| Vai trò (Role) | Mô tả | Quyền hạn chính |
|---|---|---|
| **Khách vãng lai (Guest)** | Người dùng chưa đăng nhập | - Xem danh sách sự kiện, tìm kiếm, lọc, sắp xếp.<br>- Xem chi tiết sự kiện và các loại vé.<br>- Đăng ký tài khoản, Đăng nhập. |
| **Khách hàng (Customer)** | Người dùng mua vé | - Đặt vé sự kiện (tối thiểu 1 vé, tối đa theo quy định từng loại).<br>- Giữ chỗ 15 phút, thanh toán giả lập.<br>- Xem lịch sử đơn hàng, chi tiết đơn hàng, vé điện tử cá nhân.<br>- Hủy đơn hàng đang ở trạng thái Pending.<br>- Cập nhật thông tin cá nhân. |
| **Ban tổ chức (Organizer)** | Đơn vị tổ chức sự kiện | - Tạo, cập nhật sự kiện của mình (chỉ khi ở trạng thái draft).<br>- Tạo, sửa, xóa các loại vé cho sự kiện.<br>- Gửi yêu cầu duyệt sự kiện lên Admin.<br>- Quản lý và xem danh sách đơn hàng thuộc sự kiện của mình.<br>- Soát vé (Check-in) khách tham dự bằng mã vé.<br>- Xem Dashboard thống kê vé đã bán và doanh thu. |
| **Quản trị viên (Admin)** | Người điều hành hệ thống | - Phê duyệt (Approve), Từ chối (Reject), Hủy (Cancel) sự kiện.<br>- Quản lý danh mục (Categories) và địa điểm (Venues) - chặn xóa nếu có ràng buộc dữ liệu.<br>- Quản lý người dùng: Xem, sửa, khóa/kích hoạt tài khoản, thay đổi quyền hạn.<br>- Xem toàn bộ đơn hàng trên hệ thống.<br>- Xem Dashboard thống kê tổng thể toàn hệ thống. |

---

### 3. Dữ liệu cần quản lý
1. **Người dùng (Users)**: Tên, Email, Mật khẩu mã hóa, Số điện thoại, Vai trò (admin, organizer, customer), Trạng thái kích hoạt (is_active).
2. **Danh mục sự kiện (Categories)**: Tên danh mục, Đường dẫn thân thiện (Slug duy nhất), Mô tả.
3. **Địa điểm tổ chức (Venues)**: Tên địa điểm, Địa chỉ chi tiết, Tỉnh/Thành phố, Sức chứa tối đa (Capacity).
4. **Sự kiện (Events)**: Người tổ chức (FK), Danh mục (FK), Địa điểm (FK), Tiêu đề, Slug duy nhất, Mô tả chi tiết, Ảnh banner, Thời gian bắt đầu, Thời gian kết thúc, Trạng thái (draft, pending, published, cancelled), Thời gian xóa mềm (soft delete).
5. **Loại vé (Ticket Types)**: Sự kiện (FK), Tên loại vé (VIP, Standard, Early Bird...), Giá bán (VNĐ), Tổng số lượng phát hành, Số lượng đã bán/đang giữ chỗ, Thời gian mở bán, Thời gian kết thúc bán, Giới hạn tối đa trên 1 đơn hàng (max_per_order).
6. **Đơn hàng (Orders)**: Khách hàng (FK), Mã đơn hàng duy nhất (ORD-XXXXXXXX), Tên khách hàng, Email, Số điện thoại, Tổng tiền (VNĐ), Trạng thái đơn (pending, paid, cancelled), Phương thức thanh toán, Thời điểm thanh toán, Thời điểm hết hạn giữ chỗ (expires_at).
7. **Chi tiết đơn hàng (Order Items)**: Đơn hàng (FK), Loại vé (FK), Số lượng đặt, Đơn giá tại thời điểm mua, Thành tiền.
8. **Vé điện tử (Tickets)**: Chi tiết đơn hàng (FK), Mã vé duy nhất (TKT-XXXXXXXXXX), Trạng thái vé (valid, used, cancelled), Thời điểm check-in (checked_in_at).

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
| **FR-07** | Thanh toán giả lập | Customer | Chọn phương thức thanh toán (bank_transfer, momo, card). Chuyển đơn sang `paid`, ghi nhận `paid_at`, tự động sinh mã vé duy nhất cho từng vé trong đơn. |
| **FR-08** | Hủy đơn hàng | Customer | Cho phép khách hàng chủ động hủy đơn Pending. Hoàn trả số lượng `sold` về kho vé và chuyển đơn thành `cancelled`. Đơn `paid` không được phép hủy. |
| **FR-09** | Xử lý đơn hết hạn | Hệ thống / Customer | Tự động hoàn trả vé và chuyển trạng thái cancelled khi đơn pending quá 15 phút (kiểm tra lazy khi xem đơn hoặc qua lệnh artisan `orders:expire-pending`). |
| **FR-10** | Quản lý vé của tôi | Customer | Xem danh sách vé đã mua, xem chi tiết từng vé có mã vé monospace và nút in thẻ vé. |
| **FR-11** | CRUD Sự kiện | Organizer | Tạo mới, xem, sửa sự kiện của mình. Upload ảnh banner sự kiện (kiểm tra kích thước, định dạng). Chỉ được chỉnh sửa sự kiện khi ở trạng thái `draft`. |
| **FR-12** | Quản lý Loại vé | Organizer | Thêm, sửa, xóa loại vé thuộc sự kiện của mình. Ràng buộc thời gian bán, số lượng vé không nhỏ hơn số lượng đã bán. |
| **FR-13** | Gửi duyệt sự kiện | Organizer | Chuyển trạng thái sự kiện từ `draft` sang `pending`. Điều kiện: có ít nhất 1 loại vé và thời gian bắt đầu sự kiện phải trong tương lai. |
| **FR-14** | Quản lý đơn hàng sự kiện | Organizer | Xem danh sách các đơn hàng và vé đã mua thuộc sự kiện do mình tổ chức. |
| **FR-15** | Soát vé (Check-in) | Organizer | Nhập mã vé để kiểm tra tính hợp lệ. Xử lý các tình huống: vé không tồn tại, đơn chưa thanh toán, vé đã dùng, vé bị hủy, vé không thuộc sự kiện của organizer. Cập nhật `used` và ghi nhận `checked_in_at`. |
| **FR-16** | Dashboard Ban tổ chức | Organizer | Thống kê số lượng sự kiện, tổng số vé đã bán, tổng doanh thu theo sự kiện, tối ưu truy vấn tránh N+1. |
| **FR-17** | Quản lý Danh mục & Địa điểm | Admin | CRUD danh mục và địa điểm tổ chức. Kiểm tra ràng buộc toàn vẹn: Không cho phép xóa danh mục hoặc địa điểm đang có sự kiện liên kết. |
| **FR-18** | Quản lý Người dùng | Admin | Xem danh sách, tìm kiếm, lọc theo vai trò. Khóa/mở khóa tài khoản người dùng (`is_active`). Thay đổi vai trò người dùng (không được tự khóa hoặc hạ quyền chính mình). |
| **FR-19** | Phê duyệt Sự kiện | Admin | Xem chi tiết sự kiện chờ duyệt. Thực hiện Duyệt (`published`), Từ chối (`draft`), hoặc Hủy bỏ (`cancelled`). |
| **FR-20** | Quản trị Đơn hàng toàn hệ thống | Admin | Xem toàn bộ đơn hàng trong hệ thống, lọc theo trạng thái (pending, paid, cancelled), phân trang. |
| **FR-21** | Dashboard Quản trị viên | Admin | Thống kê tổng số người dùng theo vai trò, sự kiện theo trạng thái, tổng doanh thu toàn sàn từ đơn `paid`, top sự kiện bán chạy nhất. |

---

### 5. Yêu cầu phi chức năng (Non-Functional Requirements - NFR)

- **NFR-01 (Hiệu năng & Tối ưu truy vấn)**: Tối ưu truy vấn CSDL với Eager Loading (`with`, `withCount`, `withSum`, `withMin`), ngăn chặn triệt để lỗi N+1 Query. Sử dụng Index hợp lý trên các cột thường xuyên tìm kiếm và lọc (`events(status, start_time)`, `events(category_id)`, `orders(user_id, status)`).
- **NFR-02 (Tính toàn vẹn & Đồng thời)**: Áp dụng cơ chế Khóa bi quan (`DB::table('ticket_types')->lockForUpdate()`) kết hợp `DB::transaction()` khi đặt vé và thanh toán nhằm ngăn chặn xung đột bán vượt quá số lượng vé tồn kho trong môi trường đa luồng.
- **NFR-03 (Bảo mật)**:
  - Chống CSRF trên toàn bộ các biểu mẫu với directive `@csrf`.
  - Chống XSS bằng cơ chế escape tự động của Blade `{{ $variable }}` (không sử dụng `{!! !!}` với dữ liệu người dùng nhập).
  - Kiểm soát truy cập chặt chẽ thông qua Middleware `role`, `active` và các Policy (`EventPolicy`, `TicketTypePolicy`, `OrderPolicy`).
  - Mật khẩu người dùng được băm an toàn bằng thuật toán Bcrypt.
- **NFR-04 (Giao diện & Trải nghiệm người dùng)**: Giao diện xây dựng theo chuẩn Bootstrap 5.3 hiện đại, Responsive đa kích thước màn hình, thông báo Flash Message (thành công, cảnh báo, lỗi) rõ ràng, hiển thị lỗi validation tiếng Việt chi tiết dưới từng trường nhập liệu.
- **NFR-05 (Tính khả dụng & Tương thích)**: Mặc định chạy ngay trên SQLite không cần cài đặt thêm server DB phức tạp; sẵn sàng chuyển đổi cấu hình sang MySQL một cách dễ dàng thông qua `.env`.

---

### 6. Quy trình nghiệp vụ chính (Business Workflows)

#### 6.1. Quy trình Tạo và Phê duyệt sự kiện
```
[Organizer: Tạo Draft] -> [Thêm loại vé] -> [Gửi duyệt] -> (Status: Pending) -> [Admin xem xét]
      │                                                                         │
      │                                                     ┌───────────────────┴───────────────────┐
      │                                                     ▼                                       ▼
      │                                              [Admin Duyệt]                           [Admin Từ chối]
      │                                                     │                                       │
      ▼                                                     ▼                                       ▼
(Chỉ sửa khi Draft)                                (Status: Published)                      (Status: Draft)
                                                            │
                                                            ▼
                                                   [Admin Hủy sự kiện]
                                                            │
                                                            ▼
                                                   (Status: Cancelled)
```
1. **Bước 1**: Ban tổ chức (Organizer) đăng nhập, chọn "Tạo sự kiện mới", điền thông tin cơ bản (tiêu đề, danh mục, địa điểm, thời gian, mô tả, ảnh banner). Sự kiện được khởi tạo ở trạng thái `draft`.
2. **Bước 2**: Ban tổ chức thêm ít nhất 01 loại vé (tên loại vé, số lượng, giá tiền, thời gian mở bán).
3. **Bước 3**: Khi hoàn tất thông tin và thời gian bắt đầu sự kiện còn ở tương lai, Ban tổ chức bấm "Gửi duyệt". Trạng thái chuyển thành `pending`.
4. **Bước 4**: Quản trị viên (Admin) vào danh sách sự kiện chờ duyệt, xem nội dung:
   - Nếu đạt yêu cầu: Admin bấm "Duyệt" -> Trạng thái chuyển thành `published` và hiển thị trên Storefront.
   - Nếu cần bổ sung: Admin bấm "Từ chối" -> Trạng thái quay về `draft` kèm thông báo để Organizer sửa đổi.
   - Nếu vi phạm nghiêm trọng: Admin bấm "Hủy sự kiện" -> Trạng thái chuyển thành `cancelled`.

#### 6.2. Quy trình Đặt vé, Giữ chỗ và Thanh toán
```
[Khách hàng xem sự kiện] ──► [Chọn số lượng vé] ──► [Bấm Đặt vé]
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
                         ▼                             ▼
              [Chuyển trạng thái Paid]      [Chuyển trạng thái Cancelled]
              [Tạo mã vé điện tử TKT-...]    [Hoàn trả sold về loại vé]
              [Hoàn tất đơn hàng]
```
1. **Bước 1**: Khách hàng chọn sự kiện đang mở bán (`published`, chưa diễn ra, trong khung giờ bán vé, còn vé).
2. **Bước 2**: Khách hàng chọn số lượng vé cho từng loại vé (tối thiểu 1 vé, tối đa theo giới hạn `max_per_order` và số lượng tồn khả dụng) rồi nhấn "Đặt vé".
3. **Bước 3**: Hệ thống mở `DB::transaction()` và thực hiện `lockForUpdate()` trên các bản ghi `ticket_types` tương ứng:
   - Nếu một trong các loại vé không đủ tồn hoặc quá giới hạn: Transaction rollback, ném ngoại lệ thông báo tiếng Việt.
   - Nếu hợp lệ: Tăng trường `sold` của các loại vé, khởi tạo bản ghi `orders` (trạng thái `pending`, mã `ORD-XXXXXXXX`, `expires_at = now + 15 phút`) và các `order_items`.
4. **Bước 4**: Khách hàng được chuyển đến màn hình thanh toán đơn hàng.
   - **Nhánh A (Thanh toán)**: Khách chọn phương thức thanh toán giả lập (Chuyển khoản, MoMo, Thẻ ngân hàng) và bấm "Xác nhận thanh toán". Hệ thống kiểm tra đơn còn hiệu lực, cập nhật `status = paid`, ghi nhận `paid_at`, tự động sinh các bản ghi `tickets` với mã độc nhất `TKT-XXXXXXXXXX`.
   - **Nhánh B (Hết hạn hoặc Chủ động Hủy)**: Nếu khách hàng nhấn "Hủy đơn" hoặc quá 15 phút mà chưa thanh toán, trạng thái đơn chuyển thành `cancelled`, số lượng vé đã giữ chỗ được trừ trả lại vào cột `sold` của `ticket_types`.

#### 6.3. Quy trình Soát vé tại cửa (Check-in)
```
[Organizer mở trang Check-in] ──► [Nhập mã vé TKT-XXXXXXXXXX] ──► [Gửi kiểm tra]
                                                                        │
                        ┌────────────────┬───────────────┬──────────────┼────────────────┐
                        ▼                ▼               ▼              ▼                ▼
                  Không tồn tại     Chưa Paid       Đã check-in     Đã bị hủy       Sai sự kiện
                        │                │               │              │                │
                        ▼                ▼               ▼              ▼                ▼
                   [Báo lỗi]        [Báo lỗi]       [Báo lỗi]      [Báo lỗi]        [Báo lỗi]
                                                                                         │
                                                                                         ▼ Hợp lệ
                                                                                [Cập nhật status: used]
                                                                                [Lưu checked_in_at]
                                                                                [Báo thành công & thông tin]
```
1. **Bước 1**: Ban tổ chức truy cập chức năng "Soát vé (Check-in)" trên thanh điều hướng Organizer.
2. **Bước 2**: Nhập mã vé (ví dụ: `TKT-ABC123XYZ`) và bấm "Kiểm tra vé".
3. **Bước 3**: Hệ thống thực hiện kiểm tra đa tầng:
   - Vé không tồn tại trên hệ thống -> Báo lỗi: "Mã vé không tồn tại trong hệ thống."
   - Đơn hàng của vé chưa thanh toán -> Báo lỗi: "Đơn hàng chứa vé này chưa hoàn tất thanh toán."
   - Vé đã được sử dụng trước đó (`status == 'used'`) -> Báo lỗi: "Vé đã được sử dụng vào lúc [giờ:phút ngày/tháng/năm]."
   - Vé đã bị hủy (`status == 'cancelled'`) -> Báo lỗi: "Vé này đã bị hủy, không có hiệu lực."
   - Vé không thuộc về sự kiện do Ban tổ chức này quản lý -> Báo lỗi: "Vé này không thuộc bất kỳ sự kiện nào do bạn quản lý."
4. **Bước 4**: Khi vé hoàn toàn hợp lệ, cập nhật `status = 'used'`, `checked_in_at = now()`. Hiển thị thông báo thành công kèm loại vé, tên sự kiện, và thông tin khách tham dự.

---

### 7. Quy tắc và Ràng buộc nghiệp vụ (Business Rules)
1. **BR-01 (Ràng buộc định danh và mã số)**:
   - Email người dùng phải là duy nhất trên toàn hệ thống.
   - Slug sự kiện và slug danh mục phải là duy nhất, được tự động sinh từ tiêu đề/tên và loại bỏ trùng lặp.
   - Mã đơn hàng (`order_code`) theo định dạng `ORD-` + 8 ký tự in hoa ngẫu nhiên, đảm bảo duy nhất (tự động thử lại nếu trùng lặp).
   - Mã vé (`ticket_code`) theo định dạng `TKT-` + 10 ký tự in hoa ngẫu nhiên, duy nhất trên toàn hệ thống.
2. **BR-02 (Ràng buộc thời gian)**:
   - Sự kiện chỉ được gửi duyệt khi thời gian bắt đầu (`start_time`) lớn hơn thời gian hiện tại và thời gian kết thúc (`end_time`) lớn hơn thời gian bắt đầu.
   - Thời gian mở bán vé (`sale_start`) phải nhỏ hơn thời gian đóng bán vé (`sale_end`).
   - Thời hạn giữ chỗ đơn hàng `pending` được cố định là 15 phút.
3. **BR-03 (Ràng buộc xóa dữ liệu)**:
   - Danh mục hoặc Địa điểm đang chứa sự kiện thì tuyệt đối không được phép xóa (phải yêu cầu chuyển đổi hoặc xóa sự kiện trước).
   - Sự kiện đã phát sinh đơn hàng đã thanh toán (`paid`) thì không được xóa vật lý mà chuyển sang hủy (`cancelled`), hỗ trợ xóa mềm (Soft Delete).
   - Admin không thể tự khóa tài khoản hoặc tự hạ quyền quản trị của chính mình.
4. **BR-04 (Ràng buộc tiền tệ và ngôn ngữ)**:
   - Đơn vị tiền tệ chính thức là Việt Nam Đồng (VNĐ), hiển thị có dấu phân cách hàng nghìn và hậu tố "đ".
   - Múi giờ hệ thống mặc định là `Asia/Ho_Chi_Minh`. Toàn bộ thông báo lỗi và giao diện hiển thị bằng tiếng Việt chuẩn.

---

### 8. Giả định và Đánh giá rủi ro (Assumptions & Risks)

| Rủi ro tiềm ẩn | Mức độ | Biện pháp giảm thiểu |
|---|---|---|
| Bán vượt số lượng vé (Overbooking) khi nhiều người dùng cùng đặt vé cùng một giây | Cao | Sử dụng Transaction kết hợp `lockForUpdate()` trên hàng loại vé, tính toán `sold` ngay khi giữ chỗ. |
| Người dùng giữ chỗ ảo gây hết vé nhưng không thanh toán | Trung bình | Đặt giới hạn 15 phút cho đơn hàng `pending`. Tự động thu hồi vé thông qua lazy check và schedule command. |
| Lộ lọt thông tin đơn hàng giữa các khách hàng | Cao | Áp dụng `OrderPolicy` chặn quyền truy cập chéo đơn hàng của người khác (trả về lỗi 403 Forbidden). |
| Ban tổ chức soát nhầm vé của sự kiện khác | Trung bình | Kiểm tra chặt chẽ `organizer_id` của sự kiện chứa loại vé đối với tài khoản đang đăng nhập. |
| Gián đoạn hoặc thiếu sót môi trường cơ sở dữ liệu | Thấp | Mặc định tích hợp sẵn SQLite chạy ngay lập tức không phụ thuộc dịch vụ ngoài; cung cấp hướng dẫn rõ ràng để chuyển sang MySQL. |

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
| FR-09 | Xử lý đơn hết hạn | CLI / Lazy check | `OrderService::expirePendingOrders` | Console / Background |
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
