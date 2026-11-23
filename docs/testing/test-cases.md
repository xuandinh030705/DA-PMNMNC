# BẢNG KẾT QUẢ KIỂM THỬ TỰ ĐỘNG & TEST CASES
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

---

### 1. Tổng quan kết quả kiểm thử (Test Execution Summary)
- **Công cụ kiểm thử**: PHPUnit 12.5 & Laravel Testing Suite.
- **Tổng số ca kiểm thử tự động**: **68 tests** (10 Unit tests + 58 Feature tests).
- **Số test Passed**: **68 / 68 (100%)**.
- **Số test Failed**: 0.
- **Tổng số assertions**: **254 assertions**.
- **Môi trường cơ sở dữ liệu test**: SQLite in-memory (`:memory:`), `RefreshDatabase`.
- **File nhật ký kết quả chi tiết**: [`docs/testing/test-output.txt`](./test-output.txt).

---

### 2. Bảng đặc tả Unit Tests (10 Tests)

| Mã Test | Nhóm / File | Tên Test Case | Dữ liệu đầu vào | Kết quả mong đợi | Kết quả thực tế | Trạng thái |
|---|---|---|---|---|---|---|
| **UT-01** | DomainModels | Kiểm tra accessor `remaining` của `TicketType` | `(quantity=10, sold=4)` và `(quantity=5, sold=7)` | Trả về `6` và không bao giờ âm (`0`) | Trả về đúng `6` và `0` | **PASSED** |
| **UT-02** | DomainModels | Kiểm tra helper vai trò `User` và đồng bộ Enum | `User` với vai trò từ `UserRole` và hằng số trạng thái | `isAdmin()`, `isOrganizer()`, `isCustomer()` chính xác | Toàn bộ assertions khớp 100% | **PASSED** |
| **UT-03** | QrTicketService | Payload sinh tự động từ mã vé khi thiếu | Vé có `ticket_code = 'TKT-TESTCODE12'` | Sinh JSON `{"v":1,"ticket_code":"TKT-TESTCODE12"}` | Chuỗi JSON chuẩn xác | **PASSED** |
| **UT-04** | QrTicketService | Tạo ảnh PNG Data URI từ mã vé | Vé điện tử hợp lệ | Trả về chuỗi `data:image/png;base64,...` hợp lệ | Ảnh PNG base64 hợp lệ | **PASSED** |
| **UT-05** | QrTicketService | Xuất chuỗi SVG markup cho vé | Vé điện tử hợp lệ | Chuỗi chứa thẻ `<svg ...>` | Chứa thẻ SVG hợp lệ | **PASSED** |
| **UT-06** | QrTicketService | Chữ ký số HMAC-SHA256 hợp lệ cho payload | Payload nguyên bản, ký bằng `APP_KEY` | `verifySignature()` trả về `true` | Trả về `true` | **PASSED** |
| **UT-07** | QrTicketService | Chữ ký số bị từ chối khi payload bị sửa | Payload bị thay đổi mã vé | `verifySignature()` trả về `false` | Trả về `false` | **PASSED** |
| **UT-08** | QrTicketService | Chữ ký số bị từ chối khi chuỗi rỗng | Payload rỗng hoặc chữ ký rỗng | `verifySignature()` trả về `false` | Trả về `false` | **PASSED** |
| **UT-09** | QrTicketService | Helper chữ ký số Model khớp Service | `$ticket->hasValidQrSignature()` | Trả về kết quả đồng nhất với Service | Khớp assertions | **PASSED** |
| **UT-10** | QrTicketService | Hàm giải mã payload từ chối mã vé chữ thường | Chuỗi nhập là `TKT-12345678` (không phải JSON) | Trả về `null` | Trả về `null` | **PASSED** |

---

