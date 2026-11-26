# BÁO CÁO MỤC 6 & 7: LÀM VIỆC NHÓM & BẢO VỆ ĐỒ ÁN (CHUẨN ĐẦU RA PLO6, PLO1, PLO2, PLO3)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

- **Mã tài liệu**: TEAM-DEFENSE-ETMS-v2.0
- **Nhóm thực hiện**: **Nhóm 8 — Lớp 68PM2**
- **Thành viên**: Hồ Đăng Quang (`0021568`), Tống Xuân Định (`0008268`), Nguyễn Hữu Quý (`0022968`)
- **Repository GitHub**: `https://github.com/xuandinh030705/DA-PMNMNC`
- **Nền tảng**: Laravel 13 (PHP 8.3+) / MySQL 8.0 & SQLite
- **File Word đính kèm**: [06_bao_cao_lam_viec_nhom_va_bao_ve.docx](./06_bao_cao_lam_viec_nhom_va_bao_ve.docx)
- **Chuẩn đầu ra**: PLO6 (Làm việc nhóm), PLO1, PLO2, PLO3 (Trình bày & bảo vệ sản phẩm — đối chiếu `tailieu/4.jpg`)

---

### BẢNG ĐÁNH GIÁ 5 TIÊU CHÍ MỤC 6 & 7

| Nhóm | Tiêu Chí | Nội Dung Tiêu Chí Đánh Giá | Trạng Thái Hiện Tại | Minh Chứng & Ghi Chú |
| :---: | :---: | :--- | :---: | :--- |
| **Nhóm** | **6.1** | Phân công nhiệm vụ rõ ràng giữa thành viên | **100% Hoàn thành** | Bảng phân công 3 thành viên Nhóm 8 - Lớp 68PM2 trong [`WORKLOG.md`](./WORKLOG.md) & [`KE_HOACH_NANG_CAP_1_THANG.md`](../KE_HOACH_NANG_CAP_1_THANG.md) |
| **Nhóm** | **6.2** | Các thành viên có đóng góp phù hợp | **100% Hoàn thành** | Phân chia đồng đều (~34% / 33% / 33%) gắn với từng phân hệ, nhánh Git & PR cụ thể |
| **Nhóm** | **6.3** | Nhật ký làm việc, commit Git hoặc minh chứng tiến độ | **100% Hoàn thành** | Repository GitHub (`xuandinh030705/DA-PMNMNC`), GitHub Flow (nhánh `feat/`/`fix/`/`test/`/`chore/` + PR), GitHub Actions CI, [`WORKLOG.md`](./WORKLOG.md), [`PROGRESS.md`](./PROGRESS.md) |
| **Nhóm** | **6.4** | Phối hợp, giải quyết vấn đề, hoàn thành đúng hạn | **100% Hoàn thành** | Phối hợp giải quyết `lockForUpdate`, giữ chỗ 15p, QR HMAC, PDF RAM, Camera offline, N+1 `orders.show`, Admin Cancel Cascade |
| **Bảo vệ** | **7.1** | Demo sản phẩm và trả lời câu hỏi về phân tích, thiết kế, code | **Sẵn sàng** | [`DEMO_SCRIPT.md`](./DEMO_SCRIPT.md) phân vai 3 thành viên (8–10 phút) + bộ câu hỏi-đáp phản biện chuyên sâu |

---

### MỤC 6: LÀM VIỆC NHÓM & QUẢN LÝ TIẾN ĐỘ (PLO6)

#### 6.1. Bảng Phân Công Nhiệm Vụ & Đóng Góp Của 3 Thành Viên (Nhóm 8 - Lớp 68PM2)

| STT | Thành Viên (MSSV) | Vai Trò Trong Nhóm | Module & Công Việc Đảm Nhận (Nền tảng + Đợt nâng cấp 4 tuần) | Tỷ Lệ Đóng Góp |
| :---: | :--- | :--- | :--- | :---: |
| **1** | **Hồ Đăng Quang**<br>(`0021568`) | Trưởng nhóm / Kiến trúc & Fullstack (Storefront) | • Điều phối tiến độ, phân tích yêu cầu (`01_phan_tich_yeu_cau.md`), đặc tả `SPEC.md`, quản trị GitHub Flow & review PR.<br>• Thiết kế Layouts (`app`, `panel`), Storefront tìm/lọc/hiển thị `zone` & `description`, `OrderService` (`lockForUpdate()`), thanh toán giả lập, **Email `OrderPaidMail` + Mailpit**, cấu hình **MySQL 8** (`.env.example`), tối ưu N+1, `README.md` và kịch bản Demo Chặng 1. | **34%** |
| **2** | **Tống Xuân Định**<br>(`0008268`) | Backend, Database, QR/PDF & Organizer Developer | • Thiết kế CSDL 3NF, 12 Migrations, 8 Models, Factories & Seeders (gồm `TicketboxEventSeeder`), Auth/Profile, Middleware (`CheckRole`, `CheckActive`), 3 Policies, Scheduler `orders:expire-pending`.<br>• Xây dựng **`QrTicketService` (`endroid/qr-code` + HMAC-SHA256)**, **`TicketPdfService` (`dompdf` khổ A5)**, route `tickets.pdf`, **Camera quét QR (`html5-qrcode.min.js` offline)**, Check-in 6 bước ghi `checked_in_by`, quản lý `zone`/`description` phía Organizer & Demo Chặng 2. | **33%** |
| **3** | **Nguyễn Hữu Quý**<br>(`0022968`) | Admin Developer, QA, CI/CD & System Architecture | • Xây dựng phân hệ Admin (Dashboard, CRUD Danh mục/Địa điểm `RESTRICT`, Quản lý người dùng & chống tự khóa, Phê duyệt/từ chối và **Hủy sự kiện cascade** tự hủy đơn `pending` + vé `valid`, hiển thị `checkedInBy`).<br>• Đồng bộ **4 Enums** trên Models, thiết lập **GitHub Actions CI (MySQL 8 + Pint + PHPUnit)**, xây dựng/mở rộng bộ **103 Unit/Feature Tests**, tài liệu kiểm thử `docs/testing/*`, sơ đồ hệ thống & bộ báo cáo nghiệm thu + Demo Chặng 3. | **33%** |

