# BẢNG KẾT QUẢ KIỂM THỬ TỰ ĐỘNG & TEST CASES
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN

---

### 1. Tổng quan kết quả kiểm thử (Test Execution Summary)
- **Công cụ kiểm thử**: PHPUnit 12.5 & Laravel Testing Suite.
- **Tổng số ca kiểm thử tự động**: **37 tests** (2 Unit tests + 35 Feature tests).
- **Số test Passed**: **37 / 37 (100%)**.
- **Số test Failed**: 0.
- **Tổng số assertions**: **104 assertions**.
- **Môi trường cơ sở dữ liệu test**: SQLite in-memory (`:memory:`), `RefreshDatabase`.
- **File nhật ký kết quả chi tiết**: [`docs/testing/test-output.txt`](./test-output.txt).

---

### 2. Bảng đặc tả Unit Tests (`tests/Unit/DomainModelsTest.php`)

| Mã Test | Tên Test Case | Dữ liệu đầu vào | Kết quả mong đợi | Kết quả thực tế | Trạng thái |
|---|---|---|---|---|---|
| **UT-01** | Kiểm tra accessor `remaining` của `TicketType` | `(quantity=10, sold=4)` và `(quantity=5, sold=7)` | Trả về `6` và không bao giờ âm (`0`) | Trả về đúng `6` và `0` | **PASSED** |
| **UT-02** | Kiểm tra helper vai trò `User` và đồng bộ Enum | `User` với vai trò từ `UserRole` và hằng số trạng thái | `isAdmin()`, `isOrganizer()`, `isCustomer()` chính xác; khớp hằng số Model | Toàn bộ assertions khớp 100% | **PASSED** |

---

### 3. Bảng đặc tả Feature Tests (35 Tests)

