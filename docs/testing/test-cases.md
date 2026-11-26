# BẢNG KẾT QUẢ KIỂM THỬ TỰ ĐỘNG & TEST CASES
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

---

### 1. Tổng quan kết quả kiểm thử (Test Execution Summary)
- **Công cụ kiểm thử**: PHPUnit 12.5.40 & Laravel Testing Suite.
- **Tổng số ca kiểm thử tự động**: **103 tests** (15 Unit tests + 88 Feature tests).
- **Số test Passed**: **103 / 103 (100%)**.
- **Số test Failed**: 0.
- **Tổng số assertions**: **411 assertions**.
- **Môi trường cơ sở dữ liệu test**: SQLite in-memory (`:memory:`), `RefreshDatabase` (CI chạy đồng thời trên MySQL 8.0).
- **File nhật ký kết quả chi tiết**: [`docs/testing/test-output.txt`](./test-output.txt).

---

### 2. Bảng đặc tả Unit Tests (15 Tests)

| Mã Test | Nhóm / File | Tên Test Case | Dữ liệu đầu vào | Kết quả mong đợi | Kết quả thực tế | Trạng thái |
|---|---|---|---|---|---|---|
| **UT-01** | DomainModels | Kiểm tra accessor `remaining` của `TicketType` | `(quantity=10, sold=4)` và `(quantity=5, sold=7)` | Trả về `6` và không bao giờ âm (`0`) | Trả về đúng `6` và `0` | **PASSED** |
| **UT-02** | DomainModels | Kiểm tra helper vai trò `User` và đồng bộ Enum | `User` với vai trò từ `UserRole` và hằng số trạng thái | `isAdmin()`, `isOrganizer()`, `isCustomer()` chính xác | Toàn bộ assertions khớp 100% | **PASSED** |
| **UT-03** | QrTicketService | Payload sinh tự động từ mã vé khi thiếu | Vé có `ticket_code = 'TKT-ABCDEFGHIJ'` | Sinh JSON `{"v":1,"ticket_code":"TKT-ABCDEFGHIJ"}` | Chuỗi JSON chuẩn xác | **PASSED** |
| **UT-04** | QrTicketService | Tạo ảnh PNG Data URI từ mã vé | Vé điện tử hợp lệ | Trả về chuỗi `data:image/png;base64,...` hợp lệ | Ảnh PNG base64 hợp lệ | **PASSED** |
| **UT-05** | QrTicketService | Xuất chuỗi SVG markup cho vé | Vé điện tử hợp lệ | Chuỗi chứa thẻ `<svg ...>` | Chứa thẻ SVG hợp lệ | **PASSED** |
| **UT-06** | QrTicketService | Chữ ký số HMAC-SHA256 hợp lệ cho payload | Payload nguyên bản, ký bằng `APP_KEY` | `verifySignature()` trả về `true` | Trả về `true` | **PASSED** |
| **UT-07** | QrTicketService | Chữ ký số bị từ chối khi payload bị sửa | Payload bị thay đổi mã vé | `verifySignature()` trả về `false` | Trả về `false` | **PASSED** |
| **UT-08** | QrTicketService | Chữ ký số bị từ chối khi chuỗi rỗng | Payload rỗng hoặc chữ ký rỗng | `verifySignature()` trả về `false` | Trả về `false` | **PASSED** |
| **UT-09** | QrTicketService | Helper chữ ký số Model khớp Service | `$ticket->hasValidQrSignature()` | Trả về kết quả đồng nhất với Service | Khớp assertions | **PASSED** |
| **UT-10** | QrTicketService | Hàm giải mã payload từ chối mã vé chữ thường / lỗi | Chuỗi `TKT-ABCDEFGHIJ`, `{"v":1}`, `not-json` | Trả về `null` | Trả về `null` | **PASSED** |
| **UT-11** | QrTicketService | Ưu tiên sử dụng `qr_payload` đã lưu sẵn | Vé có sẵn `qr_payload` khác mã mặc định | `payloadFor()` trả đúng `qr_payload` đã lưu | Khớp chuỗi đã lưu | **PASSED** |
| **UT-12** | QrTicketService | Kiểm tra định dạng PNG nhị phân & SVG Data URI | Vé điện tử hợp lệ | PNG có header `\x89PNG`, SVG base64 chứa `<svg>` | Đều hợp lệ 100% | **PASSED** |
| **UT-13** | QrTicketService | Chữ ký bị từ chối khi `APP_KEY` thay đổi | Payload ký bằng key cũ, kiểm tra với key mới | `verifySignature()` trả về `false` | Trả về `false` | **PASSED** |
| **UT-14** | QrTicketService | `hasValidSignature()` trả `false` khi thiếu payload/chữ ký | Vé thiếu `qr_signature` hoặc `qr_payload` | Trả về `false` | Trả về `false` | **PASSED** |
| **UT-15** | QrTicketService | `decodePayload()` xử lý khoảng trắng & từ chối kiểu sai | JSON có khoảng trắng; `ticket_code` là số/mảng/scalar | Trim hợp lệ; từ chối kiểu không phải string | Khớp 100% | **PASSED** |