#### 6.2. Quy trình Quản lý Mã nguồn Git & GitHub CI/CD
- Nhóm vận hành theo **GitHub Flow** trên repository `https://github.com/xuandinh030705/DA-PMNMNC`: mỗi thành viên làm việc trên nhánh riêng (`chore/mysql8-setup`, `feat/qr-and-pdf-services`, `chore/github-actions-and-enums`, `feat/camera-checkin-and-organizer-zone`, `feat/storefront-qr-pdf-and-email`, `feat/admin-cancel-cascade-and-views`, `test/mail-storefront-and-readme`, `test/qr-pdf-checkin-and-srs`, `fix/n1-orders-and-week3-docs`, ...), tạo Pull Request và review chéo trước khi merge vào `develop` / `main`.
- Mọi thao tác push và PR đều được kiểm chứng tự động qua [`.github/workflows/ci.yml`](../.github/workflows/ci.yml) (Laravel Pint + `migrate:fresh --seed` + PHPUnit trên container **MySQL 8.0**).
- Toàn bộ nhật ký công việc qua từng Phase và 4 tuần nâng cấp được lưu đầy đủ tại [`docs/PROGRESS.md`](./PROGRESS.md) và [`docs/WORKLOG.md`](./WORKLOG.md).

#### 6.3. Phối Hợp & Giải Quyết Vấn Đề Kỹ Thuật
1. **Race Condition khi mua vé & check-in**: Áp dụng Khóa bi quan (`lockForUpdate()`) trong `DB::transaction()` trên `InnoDB` (MySQL 8) ở `OrderService`, `TicketCheckinService` và `Admin\EventController::cancel()`.
2. **Bảo mật mã vé QR & chống IDOR tải PDF**: Ký `qr_payload` bằng HMAC-SHA256 (`hash_equals()`), bảo vệ `/my-tickets/{ticket}/pdf` bằng `OrderPolicy`.
3. **Tối ưu truy vấn chống N+1**: Áp dụng `with()`, `withCount()`, `withMin()`, `withSum()`, eager load `tickets.orderItem.ticketType.event.venue` và `tickets.checkedInBy` trên `orders.show`.
4. **Hoạt động Offline ổn định**: `html5-qrcode.min.js` lưu cục bộ, PDF tạo trong RAM không ghi đĩa, gửi email bọc `try/catch` với mặc định `MAIL_MAILER=log`.

---

### MỤC 7: TRÌNH BÀY & BẢO VỆ ĐỒ ÁN (PLO1, PLO2, PLO3)

#### 7.1. Kịch Bản Trình Diễn Sản Phẩm (8–10 Phút) — Nhóm 8 (68PM2)
Chi tiết thao tác, tài khoản demo (`admin@example.com`, `organizer1@example.com`, `customer1@example.com`, mật khẩu `password`) và lời thoại được biên soạn tại [`docs/DEMO_SCRIPT.md`](./DEMO_SCRIPT.md):
- **Chặng 1 (~3.5 phút — Hồ Đăng Quang)**: Storefront tìm/lọc, xem `zone` & `description` → Đặt vé giữ chỗ 15p → Thanh toán giả lập → Xem Email xác nhận kèm PDF trên Mailpit → Xem ảnh QR và tải vé PDF khổ A5.
- **Chặng 2 (~3.5 phút — Tống Xuân Định)**: Dashboard Organizer → Tạo sự kiện + loại vé kèm `zone`/`description` → Gửi duyệt → Quét **Camera QR** (`html5-qrcode`) tại `/organizer/checkin`, hiển thị thẻ xanh HMAC-SHA256 & nhân viên soát vé, test chặn vé đã dùng / QR giả mạo.
- **Chặng 3 (~2–3 phút — Nguyễn Hữu Quý)**: Dashboard Admin → Duyệt sự kiện → Test **Hủy sự kiện Cascade** → Kiểm tra chi tiết đơn hàng có `zone`, `description`, `checkedInBy` → Chặn xóa danh mục `RESTRICT` → Khóa/mở user → Chạy `php artisan test` (**103/103 tests PASSED, 411 assertions**) & mở GitHub Actions CI xanh trên MySQL 8.

#### 7.2. Bộ Câu Hỏi & Đáp Án Phản Biện Chuyên Sâu
Đã cập nhật đầy đủ tại Phần 2 của [`docs/DEMO_SCRIPT.md`](./DEMO_SCRIPT.md), bao gồm các nhóm câu hỏi về CSDL & `lockForUpdate()`, QR HMAC-SHA256, PDF trong RAM qua DomPDF, Email Mailpit/`log`, Camera Check-in 6 bước offline, Admin Cancel Cascade, chống N+1, phân quyền Policy/Middleware và triển khai MySQL 8 + GitHub Actions CI.