### 3. Bảng đặc tả Feature Tests (58 Tests)

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
| **TC-12** | Storefront | Duyệt danh sách sự kiện mở bán | GET `/` | Hiển thị sự kiện `published` chưa kết thúc | **PASSED** |
| **TC-13** | Storefront | Tìm kiếm sự kiện theo từ khóa | Param `q=Marathon` | Chỉ hiển thị sự kiện khớp từ khóa | **PASSED** |
| **TC-14** | Storefront | Lọc sự kiện theo danh mục & thành phố | `category` Thể Thao & `city=Hà Nội` | Trả về đúng sự kiện khớp cả 2 điều kiện | **PASSED** |
| **TC-15** | Storefront | Chặn xem sự kiện chưa published | GET `/events/{slug}` của sự kiện `draft` | Trả về HTTP 404 Not Found | **PASSED** |
| **TC-16** | Storefront | Xem chi tiết sự kiện hiển thị `zone` và `description` | Sự kiện có vé kèm khu vực và quyền lợi | Hiển thị đầy đủ badge `zone` và khối `description` | **PASSED** |
| **TC-17** | Đặt vé | Đặt vé thành công & giữ chỗ 15 phút | 2 vé hợp lệ, thông tin người nhận | Tạo đơn `pending`, tăng `sold` lên 2 | **PASSED** |
| **TC-18** | Đặt vé | Chặn đặt vé khi số lượng = 0 | Số lượng tất cả loại vé = 0 | Báo lỗi nghiệp vụ, không tạo đơn | **PASSED** |
| **TC-19** | Đặt vé | Chặn đặt vé vượt `max_per_order` | Chọn 5 vé khi giới hạn 3 vé/đơn | Báo lỗi vượt giới hạn, `sold` giữ nguyên | **PASSED** |
| **TC-20** | Đặt vé | Chặn đặt vé vượt số lượng còn lại | Còn 1 vé, khách chọn 2 vé | Báo lỗi không đủ vé, `sold` giữ nguyên | **PASSED** |
| **TC-21** | Thanh toán | Thanh toán giả lập đơn pending | Chọn `bank_transfer` và thanh toán | Đơn `paid`, sinh vé điện tử kèm QR payload | **PASSED** |
| **TC-22** | Thanh toán | Gửi `OrderPaidMail` kèm đính kèm PDF vé | Thanh toán thành công (`Mail::fake()`) | Email được gửi kèm file vé PDF trong RAM | **PASSED** |
| **TC-23** | Thanh toán | Trang đơn hàng và vé của tôi render mã QR & nút tải PDF | Đơn hàng trạng thái `paid` | Hiển thị thẻ `<img src="data:image/svg...">` và nút tải PDF | **PASSED** |
| **TC-24** | Hủy đơn | Khách hàng chủ động hủy đơn pending | Đơn pending giữ 3 vé, gửi POST cancel | Đơn chuyển `cancelled`, hoàn lại 3 vé `sold` | **PASSED** |
| **TC-25** | Hủy đơn | Chặn hủy đơn hàng đã thanh toán | Đơn `paid`, gửi POST cancel | Trả về 403 Forbidden, đơn giữ `paid` | **PASSED** |
| **TC-26** | Hết hạn | Lệnh `orders:expire-pending` hủy đơn quá hạn | Đơn pending có `expires_at` ở quá khứ | Tự động hủy đơn và hoàn trả `sold` | **PASSED** |
| **TC-27** | Hết hạn | Bấm thanh toán đơn đã quá hạn 15 phút | POST pay cho đơn pending quá hạn | Tự động hủy đơn, hoàn `sold`, báo lỗi | **PASSED** |
| **TC-28** | Check-in | Soát vé hợp lệ bằng mã nhập tay | Mã vé hợp lệ thuộc đơn `paid` của Organizer | Đổi status sang `used`, lưu `checked_in_at` & `checked_in_by` | **PASSED** |
| **TC-29** | Check-in | Báo lỗi khi mã vé không tồn tại | Mã vé không có trong DB | Báo lỗi mã vé không tồn tại | **PASSED** |
| **TC-30** | Check-in | Báo lỗi khi đơn hàng chưa thanh toán | Mã vé thuộc đơn `pending` | Báo lỗi đơn chưa thanh toán | **PASSED** |
| **TC-31** | Check-in | Báo lỗi khi vé đã sử dụng trước đó | Mã vé có trạng thái `used` | Báo lỗi vé đã check-in trước đó kèm thời gian | **PASSED** |
| **TC-32** | Check-in | Báo lỗi khi vé thuộc sự kiện Organizer khác | Org 1 soát vé sự kiện của Org 2 | Báo lỗi vé không thuộc sự kiện quản lý | **PASSED** |
| **TC-33** | Check-in | Soát vé thành công qua chuỗi QR payload hợp lệ | Chuỗi JSON `{"v":1,"ticket_code":"TKT-..."}` có chữ ký đúng | Check-in thành công, status `used` | **PASSED** |
| **TC-34** | Check-in | Chặn quét QR khi chữ ký số HMAC bị sửa | Chuỗi JSON bị kẻ gian sửa đổi mã vé | Báo lỗi chữ ký không hợp lệ, chặn check-in | **PASSED** |
| **TC-35** | Check-in | Chặn chuỗi QR JSON sai định dạng | Chuỗi JSON thiếu trường bắt buộc | Báo lỗi dữ liệu mã QR không hợp lệ | **PASSED** |
| **TC-36** | Check-in | Chấp nhận mã vé nhập tay chữ thường | Nhập `tkt-abc123xyz` | Tự động chuyển thành chữ hoa và check-in thành công | **PASSED** |
| **TC-37** | Check-in | Form soát vé cung cấp khung camera và ô nhập tay | Truy cập `/organizer/checkin` | Có nút bật camera `html5-qrcode` và ô nhập tay | **PASSED** |
| **TC-38** | Check-in | Card kết quả soát vé hiển thị thông tin vé | Flash session sau khi check-in | Hiển thị mã vé, sự kiện, loại vé, zone, người soát | **PASSED** |
| **TC-39** | Organizer | Form tạo loại vé có input `zone` và `description` | GET `/organizer/events/{id}/ticket-types/create` | Chứa input `zone` và textarea `description` | **PASSED** |
| **TC-40** | Organizer | Lưu thành công `zone` và `description` loại vé | POST tạo loại vé kèm khu vực và quyền lợi | Dữ liệu được lưu chính xác vào DB | **PASSED** |
| **TC-41** | Organizer | Trang chi tiết sự kiện BTC hiển thị `zone` và `description` | GET `/organizer/events/{id}` | Bảng loại vé hiển thị đầy đủ `zone` và `description` | **PASSED** |
| **TC-42** | Organizer | Form chỉnh sửa loại vé điền sẵn `zone` và `description` | GET edit loại vé | Điền sẵn dữ liệu cũ vào input | **PASSED** |
| **TC-43** | Ticket PDF | Render PDF vé động trong RAM | Gọi `TicketPdfService::output()` | Trả về chuỗi binary PDF bắt đầu bằng `%PDF` | **PASSED** |
| **TC-44** | Ticket PDF | Tên file PDF xuất ra theo đúng mã vé | `filenameFor($ticket)` | Trả về `ve-{ticket_code}.pdf` | **PASSED** |
| **TC-45** | Ticket PDF | Khách hàng tải được vé PDF của mình | GET `/my-tickets/{ticket}/pdf` bởi chủ vé | Trả về HTTP 200, Content-Type `application/pdf` | **PASSED** |
| **TC-46** | Ticket PDF | Chặn người lạ tải trộm vé của khách khác | GET `/my-tickets/{ticket}/pdf` bởi user khác | Trả về HTTP 403 Forbidden | **PASSED** |
| **TC-47** | Ticket PDF | Chuyển hướng khách vãng lai về trang Login | Khách chưa đăng nhập tải vé PDF | 302 Redirect về trang đăng nhập | **PASSED** |
| **TC-48** | Admin | Admin phê duyệt sự kiện pending | POST approve sự kiện `pending` | Chuyển sự kiện sang `published` | **PASSED** |
| **TC-49** | Admin | Admin từ chối sự kiện pending | POST reject sự kiện `pending` | Chuyển sự kiện về `draft` | **PASSED** |
| **TC-50** | Admin | Admin hủy sự kiện đơn giản | POST cancel sự kiện chưa có vé | Chuyển sự kiện sang `cancelled` | **PASSED** |
| **TC-51** | Admin | Admin hủy sự kiện cascade đơn pending và vé valid | POST cancel sự kiện đang có đơn pending & vé valid | Tự hủy đơn pending hoàn vé `sold`, chuyển vé valid sang `cancelled` | **PASSED** |
| **TC-52** | Admin | Sự kiện bị hủy chặn check-in và thanh toán, bảo vệ số lượng bán | Thử check-in vé hoặc thanh toán đơn của sự kiện đã hủy | Báo lỗi bị chặn, `sold` không bị âm | **PASSED** |
| **TC-53** | Admin | Chặn hủy sự kiện đã ở trạng thái hủy trước đó | Gửi cancel cho sự kiện đã `cancelled` | Báo warning sự kiện đã hủy trước đó | **PASSED** |
| **TC-54** | Admin | Trang sự kiện của Admin hiển thị `zone` và `description` | GET `/admin/events/{id}` | Hiển thị chi tiết khu vực và quyền lợi loại vé | **PASSED** |
| **TC-55** | Admin | Trang chi tiết đơn hàng Admin hiển thị `zone`, `description`, nhân viên soát | GET `/admin/orders/{id}` | Hiển thị đầy đủ thông tin vé và người đã check-in | **PASSED** |
| **TC-56** | Admin | Chặn xóa danh mục đang có sự kiện | DELETE danh mục đang gắn với event | Báo lỗi toàn vẹn, giữ nguyên danh mục trong DB | **PASSED** |
| **TC-57** | Admin | Admin khóa và mở khóa người dùng | POST toggle tài khoản customer | Đổi `is_active` `true <-> false` | **PASSED** |
| **TC-58** | Admin | Admin truy cập Dashboard, Users, tạo Danh mục & Địa điểm | GET dashboard/users, POST store category & venue | HTTP 200, tạo mới thành công vào DB | **PASSED** |

---

### 4. Rà soát an toàn & Bảo mật (Security Audit)
- **Bảo mật chữ ký số HMAC-SHA256**: Mã QR được ký bởi khóa bí mật `APP_KEY`, bảo vệ 100% trước nguy cơ làm giả hoặc sửa đổi thông tin vé.
- **CSRF Protection**: 100% các biểu mẫu gửi dữ liệu (`POST`, `PUT`, `DELETE`) đều chứa `@csrf`.
- **XSS Prevention**: 100% dữ liệu hiển thị trên Blade sử dụng cú pháp escape `{{ $variable }}` (không dùng `{!! !!}`).
- **Mass Assignment**: Toàn bộ các Model khai báo `$fillable` rõ ràng, casting Enums nghiêm ngặt.
- **SQL Injection**: Toàn bộ truy vấn sử dụng Eloquent ORM và Query Builder với Parameter Binding.
- **IDOR / Privilege Escalation**: Phân quyền đa tầng qua Middleware (`role`, `active`) và 3 Policies (`EventPolicy`, `TicketTypePolicy`, `OrderPolicy`).
- **N+1 Query Elimination**: Tối ưu với `with()`, `withCount()`, `withSum()`, `withMin()`.