---

### 3. Bảng đặc tả Feature Tests (88 Tests)

| Mã TC | Phân hệ | Tên Test Case | Dữ liệu đầu vào (Input) | Kết quả mong đợi (Expected) | Trạng thái |
|---|---|---|---|---|---|
| **TC-01** | Auth | Đăng ký tài khoản mới thành công | Họ tên, email mới, SĐT, mật khẩu 8+ ký tự | Tạo user role customer, redirect home, tự đăng nhập | **PASSED** |
| **TC-02** | Auth | Đăng nhập tài khoản hợp lệ | Email, password chính xác | Xác thực thành công, chuyển về trang chủ | **PASSED** |
| **TC-03** | Auth | Chặn đăng nhập mật khẩu sai | Email đúng, password sai | Chặn đăng nhập, session báo lỗi | **PASSED** |
| **TC-04** | Auth | Chặn đăng nhập tài khoản bị khóa | Tài khoản `is_active = false` | Chặn đăng nhập, thông báo tài khoản bị khóa | **PASSED** |
| **TC-05** | Auth | Cập nhật hồ sơ cá nhân | Tên mới, SĐT mới | Cập nhật thông tin trong DB, flash success | **PASSED** |
| **TC-06** | Auth | Đăng xuất an toàn | POST `/logout` từ user đã đăng nhập | Xóa phiên đăng nhập, chuyển về login | **PASSED** |
| **TC-07** | Phân quyền | Chặn Customer truy cập Admin | Customer gửi GET `/admin` | Trả về HTTP 403 Forbidden | **PASSED** |
| **TC-08** | Phân quyền | Chặn Customer truy cập Organizer | Customer gửi GET `/organizer` | Trả về HTTP 403 Forbidden | **PASSED** |
| **TC-09** | Phân quyền | Chặn Organizer sửa sự kiện người khác | Org 1 gửi GET edit sự kiện của Org 2 | Policy chặn truy cập chéo, trả về 403 | **PASSED** |
| **TC-10** | Phân quyền | Chặn Customer xem đơn hàng người khác | Customer 1 xem đơn của Customer 2 | OrderPolicy chặn và trả về 403 | **PASSED** |
| **TC-11** | Phân quyền | Chặn Admin tự khóa hoặc tự hạ quyền mình | Admin toggle khóa hoặc đổi role chính mình | Hệ thống chặn, giữ nguyên quyền Admin | **PASSED** |
| **TC-12** | Phân quyền | Chặn Organizer xem đơn hàng của sự kiện BTC khác | Org 1 GET `/organizer/orders/{order_code}` của Org 2 | OrderPolicy chặn và trả về HTTP 403 Forbidden | **PASSED** |
| **TC-13** | Storefront | Duyệt danh sách sự kiện mở bán | GET `/` | Hiển thị sự kiện `published` chưa kết thúc | **PASSED** |
| **TC-14** | Storefront | Tìm kiếm sự kiện theo từ khóa | Param `q=Marathon` | Chỉ hiển thị sự kiện khớp từ khóa | **PASSED** |
| **TC-15** | Storefront | Lọc sự kiện theo danh mục & thành phố | `category` Thể Thao & `city=Hà Nội` | Trả về đúng sự kiện khớp cả 2 điều kiện | **PASSED** |
| **TC-16** | Storefront | Chặn xem sự kiện chưa published | GET `/events/{slug}` của sự kiện `draft` | Trả về HTTP 404 Not Found | **PASSED** |
| **TC-17** | Storefront | Xem chi tiết sự kiện hiển thị `zone` và `description` | Sự kiện có vé kèm khu vực và quyền lợi | Hiển thị đầy đủ badge `zone` và khối `description` | **PASSED** |
| **TC-18** | Đặt vé | Đặt vé thành công & giữ chỗ 15 phút | 2 vé hợp lệ, thông tin người nhận | Tạo đơn `pending`, tăng `sold` lên 2 | **PASSED** |
| **TC-19** | Đặt vé | Chặn đặt vé khi số lượng = 0 | Số lượng tất cả loại vé = 0 | Báo lỗi nghiệp vụ, không tạo đơn | **PASSED** |
| **TC-20** | Đặt vé | Chặn đặt vé vượt `max_per_order` | Chọn 5 vé khi giới hạn 3 vé/đơn | Báo lỗi vượt giới hạn, `sold` giữ nguyên | **PASSED** |
| **TC-21** | Đặt vé | Chặn đặt vé vượt số lượng còn lại | Còn 1 vé, khách chọn 2 vé | Báo lỗi không đủ vé, `sold` giữ nguyên | **PASSED** |
| **TC-22** | Thanh toán | Thanh toán giả lập đơn pending & gửi mail | Chọn `bank_transfer` và thanh toán (`Mail::fake()`) | Đơn `paid`, sinh vé kèm QR & gửi `OrderPaidMail` với thông tin đã thanh toán | **PASSED** |
| **TC-23** | Thanh toán | `OrderPaidMail` tạo nội dung & đính kèm PDF | Đơn `paid` có 1 vé điện tử | Đúng tiêu đề và đính kèm 1 file PDF | **PASSED** |
| **TC-24** | Thanh toán | Trang đơn hàng và vé của tôi render mã QR & nút tải PDF | Đơn hàng trạng thái `paid` | Hiển thị mã QR và nút tải PDF | **PASSED** |
| **TC-25** | Hiệu năng | Trang chi tiết đơn hàng không phát sinh N+1 query | Đơn 1 vé vs đơn 6 vé khi gọi `orders.show` | Số lượng SQL query bằng nhau (O(1)) | **PASSED** |
| **TC-26** | Hủy đơn | Khách hàng chủ động hủy đơn pending | Đơn pending giữ 3 vé, gửi POST cancel | Đơn chuyển `cancelled`, hoàn lại 3 vé `sold` | **PASSED** |
| **TC-27** | Hủy đơn | Chặn hủy đơn hàng đã thanh toán | Đơn `paid`, gửi POST cancel | Trả về 403 Forbidden, đơn giữ `paid` | **PASSED** |
| **TC-28** | Hết hạn | Lệnh `orders:expire-pending` hủy đơn quá hạn | Đơn pending có `expires_at` ở quá khứ | Tự động hủy đơn và hoàn trả `sold` | **PASSED** |
| **TC-29** | Hết hạn | Bấm thanh toán đơn đã quá hạn 15 phút | POST pay cho đơn pending quá hạn | Tự động hủy đơn, hoàn `sold`, báo lỗi | **PASSED** |
| **TC-30** | Offline / Mail | Lỗi mail transport không rollback giao dịch thanh toán | Mock `Mail::to()` ném `RuntimeException` khi pay | Đơn vẫn chuyển `paid`, sinh vé + QR hợp lệ, redirect success | **PASSED** |
| **TC-31** | E2E Luồng vé | Luồng Checkout → Pay → Tải PDF → Check-in QR → Chặn quét lại | Khách đặt & thanh toán → tải PDF → BTC quét QR payload 2 lần | PDF hợp lệ `%PDF-`, lần 1 check-in `used` + lưu staff, lần 2 bị chặn | **PASSED** |
| **TC-32** | Check-in | Soát vé hợp lệ bằng mã nhập tay | Mã vé hợp lệ thuộc đơn `paid` của Organizer | Đổi status sang `used`, lưu `checked_in_at` & `checked_in_by` | **PASSED** |
| **TC-33** | Check-in | Báo lỗi khi mã vé không tồn tại | Mã vé không có trong DB | Báo lỗi mã vé không tồn tại | **PASSED** |
| **TC-34** | Check-in | Báo lỗi khi đơn hàng chưa thanh toán | Mã vé thuộc đơn `pending` | Báo lỗi đơn chưa thanh toán | **PASSED** |
| **TC-35** | Check-in | Báo lỗi khi vé đã sử dụng trước đó | Mã vé có trạng thái `used` | Báo lỗi vé đã check-in trước đó kèm thời gian | **PASSED** |
| **TC-36** | Check-in | Báo lỗi khi vé thuộc sự kiện Organizer khác | Org 1 soát vé sự kiện của Org 2 | Báo lỗi vé không thuộc sự kiện quản lý | **PASSED** |
| **TC-37** | Check-in | Soát vé thành công qua chuỗi QR payload hợp lệ | Chuỗi JSON `{"v":1,"ticket_code":"TKT-..."}` đúng chữ ký | Check-in thành công, status `used`, flash `success_ticket` | **PASSED** |
| **TC-38** | Check-in | Chặn quét QR khi chữ ký số HMAC bị sửa | Chuỗi JSON bị sửa payload | Báo lỗi chữ ký không hợp lệ, không điền lại input | **PASSED** |
| **TC-39** | Check-in | Chặn chuỗi QR JSON sai định dạng | Chuỗi JSON hỏng cú pháp `{"v":1,` | Báo lỗi dữ liệu mã QR không hợp lệ | **PASSED** |
| **TC-40** | Check-in | Chặn vé đã hủy qua cả nhập tay và QR payload | Vé trạng thái `cancelled` | Từ chối check-in, giữ `cancelled`, `checked_in_by` null | **PASSED** |
| **TC-41** | Check-in | Chặn quét QR khi đơn chưa thanh toán | QR payload thuộc đơn `pending` | Báo lỗi đơn chưa thanh toán, không giữ old input | **PASSED** |
| **TC-42** | Check-in | Chặn quét QR khi vé đã dùng | Quét lại QR payload lần 2 | Báo lỗi vé đã dùng, giữ nguyên thời gian check-in đầu | **PASSED** |
| **TC-43** | Check-in | Chặn quét QR vé thuộc sự kiện Organizer khác | Org 1 quét QR vé của Org 2 | Báo lỗi không thuộc quyền quản lý | **PASSED** |
| **TC-44** | Check-in | Chặn quét QR khi chữ ký lưu trong DB bị sai lệch | `qr_signature` bị chỉnh sửa trong DB | Báo lỗi sai chữ ký HMAC, từ chối check-in | **PASSED** |
| **TC-45** | Check-in | Chặn quét QR của mã vé không tồn tại | QR payload chứa mã vé ảo | Báo lỗi không tồn tại, không điền lại input | **PASSED** |
| **TC-46** | Check-in | Từ chối dữ liệu rỗng hoặc chỉ chứa khoảng trắng | `ticket_code` = `""` hoặc `"   "` | Validation báo lỗi `ticket_code` | **PASSED** |
| **TC-47** | Check-in | Chặn Customer truy cập route Check-in | Customer GET/POST `/organizer/checkin` | Trả về HTTP 403 Forbidden | **PASSED** |
| **TC-48** | Check-in | Chấp nhận mã vé nhập tay chữ thường | Nhập `tkt-lowercase1` | Tự động chuẩn hóa in hoa và check-in thành công | **PASSED** |
| **TC-49** | Check-in | Form soát vé cung cấp khung camera và ô nhập tay | GET `/organizer/checkin` | Có nút bật camera `html5-qrcode.min.js` và ô nhập tay | **PASSED** |
| **TC-50** | Check-in | Card kết quả soát vé hiển thị thông tin vé | Flash session sau khi check-in | Hiển thị mã vé, tên sự kiện, loại vé, nhân viên soát | **PASSED** |
| **TC-51** | Organizer | Form tạo loại vé có input `zone` và `description` | GET `/organizer/events/{id}/ticket-types/create` | Chứa input `zone` và textarea `description` | **PASSED** |
| **TC-52** | Organizer | Lưu thành công `zone` và `description` loại vé | POST tạo loại vé kèm khu vực và quyền lợi | Dữ liệu được lưu chính xác vào DB | **PASSED** |
| **TC-53** | Organizer | Trang chi tiết sự kiện BTC hiển thị `zone` và `description` | GET `/organizer/events/{id}` | Bảng loại vé hiển thị đầy đủ `zone` và `description` | **PASSED** |
| **TC-54** | Organizer | Form chỉnh sửa loại vé điền sẵn `zone` và `description` | GET edit loại vé | Điền sẵn dữ liệu cũ vào input | **PASSED** |
| **TC-55** | Organizer | BTC cập nhật và xóa trống được `zone` và `description` | PUT cập nhật giá trị mới rồi PUT `null` | DB cập nhật tương ứng giá trị mới và `null` | **PASSED** |
| **TC-56** | Organizer | Validation chặn `zone` > 100 và `description` > 1000 ký tự | Chuỗi `zone` 101 ký tự, `description` 1001 ký tự | Session báo lỗi validation 2 trường | **PASSED** |
| **TC-57** | Organizer | Chặn BTC khác sửa `zone` và `description` loại vé | Org khác gửi PUT `/organizer/ticket-types/{id}` | Trả về HTTP 403 Forbidden, dữ liệu gốc giữ nguyên | **PASSED** |
| **TC-58** | Ticket PDF | Render PDF vé động trong RAM | Gọi `TicketPdfService::output()` | Trả về chuỗi binary PDF bắt đầu bằng `%PDF-` | **PASSED** |
| **TC-59** | Ticket PDF | Tên file PDF xuất ra theo đúng mã vé | `filenameFor($ticket)` | Trả về `ve-{ticket_code}.pdf` | **PASSED** |
| **TC-60** | Ticket PDF | Khách hàng tải được vé PDF của mình | GET `/my-tickets/{ticket}/pdf` bởi chủ vé | Trả về HTTP 200, tải file `ve-{ticket_code}.pdf` | **PASSED** |
| **TC-61** | Ticket PDF | Chặn người lạ tải trộm vé của khách khác | GET `/my-tickets/{ticket}/pdf` bởi user khác | Trả về HTTP 403 Forbidden | **PASSED** |
| **TC-62** | Ticket PDF | Chuyển hướng khách vãng lai về trang Login | Khách chưa đăng nhập tải vé PDF | 302 Redirect về trang đăng nhập | **PASSED** |
| **TC-63** | Ticket PDF | PDF sinh trong RAM không ghi file ra ổ đĩa | Gọi `TicketPdfService::output()` | Trả về `%PDF-`, không tạo file trong `storage/app/` | **PASSED** |
| **TC-64** | Ticket PDF | View PDF hiển thị đúng tiếng Việt, `zone`, quyền lợi, QR & người soát | Render view `pdf.ticket` với vé đã check-in | Có `DejaVu Sans`, đầy đủ tiếng Việt UTF-8, QR & staff | **PASSED** |
| **TC-65** | Ticket PDF | BTC của sự kiện và Admin được tải PDF; BTC khác bị chặn | GET `/my-tickets/{ticket}/pdf` với 3 vai trò | Event Org & Admin → 200; Org khác → 403 | **PASSED** |
| **TC-66** | Ticket PDF | Chủ vé vẫn tải được PDF nếu dữ liệu cũ thiếu `qr_payload` | Vé có `qr_payload = null`, `qr_signature = null` | Tự sinh fallback QR và tải PDF thành công | **PASSED** |
| **TC-67** | Admin | Admin phê duyệt sự kiện pending | POST approve sự kiện `pending` | Chuyển sự kiện sang `published` | **PASSED** |
| **TC-68** | Admin | Admin từ chối sự kiện pending | POST reject sự kiện `pending` | Chuyển sự kiện về `draft` | **PASSED** |
| **TC-69** | Admin | Admin hủy sự kiện đơn giản | POST cancel sự kiện chưa có vé | Chuyển sự kiện sang `cancelled` | **PASSED** |
| **TC-70** | Admin | Admin hủy sự kiện cascade đơn pending và vé valid | POST cancel sự kiện đang có đơn pending & vé valid | Tự hủy đơn pending hoàn vé `sold`, chuyển vé valid sang `cancelled` | **PASSED** |
| **TC-71** | Admin | Sự kiện bị hủy chặn check-in, thanh toán & bảo vệ `sold` không âm | Thử check-in vé hoặc thanh toán đơn của sự kiện đã hủy | Báo lỗi bị chặn, `sold` về `0` không âm | **PASSED** |
| **TC-72** | Admin | Chặn hủy sự kiện đã ở trạng thái hủy trước đó | Gửi cancel cho sự kiện đã `cancelled` | Trả về HTTP 403 Forbidden | **PASSED** |
| **TC-73** | Admin | Trang sự kiện của Admin hiển thị `zone` và `description` | GET `/admin/events/{id}` | Hiển thị chi tiết khu vực và quyền lợi loại vé | **PASSED** |
| **TC-74** | Admin | Trang chi tiết đơn hàng Admin hiển thị `zone`, `description`, nhân viên soát | GET `/admin/orders/{id}` | Hiển thị đầy đủ thông tin vé và người đã check-in | **PASSED** |
| **TC-75** | Admin | Chặn xóa danh mục đang có sự kiện | DELETE danh mục đang gắn với event | Báo lỗi toàn vẹn, giữ nguyên danh mục trong DB | **PASSED** |
| **TC-76** | Admin | Admin khóa và mở khóa người dùng | POST toggle tài khoản customer | Đổi `is_active` `true <-> false` | **PASSED** |
| **TC-77** | Admin | Admin truy cập Dashboard, Users, tạo Danh mục & Địa điểm | GET dashboard/users, POST store category & venue | HTTP 200, tạo mới thành công vào DB | **PASSED** |
| **TC-78** | Storefront | Sắp xếp `price_asc` và `price_desc` đẩy sự kiện chưa có loại vé xuống cuối | Sự kiện không có loại vé và các sự kiện có giá vé khác nhau | Cả `price_asc` và `price_desc` đều xếp sự kiện `NULL` giá xuống cuối | **PASSED** |
| **TC-79** | Đặt vé | Đặt nhiều loại vé cùng đơn với thứ tự ID bất kỳ | Truyền mảng `tickets` thứ tự ID giảm dần | Khóa theo thứ tự ID tăng dần, cập nhật `sold` và tổng tiền đúng | **PASSED** |
| **TC-80** | Hủy đơn | Chặn âm `sold` khi hủy đơn có `quantity > sold` | Loại vé có `sold=1`, hủy đơn `pending` gồm 3 vé | `sold` về `0`, không văng lỗi underflow unsigned integer | **PASSED** |
| **TC-81** | Thanh toán | Chặn thanh toán đơn `pending` khi sự kiện đã bị hủy | Sự kiện chuyển sang `cancelled` trước khi khách POST pay | Từ chối thanh toán với flash error, không sinh vé | **PASSED** |
| **TC-82** | Giao diện vé | Vé đã hủy không hiển thị "Sẵn sàng sử dụng" ở trang khách hàng | Đơn `paid` có vé trạng thái `cancelled` | `orders.show` & `tickets.index` hiển thị trạng thái đã hủy, không hiện sẵn sàng | **PASSED** |
| **TC-83** | Organizer | Trang chi tiết đơn hàng BTC hiển thị `zone`, `description` & `checkedInBy` | Organizer GET `/organizer/orders/{order_code}` | Hiển thị đầy đủ khu vực, quyền lợi, nhân viên soát vé và vé bị hủy | **PASSED** |
| **TC-84** | Organizer | Sửa loại vé chưa bán không cho phép đặt `quantity = 0` | Loại vé `sold=0`, gửi PUT `quantity=0` | Validation báo lỗi `quantity` (`min:1`), giữ nguyên số lượng cũ | **PASSED** |
| **TC-85** | Organizer | Xóa mềm sự kiện nháp giữ ảnh banner đến khi `forceDelete` | Xóa bản nháp có banner rồi mới gọi `forceDelete()` | Soft delete giữ banner trên disk; `forceDelete()` mới xóa file banner | **PASSED** |
| **TC-86** | Check-in | Xử lý an toàn khi vé thuộc sự kiện bị xóa mềm trong DB | Sự kiện có vé đã thanh toán bị soft-delete trước khi check-in | Bắt ngoại lệ sạch sẽ bằng `BusinessException`, không crash 500 | **PASSED** |
| **TC-87** | Admin | Chặn xóa danh mục/địa điểm vẫn còn sự kiện đã xóa mềm | Xóa mềm sự kiện gắn category/venue rồi Admin bấm xóa category/venue | Trả về flash error thân thiện thay vì crash FK `RESTRICT` | **PASSED** |
| **TC-88** | Admin | Top sự kiện bán chạy xếp sự kiện có vé bán cao nhất lên đầu | Dashboard có sự kiện chưa tạo loại vé và sự kiện có `sold=15` | Sự kiện có `sold=15` luôn xếp #1 (không bị `NULL` nhảy lên đầu) | **PASSED** |

