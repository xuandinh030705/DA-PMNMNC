# BẢNG KẾT QUẢ KIỂM THỬ TỰ ĐỘNG & TEST CASES
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN

---

### 1. Tổng quan kết quả kiểm thử (Test Execution Summary)
- **Công cụ kiểm thử**: PHPUnit 12 & Laravel Testing Suite.
- **Tổng số ca kiểm thử tự động (Feature Tests)**: 33 tests.
- **Số test Passed**: 33 / 33 (100%).
- **Số test Failed**: 0.
- **Tổng số assertions**: 82 assertions.
- **Môi trường cơ sở dữ liệu test**: SQLite in-memory (`:memory:`), `RefreshDatabase`.
- **File nhật ký kết quả chi tiết**: `docs/testing/test-output.txt`.

---

### 2. Bảng đặc tả Test Cases (TC-01 đến TC-33)

| Mã TC | Nhóm chức năng | Tên Test Case | Dữ liệu đầu vào (Input) | Kết quả mong đợi (Expected) | Kết quả thực tế | Trạng thái |
|---|---|---|---|---|---|---|
| **TC-01** | Xác thực | Đăng ký tài khoản mới thành công | Họ tên, email chưa tồn tại, SĐT, mật khẩu hợp lệ (8+ ký tự) | Tạo user với role customer, chuyển hướng về home, tự động đăng nhập | 302 Redirect về home, Database có user customer | **PASSED** |
| **TC-02** | Xác thực | Đăng nhập tài khoản hợp lệ | Email, password chính xác | Xác thực thành công, regenerate session, chuyển về trang chủ | 302 Redirect về home, Authenticated as user | **PASSED** |
| **TC-03** | Xác thực | Chặn đăng nhập mật khẩu sai | Email đúng, password sai | Chặn đăng nhập, giữ lại email, báo lỗi session error | 302 Redirect login, Session có lỗi email, Unauthenticated | **PASSED** |
| **TC-04** | Xác thực | Chặn đăng nhập tài khoản bị khóa | Email tài khoản có `is_active = false` | Chặn đăng nhập, thông báo tài khoản bị khóa | 302 Redirect login, Session báo tài khoản bị khóa | **PASSED** |
| **TC-05** | Xác thực | Cập nhật hồ sơ cá nhân | Tên mới, SĐT mới | Cập nhật thông tin trong CSDL, thông báo thành công | Database cập nhật tên và SĐT mới, Session có flash success | **PASSED** |
| **TC-06** | Xác thực | Đăng xuất an toàn | Yêu cầu POST `/logout` từ user đã đăng nhập | Xóa phiên đăng nhập, chuyển về login | 302 Redirect login, Auth::check() trả về false | **PASSED** |
| **TC-07** | Phân quyền | Chặn Customer truy cập Admin | User role customer gửi GET `/admin` | Chặn truy cập với mã HTTP 403 Forbidden | HTTP 403 Forbidden | **PASSED** |
| **TC-08** | Phân quyền | Chặn Customer truy cập Organizer | User role customer gửi GET `/organizer` | Chặn truy cập với mã HTTP 403 Forbidden | HTTP 403 Forbidden | **PASSED** |
| **TC-09** | Phân quyền | Chặn Organizer sửa sự kiện của người khác | Organizer A gửi GET `/organizer/events/{id}/edit` của Organizer B | Policy chặn truy cập chéo dữ liệu, trả về 403 Forbidden | HTTP 403 Forbidden | **PASSED** |
| **TC-10** | Phân quyền | Chặn Customer xem đơn hàng người khác | Customer A gửi GET `/orders/{code}` của Customer B | OrderPolicy chặn xem đơn hàng người khác, trả về 403 | HTTP 403 Forbidden | **PASSED** |
| **TC-11** | Phân quyền | Chặn Admin tự khóa hoặc tự hạ quyền mình | Admin gửi request toggle khóa chính mình hoặc đổi role sang customer | Hệ thống chặn thao tác, báo lỗi tiếng Việt, giữ nguyên quyền và trạng thái | Admin vẫn có role admin và is_active = true | **PASSED** |
| **TC-12** | Storefront | Duyệt danh sách sự kiện mở bán | Truy cập GET `/` | Hiển thị các sự kiện `published` và chưa kết thúc | HTTP 200 OK, hiển thị đầy đủ tiêu đề sự kiện | **PASSED** |
| **TC-13** | Storefront | Tìm kiếm sự kiện theo từ khóa | Param `q=Marathon` | Chỉ hiển thị các sự kiện chứa từ khóa "Marathon" trong tiêu đề | HTTP 200, thấy sự kiện Marathon, không thấy sự kiện Jazz | **PASSED** |
| **TC-14** | Storefront | Lọc sự kiện theo danh mục & thành phố | Param `category=Thể Thao` & `city=Hà Nội` | Chỉ trả về sự kiện Thể Thao tại Hà Nội | HTTP 200, lọc chính xác theo danh mục và thành phố | **PASSED** |
| **TC-15** | Storefront | Chặn xem chi tiết sự kiện chưa published | Truy cập GET `/events/{slug}` của sự kiện draft | Trả về lỗi HTTP 404 Not Found | HTTP 404 Not Found | **PASSED** |
| **TC-16** | Đặt vé | Đặt vé thành công & giữ chỗ 15 phút | Chọn 2 vé hợp lệ, điền thông tin người nhận | Tạo đơn pending, expires_at = now + 15p, tăng sold | Đơn pending được tạo, sold tăng từ 0 lên 2 | **PASSED** |
| **TC-17** | Đặt vé | Chặn đặt vé khi số lượng = 0 | Mảng vé có quantity = 0 | Ném ngoại lệ nghiệp vụ, không tạo đơn hàng | Session có lỗi, không có bản ghi orders nào được tạo | **PASSED** |
| **TC-18** | Đặt vé | Chặn đặt vé vượt max_per_order | Loại vé giới hạn 3 vé/đơn, khách chọn 5 vé | Ném ngoại lệ nghiệp vụ thông báo vượt giới hạn | Báo lỗi, sold không đổi, không tạo đơn hàng | **PASSED** |
| **TC-19** | Đặt vé | Chặn đặt vé vượt số lượng còn lại | Loại vé còn lại 1 vé, khách chọn 2 vé | Ném ngoại lệ nghiệp vụ không đủ vé tồn kho | Báo lỗi không đủ vé, sold không đổi | **PASSED** |
| **TC-20** | Thanh toán | Thanh toán giả lập đơn pending | Chọn phương thức bank_transfer và bấm thanh toán | Đơn hàng sang paid, set paid_at, sinh đủ vé TKT-... | Đơn hàng status paid, 2 vé Ticket được tạo thành công | **PASSED** |
| **TC-21** | Hủy đơn | Khách hàng chủ động hủy đơn pending | Đơn hàng pending có 3 vé giữ chỗ, gửi POST cancel | Đơn chuyển sang cancelled, trừ trả lại 3 vé vào sold | Đơn cancelled, cột sold của loại vé giảm tương ứng | **PASSED** |
| **TC-22** | Hủy đơn | Chặn hủy đơn hàng đã thanh toán | Đơn hàng đã ở trạng thái paid, gửi POST cancel | Chặn thao tác với mã 403 Forbidden | HTTP 403 Forbidden, đơn vẫn giữ nguyên status paid | **PASSED** |
| **TC-23** | Hết hạn | Lệnh `orders:expire-pending` hủy đơn quá hạn | Đơn pending có `expires_at` ở quá khứ | Tự động hủy đơn hàng và hoàn trả lại số vé đã giữ | Đơn chuyển sang cancelled, số lượng sold được hoàn lại | **PASSED** |
| **TC-24** | Soát vé | Soát vé hợp lệ thành công | Nhập mã vé hợp lệ thuộc đơn paid của sự kiện mình | Đổi status sang used, lưu thời điểm checked_in_at | Status ticket là used, checked_in_at được ghi nhận | **PASSED** |
| **TC-25** | Soát vé | Báo lỗi khi mã vé không tồn tại | Nhập mã vé ngẫu nhiên không có trong hệ thống | Báo lỗi: "Mã vé không tồn tại trong hệ thống." | Session có flash error thông báo mã không tồn tại | **PASSED** |
| **TC-26** | Soát vé | Báo lỗi khi đơn hàng chưa thanh toán | Nhập mã vé của đơn hàng chưa hoàn tất thanh toán | Báo lỗi: "Đơn hàng chưa hoàn tất thanh toán." | Session có flash error, vé không chuyển used | **PASSED** |
| **TC-27** | Soát vé | Báo lỗi khi vé đã sử dụng trước đó | Nhập mã vé đã có trạng thái used | Báo lỗi: "Vé đã được sử dụng vào lúc..." | Session có flash error thông báo thời điểm đã dùng | **PASSED** |
| **TC-28** | Soát vé | Báo lỗi khi vé thuộc sự kiện của người khác | Organizer A soát vé thuộc sự kiện do Organizer B tạo | Báo lỗi: "Vé không thuộc sự kiện bạn quản lý." | Session có flash error, vé không đổi trạng thái | **PASSED** |
| **TC-29** | Quản trị | Admin phê duyệt sự kiện pending | Gửi POST approve cho sự kiện pending | Chuyển trạng thái sự kiện sang published | Sự kiện chuyển sang published và mở bán công khai | **PASSED** |
| **TC-30** | Quản trị | Admin từ chối sự kiện pending | Gửi POST reject cho sự kiện pending | Chuyển trạng thái sự kiện về draft để BTC sửa | Sự kiện chuyển về draft | **PASSED** |
| **TC-31** | Quản trị | Admin hủy bỏ sự kiện | Gửi POST cancel cho sự kiện | Chuyển trạng thái sự kiện sang cancelled | Sự kiện chuyển sang cancelled | **PASSED** |
| **TC-32** | Quản trị | Chặn xóa danh mục đang có sự kiện liên kết | Gửi DELETE danh mục đang có sự kiện | Báo lỗi toàn vẹn dữ liệu, chặn không cho xóa | Danh mục vẫn tồn tại trong CSDL, session có lỗi | **PASSED** |
| **TC-33** | Quản trị | Admin khóa và mở khóa người dùng | Gửi POST toggle cho tài khoản customer | Đổi trạng thái is_active giữa true và false | Database cập nhật is_active tương ứng | **PASSED** |

