# HƯỚNG DẪN SỬ DỤNG VÀ TRA CỨU SƠ ĐỒ THIẾT KẾ (DIAGRAMS)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN

Thư mục này chứa toàn bộ các mã nguồn sơ đồ thiết kế hệ thống được viết bằng cú pháp **PlantUML** chuẩn, sẵn sàng để kết xuất trực tiếp sang hình ảnh trong báo cáo và slide thuyết trình.

---

### 1. Bảng tra cứu sơ đồ và vị trí tương ứng trong báo cáo

| Tên File | Loại sơ đồ | Nội dung biểu diễn | Mục sử dụng trong báo cáo |
|---|---|---|---|
| `01_usecase_tong_quan.puml` | Use Case Diagram | Sơ đồ Use Case tổng quan của toàn bộ hệ thống gồm 4 Actor (Guest, Customer, Organizer, Admin) và 13 nhóm chức năng cốt lõi. | Chương 2: Phân tích yêu cầu (Sơ đồ Use Case tổng quan) |
| `02_usecase_khach_hang.puml` | Use Case Diagram | Sơ đồ chi tiết phân hệ Khách hàng: Duyệt sự kiện, Đặt vé, Giữ chỗ, Thanh toán, In vé, Quản lý hồ sơ cá nhân. | Chương 2: Phân tích ca sử dụng - Phân hệ Khách hàng |
| `03_usecase_ban_to_chuc.puml` | Use Case Diagram | Sơ đồ chi tiết phân hệ Ban tổ chức: CRUD sự kiện nháp, Quản lý loại vé, Gửi duyệt, Soát vé Check-in, Báo cáo doanh thu. | Chương 2: Phân tích ca sử dụng - Phân hệ Ban tổ chức |
| `04_usecase_quan_tri.puml` | Use Case Diagram | Sơ đồ chi tiết phân hệ Quản trị viên: Phê duyệt/từ chối/hủy sự kiện, Quản lý Danh mục & Địa điểm, Quản lý Người dùng, Đơn hàng toàn sàn. | Chương 2: Phân tích ca sử dụng - Phân hệ Quản trị viên |
| `05_erd.puml` | Entity Relationship Diagram | Sơ đồ CSDL vật lý chuẩn xác 100% với 8 bảng: `users`, `categories`, `venues`, `events`, `ticket_types`, `orders`, `order_items`, `tickets` kèm khóa chính, khóa ngoại, kiểu dữ liệu và lực lượng quan hệ (cardinality). | Chương 3: Thiết kế cơ sở dữ liệu (Sơ đồ ERD) |
| `06_so_do_chuc_nang.puml` | Work Breakdown Structure (WBS) | Cây phân rã chức năng nghiệp vụ của toàn bộ hệ thống theo 3 nhóm vai trò chính. | Chương 2: Cây phân rã chức năng hệ thống |
| `07_activity_mua_ve.puml` | Activity Diagram | Sơ đồ hoạt động luồng đặt vé: Chọn vé, Giao dịch nguyên tử, Khóa bi quan, Giữ chỗ 15 phút, Nhánh hủy, Nhánh quá hạn tự động và Nhánh thanh toán. | Chương 3: Thiết kế luồng nghiệp vụ Đặt vé |
| `08_sequence_thanh_toan.puml` | Sequence Diagram | Sơ đồ tuần tự tương tác đa tầng: Customer -> Controller -> Policy -> OrderService -> Database khi thanh toán đơn hàng. | Chương 3: Thiết kế tuần tự chức năng Thanh toán |
| `sequence_01_dang_nhap.puml` | Sequence Diagram | Sơ đồ tuần tự Xác thực & Phân luồng đăng nhập theo vai trò (Admin / Organizer / Customer), kiểm tra tài khoản bị khóa (`is_active`). | Chương 3: Thiết kế tuần tự Xác thực & Phân quyền |
| `sequence_02_dat_ve_lockforupdate.puml` | Sequence Diagram | Sơ đồ tuần tự Khách hàng đặt vé & Giữ chỗ 15 phút với Khóa bi quan (`lockForUpdate` + `DB::transaction`) chống bán vượt vé. | Chương 3: Thiết kế tuần tự Đặt vé & Giữ chỗ |
| `sequence_03_thanh_toan_kich_hoat_ve.puml` | Sequence Diagram | Sơ đồ tuần tự Thanh toán giả lập, kiểm tra hạn giữ chỗ, sinh mã vé điện tử độc nhất `TKT-XXXXXXXXXX`. | Chương 3: Thiết kế tuần tự Thanh toán & Phát hành vé |
| `sequence_04_soat_ve_checkin_5_buoc.puml` | Sequence Diagram | Sơ đồ tuần tự Quy trình soát vé Check-in 5 bước tại cổng sự kiện qua `TicketCheckinService`. | Chương 3: Thiết kế tuần tự Soát vé Check-in |
| `sequence_05_gui_va_duyet_su_kien.puml` | Sequence Diagram | Sơ đồ tuần tự Vòng đời sự kiện: Ban tổ chức gửi duyệt -> Quản trị viên kiểm tra và Phê duyệt/Từ chối. | Chương 3: Thiết kế tuần tự Quản lý Vòng đời Sự kiện |
| `sequence_06_tu_dong_huy_don_het_han.puml` | Sequence Diagram | Sơ đồ tuần tự Tác vụ nền (Scheduler/Artisan Command) tự động quét và hoàn trả vé các đơn quá hạn 15 phút. | Chương 3: Thiết kế tuần tự Quản lý Đơn hàng Tự động |

---

### 2. Cách xem và xuất sơ đồ ra ảnh bằng Draw.io

1. **Bước 1**: Mở trình duyệt và truy cập [https://app.diagrams.net](https://app.diagrams.net) (hoặc phần mềm Draw.io Desktop).
2. **Bước 2**: Trên thanh menu trên cùng, chọn:
   ```
   Arrange > Insert > Advanced > PlantUML...
   (Hoặc: Sắp xếp > Chèn > Nâng cao > PlantUML...)
   ```
3. **Bước 3**: Mở file `.puml` tương ứng trong thư mục này, sao chép toàn bộ nội dung (từ `@startuml` đến `@enduml` hoặc `@startwbs` đến `@endwbs`) và dán vào hộp thoại PlantUML trong draw.io.
4. **Bước 4**: Bấm nút **"Insert"** (hoặc **"Chèn"**). Draw.io sẽ tự động vẽ sơ đồ vector sắc nét.
5. **Bước 5**: Bạn có thể tùy chỉnh màu sắc, phông chữ hoặc xuất ra file PNG / PDF độ phân giải cao (`File > Export as > PNG/PDF`) để chèn vào báo cáo môn học.

---

### 3. Cách xem trước nhanh qua PlantUML Server

Nếu bạn cần xem trước trực tiếp trên web mà không cần mở Draw.io:
1. Truy cập [http://www.plantuml.com/plantuml/uml/](http://www.plantuml.com/plantuml/uml/)
2. Dán mã nguồn từ file `.puml` vào ô soạn thảo.
3. Bấm **"Submit"** để xem và tải ảnh sơ đồ ngay lập tức.