---

### 4. Rà soát an toàn & Bảo mật (Security Audit)
- **Bảo mật chữ ký số HMAC-SHA256**: Mã QR được ký bởi khóa bí mật `APP_KEY` và kiểm tra bằng `hash_equals()`, bảo vệ 100% trước nguy cơ làm giả hoặc sửa đổi thông tin vé.
- **CSRF Protection**: 100% các biểu mẫu gửi dữ liệu (`POST`, `PUT`, `DELETE`) đều chứa `@csrf`.
- **XSS Prevention**: 100% dữ liệu hiển thị trên Blade sử dụng cú pháp escape `{{ $variable }}` (không dùng `{!! !!}`).
- **Mass Assignment**: Toàn bộ các Model khai báo `$fillable` rõ ràng, casting Enums nghiêm ngặt.
- **SQL Injection**: Toàn bộ truy vấn sử dụng Eloquent ORM và Query Builder với Parameter Binding.
- **IDOR / Privilege Escalation**: Phân quyền đa tầng qua Middleware (`role`, `active`) và 3 Policies (`EventPolicy`, `TicketTypePolicy`, `OrderPolicy`), bao gồm bảo vệ route tải vé PDF `/my-tickets/{ticket}/pdf` và đơn hàng của Organizer.
- **Offline & Fault Tolerance**: `OrderController@pay` bọc `try/catch` quanh bước gửi email để lỗi mail transport không ảnh hưởng đơn hàng đã thanh toán; `QrTicketService`, `TicketPdfService` và `html5-qrcode.min.js` vận hành hoàn toàn nội bộ.
- **N+1 Query Elimination**: Tối ưu với `with()`, `withCount()`, `withSum()`, `withMin()` (kèm test hồi quy chống N+1 trên `orders.show`).