---

### 3. Rà soát an toàn & Bảo mật (Security Audit)
- **CSRF Protection**: 100% các biểu mẫu gửi dữ liệu (POST, PUT, DELETE) đều chứa `@csrf`.
- **XSS Prevention**: 100% dữ liệu hiển thị trên Blade sử dụng cú pháp an toàn `{{ $variable }}`. Không có bất kỳ thẻ `{!! !!}` nào áp dụng cho dữ liệu do người dùng nhập.
- **Mass Assignment**: Toàn bộ các Model Eloquent khai báo danh sách `$fillable` chặt chẽ, bảo vệ các cột nhạy cảm.
- **SQL Injection**: Toàn bộ thao tác truy vấn CSDL đều sử dụng Eloquent ORM và Query Builder với Parameter Binding.
- **IDOR / Privilege Escalation**: Kiểm soát phân quyền đa tầng qua Middleware (`role`, `active`) và Policy (`EventPolicy`, `TicketTypePolicy`, `OrderPolicy`).
- **N+1 Query Elimination**: Các trang danh sách đều tối ưu hóa với `with()`, `withCount()`, `withSum()`, `withMin()`.

---

### 4. Mục ảnh chụp màn hình minh chứng (Screenshots Placeholder)
*(Phần dành cho sinh viên/nhóm thực hiện tự chèn ảnh chụp màn hình kiểm thử thực tế vào báo cáo)*