| Mã TC | Nhóm chức năng | Tên Test Case | Dữ liệu đầu vào (Input) | Kết quả mong đợi (Expected) | Kết quả thực tế | Trạng thái |
|---|---|---|---|---|---|---|
| **TC-01** | Xác thực | Đăng ký tài khoản mới thành công | Họ tên, email mới, SĐT, mật khẩu 8+ ký tự | Tạo user role customer, redirect home, tự đăng nhập | 302 Redirect home, DB có customer | **PASSED** |
| **TC-02** | Xác thực | Đăng nhập tài khoản hợp lệ | Email, password chính xác | Xác thực thành công, chuyển về home | 302 Redirect home, Authenticated | **PASSED** |
| **TC-03** | Xác thực | Chặn đăng nhập mật khẩu sai | Email đúng, password sai | Chặn đăng nhập, session có lỗi email | 302 Redirect login, Guest | **PASSED** |
| **TC-04** | Xác thực | Chặn đăng nhập tài khoản bị khóa | Tài khoản `is_active = false` | Chặn đăng nhập, báo lỗi tài khoản bị khóa | 302 Redirect login, lỗi `email` | **PASSED** |
| **TC-05** | Xác thực | Cập nhật hồ sơ cá nhân | Tên mới, SĐT mới | Cập nhật thông tin trong DB, flash success | DB cập nhật đúng tên và SĐT | **PASSED** |
| **TC-06** | Xác thực | Đăng xuất an toàn | POST `/logout` từ user đã đăng nhập | Xóa phiên đăng nhập, chuyển về login | 302 Redirect login, Guest | **PASSED** |
| **TC-07** | Phân quyền | Chặn Customer truy cập Admin | Customer gửi GET `/admin` | Trả về HTTP 403 Forbidden | HTTP 403 Forbidden | **PASSED** |
| **TC-08** | Phân quyền | Chặn Customer truy cập Organizer | Customer gửi GET `/organizer` | Trả về HTTP 403 Forbidden | HTTP 403 Forbidden | **PASSED** |
| **TC-09** | Phân quyền | Chặn Organizer sửa sự kiện người khác | Org 1 gửi GET edit sự kiện của Org 2 | Policy chặn truy cập chéo, trả về 403 | HTTP 403 Forbidden | **PASSED** |
| **TC-10** | Phân quyền | Chặn Customer xem đơn hàng người khác | Customer 1 xem đơn của Customer 2 | OrderPolicy chặn và trả về 403 | HTTP 403 Forbidden | **PASSED** |
| **TC-11** | Phân quyền | Chặn Admin tự khóa hoặc tự hạ quyền mình | Admin toggle khóa hoặc đổi role chính mình | Hệ thống chặn, giữ nguyên `admin` & `is_active` | Session báo lỗi, quyền giữ nguyên | **PASSED** |
| **TC-12** | Storefront | Duyệt danh sách sự kiện mở bán | GET `/` | Hiển thị sự kiện `published` chưa kết thúc | HTTP 200 OK, thấy tiêu đề sự kiện | **PASSED** |
| **TC-13** | Storefront | Tìm kiếm sự kiện theo từ khóa | Param `q=Marathon` | Chỉ hiển thị sự kiện khớp từ khóa | HTTP 200, khớp đúng sự kiện | **PASSED** |
| **TC-14** | Storefront | Lọc sự kiện theo danh mục & thành phố | `category` Thể Thao & `city=Hà Nội` | Trả về đúng sự kiện khớp cả 2 điều kiện | HTTP 200, lọc chính xác | **PASSED** |
| **TC-15** | Storefront | Chặn xem sự kiện chưa published | GET `/events/{slug}` của sự kiện `draft` | Trả về HTTP 404 Not Found | HTTP 404 Not Found | **PASSED** |
| **TC-16** | Đặt vé | Đặt vé thành công & giữ chỗ 15 phút | 2 vé hợp lệ, thông tin người nhận | Tạo đơn `pending`, tăng `sold` lên 2 | Đơn `pending` tạo thành công | **PASSED** |
| **TC-17** | Đặt vé | Chặn đặt vé khi số lượng = 0 | Số lượng tất cả loại vé = 0 | Báo lỗi nghiệp vụ, không tạo đơn | Session `error`, 0 đơn được tạo | **PASSED** |
| **TC-18** | Đặt vé | Chặn đặt vé vượt `max_per_order` | Chọn 5 vé khi giới hạn 3 vé/đơn | Báo lỗi vượt giới hạn, `sold` giữ nguyên | Session `error`, `sold = 0` | **PASSED** |
| **TC-19** | Đặt vé | Chặn đặt vé vượt số lượng còn lại | Còn 1 vé, khách chọn 2 vé | Báo lỗi không đủ vé, `sold` giữ nguyên | Session `error`, `sold = 9` | **PASSED** |
| **TC-20** | Thanh toán | Thanh toán giả lập đơn pending | Chọn `bank_transfer` và thanh toán | Đơn chuyển `paid`, set `paid_at`, sinh 2 vé | Status `paid`, 2 vé `Ticket` được tạo | **PASSED** |
| **TC-21** | Hủy đơn | Khách hàng chủ động hủy đơn pending | Đơn pending giữ 3 vé, gửi POST cancel | Đơn chuyển `cancelled`, hoàn lại 3 vé `sold` | Status `cancelled`, `sold` giảm còn 2 | **PASSED** |
| **TC-22** | Hủy đơn | Chặn hủy đơn hàng đã thanh toán | Đơn `paid`, gửi POST cancel | Trả về 403 Forbidden, đơn giữ `paid` | HTTP 403, status giữ `paid` | **PASSED** |
| **TC-23** | Hết hạn | Lệnh `orders:expire-pending` hủy đơn quá hạn | Đơn pending có `expires_at` ở quá khứ | Tự động hủy đơn và hoàn trả `sold` | Status `cancelled`, `sold` hoàn trả | **PASSED** |
| **TC-24** | Hết hạn | Bấm thanh toán đơn đã quá hạn 15 phút | POST pay cho đơn pending có `expires_at` quá khứ | Tự động hủy đơn, hoàn `sold`, redirect kèm lỗi | Status `cancelled`, `sold` hoàn trả, session `error` | **PASSED** |
| **TC-25** | Soát vé | Soát vé hợp lệ thành công | Mã vé hợp lệ thuộc đơn `paid` của Organizer | Đổi status sang `used`, lưu `checked_in_at` | Status `used`, có `checked_in_at` | **PASSED** |
| **TC-26** | Soát vé | Báo lỗi khi mã vé không tồn tại | Mã vé không có trong DB | Báo lỗi mã vé không tồn tại | Session `error` | **PASSED** |
| **TC-27** | Soát vé | Báo lỗi khi đơn hàng chưa thanh toán | Mã vé thuộc đơn `pending` | Báo lỗi đơn chưa thanh toán | Session `error`, vé không đổi | **PASSED** |
| **TC-28** | Soát vé | Báo lỗi khi vé đã sử dụng trước đó | Mã vé có trạng thái `used` | Báo lỗi vé đã check-in trước đó | Session `error` | **PASSED** |
| **TC-29** | Soát vé | Báo lỗi khi vé thuộc sự kiện Organizer khác | Org 1 soát vé sự kiện của Org 2 | Báo lỗi vé không thuộc sự kiện quản lý | Session `error`, vé giữ `valid` | **PASSED** |
| **TC-30** | Quản trị | Admin phê duyệt sự kiện pending | POST approve sự kiện `pending` | Chuyển sự kiện sang `published` | Status `published` | **PASSED** |
| **TC-31** | Quản trị | Admin từ chối sự kiện pending | POST reject sự kiện `pending` | Chuyển sự kiện về `draft` | Status `draft` | **PASSED** |
| **TC-32** | Quản trị | Admin hủy bỏ sự kiện | POST cancel sự kiện `published` | Chuyển sự kiện sang `cancelled` | Status `cancelled` | **PASSED** |
| **TC-33** | Quản trị | Chặn xóa danh mục đang có sự kiện | DELETE danh mục đang gắn với event | Báo lỗi toàn vẹn, giữ nguyên danh mục | Session `error`, danh mục còn trong DB | **PASSED** |
| **TC-34** | Quản trị | Admin khóa và mở khóa người dùng | POST toggle tài khoản customer | Đổi `is_active` `true <-> false` | DB cập nhật `is_active` đúng | **PASSED** |
| **TC-35** | Quản trị | Admin truy cập Dashboard, Users, tạo Danh mục & Địa điểm | GET dashboard/users, POST store category & venue | Hiển thị 200 OK, tạo mới thành công vào DB | HTTP 200, DB có danh mục & địa điểm mới | **PASSED** |