- **Ảnh 1: Kết quả thực thi PHPUnit Test trong Terminal**
  ```
  [CHÈN ẢNH CHỤP MÀN HÌNH LỆNH PHP ARTISAN TEST TẠI ĐÂY]
  ```

- **Ảnh 2: Giao diện Trang chủ và Bộ lọc sự kiện**
  ```
  [CHÈN ẢNH CHỤP MÀN HÌNH TRANG CHỦ TẠI ĐÂY]
  ```

- **Ảnh 3: Giao diện Đặt vé và Đếm ngược giữ chỗ 15 phút**
  ```
  [CHÈN ẢNH CHỤP MÀN HÌNH ĐẶT VÉ TẠI ĐÂY]
  ```

- **Ảnh 4: Thẻ vé điện tử có mã Monospace và nút in**
  ```
  [CHÈN ẢNH CHỤP MÀN HÌNH VÉ ĐIỆN TỬ TẠI ĐÂY]
  ```

- **Ảnh 5: Màn hình Soát vé (Check-in) của Ban tổ chức**
  ```
  [CHÈN ẢNH CHỤP MÀN HÌNH CHECK-IN TẠI ĐÂY]
  ```

- **Ảnh 6: Dashboard Quản trị viên (Admin)**
  ```
  [CHÈN ẢNH CHỤP MÀN HÌNH ADMIN DASHBOARD TẠI ĐÂY]
  ```