---

### 4. Rà soát an toàn & Bảo mật (Security Audit)
- **CSRF Protection**: 100% các biểu mẫu gửi dữ liệu (`POST`, `PUT`, `DELETE`) đều chứa `@csrf`.
- **XSS Prevention**: 100% dữ liệu hiển thị trên Blade sử dụng cú pháp escape `{{ $variable }}` (không dùng `{!! !!}`).
- **Mass Assignment**: Toàn bộ các Model khai báo `$fillable` rõ ràng.
- **SQL Injection**: Toàn bộ truy vấn sử dụng Eloquent ORM và Query Builder với Parameter Binding.
- **IDOR / Privilege Escalation**: Phân quyền đa tầng qua Middleware (`role`, `active`) và 3 Policies (`EventPolicy`, `TicketTypePolicy`, `OrderPolicy`).
- **N+1 Query Elimination**: Tối ưu với `with()`, `withCount()`, `withSum()`, `withMin()`.

---

### 5. Mục ảnh chụp màn hình minh chứng (Screenshots Placeholder)
*(Khi demo trên trình duyệt tại `http://localhost:8000`, nhóm chụp bổ sung ảnh thực tế để chèn vào báo cáo Word nếu giảng viên yêu cầu.)*

- **Ảnh 1**: Kết quả chạy `php artisan test` / `php vendor/bin/phpunit --testdox` (37/37 passed, 104 assertions).
- **Ảnh 2**: Giao diện Trang chủ, thanh tìm kiếm & bộ lọc sự kiện.
- **Ảnh 3**: Giao diện Chi tiết đơn hàng & Thanh toán giả lập (giữ chỗ 15 phút).
- **Ảnh 4**: Thẻ vé điện tử mã Monospace (`TKT-XXXXXXXXXX`) & giao diện In vé.
- **Ảnh 5**: Màn hình Soát vé (Check-in) của Ban tổ chức.
- **Ảnh 6**: Dashboard & Quản lý Người dùng của Quản trị viên (Admin).
