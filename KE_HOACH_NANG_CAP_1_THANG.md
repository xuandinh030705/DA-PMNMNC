# KẾ HOẠCH NÂNG CẤP HỆ THỐNG BÁN VÉ SỰ KIỆN (LỘ TRÌNH 1 THÁNG)

> **Dự án:** Hệ thống Quản lý Bán vé Sự kiện trực tuyến (`EventTicket`)  
> **Nhóm thực hiện:** Nhóm 8 — Lớp **68PM2** (**Hồ Đăng Quang**, **Tống Xuân Định**, **Nguyễn Hữu Quý**)  
> **Thời gian thực hiện:** **4 Tuần (1 Tháng)**  
> **Mục tiêu:** Giữ nguyên nền giao diện **Laravel Blade + Bootstrap 5.3** đang chạy ổn định, tập trung nâng cấp sâu phần **MySQL 8, Đồng bộ Enum, Vé QR bảo mật HMAC, Camera Check-in, Xuất PDF động, Email Mailpit, GitHub Actions CI & Sơ đồ `draw.io`**, đảm bảo tuân thủ 100% Rubric môn học.

---

## PHẦN 1: CHI TIẾT CÁC CÔNG NGHỆ SẮP NÂNG CẤP

### 1.1 Bảng tổng hợp chuyển đổi công nghệ (Hiện tại → Nâng cấp)

| Hạng mục | Hiện tại đang dùng | Công nghệ nâng cấp mới | Vai trò & Lý do kỹ thuật |
| :--- | :--- | :--- | :--- |
| **Backend Core** | PHP 8.5 (`^8.3`), Laravel 13, MVC + Service Layer | Giữ **PHP 8.3+ / 8.5, Laravel 13**, bổ sung **Enum casting**, **Mailable**, **Service mới** (`QrTicketService`, `TicketPdfService`) | Giữ nguyên kiến trúc sạch sẵn có, mở rộng thêm các Service tách biệt trách nhiệm cho QR và PDF. |
| **Cơ sở dữ liệu** | SQLite mặc định (`database/database.sqlite`) | **MySQL 8 Community Server + DBeaver** *(vẫn tương thích XAMPP/Laragon khi chấm bài)* | Giúp `DB::transaction()` + `lockForUpdate()` khóa đúng mức dòng (InnoDB row-level lock), chống bán vượt vé và check-in trùng hiệu quả. |
| **Frontend & Asset** | Laravel Blade + Bootstrap 5.3.3 CDN + Bootstrap Icons 1.11.3 CDN | **Giữ nguyên Laravel Blade + Bootstrap 5.3 + Bootstrap Icons** (+ Blade Components nếu cần) | Không phải viết lại 30+ file giao diện, giữ nguyên `Paginator::useBootstrapFive()`, **không cần Node.js / `npm run build`** khi chấm bài. |
| **Mã vé & Check-in** | Mã chữ `TKT-XXXXXXXXXX`, nhập tay ở form Check-in | **`endroid/qr-code`** (sinh QR ký **HMAC-SHA256**) + **`html5-qrcode`** (quét camera) + giữ ô nhập tay | Nâng cấp vé điện tử thực tế, chống giả mạo mã vé qua chữ ký số HMAC, hỗ trợ quét trực tiếp bằng webcam/điện thoại lẫn nhập tay dự phòng. |
| **Xuất vé PDF** | Chưa có (chỉ in HTML bằng `window.print()`) | **`barryvdh/laravel-dompdf`** (tạo PDF động *on-the-fly*, không lưu file lên ổ đĩa) | Xuất file PDF vé chuyên nghiệp đính kèm QR Code & font tiếng Việt Unicode, tải qua route bảo vệ bởi `OrderPolicy`. |
| **Email thông báo** | Chưa triển khai gửi mail (`.env` chỉ để `log`) | **Laravel Mail (`Mailable`)**: `.env.example` mặc định **`log`**, dev dùng **Mailpit** | Tự động gửi email xác nhận đơn hàng kèm file PDF vé (tạo trong RAM); an toàn khi giảng viên chấm offline, trực quan khi nhóm test qua Mailpit. |
| **Thanh toán & Lưu trữ** | Thanh toán giả lập; Laravel Storage `public` + `storage:link` | **Giữ nguyên** Thanh toán giả lập + Storage `public` | Đảm bảo hệ thống chạy ổn định 100% không phụ thuộc Internet/API key ngoài trong buổi bảo vệ (Mục 5 Rubric). |
| **Kiểm thử & QA** | PHPUnit 12 (37 tests), Laravel Pint | **PHPUnit 12** (mở rộng thêm test mới) + **Laravel Pint** | Kiểm chứng tự động toàn bộ luồng mới: QR HMAC, PDF download, Mail fake, `zone`/`description`, check-in lock, hủy sự kiện. |
| **CI/CD & Quản lý nhóm** | Chạy tay local, tài liệu tiến độ bằng Markdown (`WORKLOG.md`) | **Git + GitHub (GitHub Flow)** + **GitHub Projects** + **GitHub Actions** | Tự động chạy `pint --test` & `php artisan test` trên MySQL 8 mỗi lần Push/PR; quản lý task Kanban và ghi nhận đóng góp thật của cả 3 thành viên. |
| **Sơ đồ thiết kế** | PlantUML (`.puml` + `.png`) | **`draw.io`** (Use Case, ERD, Sơ đồ chức năng, Activity/Sequence) | Đồng bộ lại toàn bộ sơ đồ với 5 cột DB mới & luồng QR/PDF/Email, dễ chỉnh sửa trực quan cho báo cáo. |

---

### 1.2 Chi tiết kỹ thuật & Lưu ý triển khai từng công nghệ mới

1. **MySQL 8 Community Server + DBeaver:**
   * Cấu hình chuẩn InnoDB, charset `utf8mb4`, collation `utf8mb4_unicode_ci`, database `event_ticket`.
   * Lưu ý: Không bật MySQL của XAMPP cùng lúc để tránh đụng cổng `3306`; giữ nguyên `php.exe` để chạy `php artisan serve`.
2. **PHP Extension bắt buộc (`php.ini`):**
   * `endroid/qr-code` (PNG writer) và `barryvdh/laravel-dompdf` yêu cầu bật **`extension=gd`** trong `php.ini` (ghi chú rõ bước này vào `README.md`).
   * Đảm bảo `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip` đã bật.
3. **Giao diện Bootstrap 5.3 + Blade (Giữ nguyên nền hiện tại):**
   * Giữ nguyên [`layouts/app.blade.php`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/resources/views/layouts/app.blade.php), [`layouts/panel.blade.php`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/resources/views/layouts/panel.blade.php) và `Paginator::useBootstrapFive()` trong [`AppServiceProvider.php`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/app/Providers/AppServiceProvider.php).
   * Chỉ bổ sung giao diện tại đúng các màn hình có tính năng mới:
     * Hiển thị `zone` (khu vực) & `description` (quyền lợi) tại trang chi tiết sự kiện, chi tiết quản lý sự kiện và form tạo/sửa loại vé.
     * Hiển thị ảnh QR Code + nút **Tải vé PDF** tại trang "Vé của tôi" và "Chi tiết đơn hàng".
     * Tích hợp khung camera quét QR + hiển thị người soát vé (`checkedInBy`) tại trang Check-in của Ban tổ chức.
   * Có thể tách thêm một số Blade Component (`resources/views/components/`) nếu cần tái sử dụng thẻ vé hoặc badge.
4. **QR Code (`endroid/qr-code` + `html5-qrcode`):**
   * Payload QR lưu trong `tickets.qr_payload` dạng JSON `{"v":1,"ticket_code":"TKT-..."}` và chữ ký `tickets.qr_signature = hash_hmac('sha256', payload, APP_KEY)`.
   * Khi khách xem "Vé của tôi", hiển thị ảnh QR (SVG/PNG base64) từ `QrTicketService`.
   * Trang Check-in của Organizer (`organizer/checkin/form.blade.php`) nhúng thư viện JS `html5-qrcode` (lưu local tại `public/js/html5-qrcode.min.js` để không phụ thuộc CDN khi demo offline) qua `@push('scripts')`, hỗ trợ quét bằng camera (mở qua `http://localhost:8000` để trình duyệt cấp quyền webcam) **song song** với ô nhập mã tay `TKT-...`.
   * Khi gửi mã quét từ QR lên `TicketCheckinService`, xác thực cả chữ ký `qr_signature` (nếu payload QR) lẫn 5 bước kiểm tra hiện có + ghi `checked_in_by`.
5. **PDF động (`barryvdh/laravel-dompdf`):**
   * Không lưu file PDF ra ổ đĩa (không dùng cột `pdf_path`).
   * Tạo `TicketPdfService` render view `resources/views/pdf/ticket.blade.php` bằng HTML/CSS tương thích DomPDF (dùng `<table>`, tránh Flexbox/Grid, khai báo `font-family: DejaVu Sans` để hiển thị chuẩn dấu tiếng Việt) và nhúng ảnh QR base64 từ `QrTicketService`.
   * Route tải vé `GET /my-tickets/{ticket}/pdf` kiểm tra quyền sở hữu qua `OrderPolicy` (chặn người lạ tải trộm vé của khách khác).
6. **Email (`Mailable` + Mailpit):**
   * Tạo `OrderPaidMail` gửi sau khi `OrderService::payOrder()` hoàn tất: nội dung tóm tắt đơn hàng + đính kèm file vé PDF tạo động trong RAM (`$pdf->output()`).
   * Môi trường dev cài `Mailpit` (`MAIL_MAILER=smtp`, `MAIL_HOST=127.0.0.1`, `MAIL_PORT=1025`), file `.env.example` mặc định `MAIL_MAILER=log`.
7. **GitHub Actions (`.github/workflows/ci.yml`):**
   * Kích hoạt khi `push` hoặc `pull_request` vào `main`/`master`.
   * Dùng container service `mysql:8.0`, cài PHP 8.3+ (kèm `pdo_mysql`, `gd`) + Composer dependencies, chạy `vendor/bin/pint --test` và `php artisan test` (không cần bước Node.js/Vite build vì giữ Bootstrap).

---

## PHẦN 2: CẤU TRÚC DỰ ÁN SẮP THAY ĐỔI

Sơ đồ cây thư mục dự kiến sau nâng cấp (đánh dấu rõ trạng thái từng thành phần):

```text
DA_PMNM_NC/
├── .github/
│   └── workflows/
│       └── ci.yml                          # [THÊM MỚI] Workflow GitHub Actions (Pint + PHPUnit + MySQL 8)
├── app/
│   ├── Enums/                              # [CHỈNH SỬA/ĐỒNG BỘ] Cast EventStatus, OrderStatus, TicketStatus, UserRole vào Models
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/EventController.php   # [CHỈNH SỬA] Xử lý hủy đơn pending & vé valid khi Admin hủy sự kiện
│   │   │   ├── Organizer/
│   │   │   │   └── CheckinController.php   # [CHỈNH SỬA] Hỗ trợ nhận payload QR hoặc ticket_code từ camera/nhập tay
│   │   │   └── Storefront/
│   │   │       ├── OrderController.php     # [CHỈNH SỬA] Gửi OrderPaidMail sau khi thanh toán thành công
│   │   │       └── TicketController.php    # [CHỈNH SỬA] Thêm action downloadPdf(Ticket $ticket)
│   │   └── Requests/
│   │       └── Organizer/
│   │           ├── TicketTypeRequest.php   # [ĐÃ XONG CSDL] Hỗ trợ validate zone, description
│   │           └── CheckinRequest.php      # [CHỈNH SỬA] Cho phép nhận mã vé hoặc chuỗi QR payload
│   ├── Mail/
│   │   └── OrderPaidMail.php               # [THÊM MỚI] Mailable gửi xác nhận thanh toán + đính kèm PDF vé
│   ├── Models/
│   │   ├── Event.php / Order.php / User.php # [CHỈNH SỬA] Đồng bộ cast Enum (nếu gộp)
│   │   ├── Ticket.php                      # [ĐÃ XONG CSDL] qr_payload, qr_signature, checked_in_by, HMAC helpers
│   │   └── TicketType.php                  # [ĐÃ XONG CSDL] zone, description
│   └── Services/
│       ├── OrderService.php                # [ĐÃ XONG CSDL] Sinh qr_payload & qr_signature khi payOrder()
│       ├── TicketCheckinService.php        # [ĐÃ XONG CSDL] DB::transaction + lockForUpdate + lưu checked_in_by; [NÂNG CẤP THÊM] verify qr_signature
│       ├── QrTicketService.php             # [THÊM MỚI] Sinh ảnh QR (SVG / PNG base64) qua endroid/qr-code & verify payload
│       └── TicketPdfService.php            # [THÊM MỚI] Render PDF vé động qua barryvdh/laravel-dompdf
├── database/
│   ├── factories/
│   │   ├── TicketFactory.php               # [ĐÃ XONG CSDL]
│   │   └── TicketTypeFactory.php           # [ĐÃ XONG CSDL]
│   ├── migrations/
│   │   ├── 2026_11_12_210058_add_qr_and_checkin_columns_to_tickets_table.php      # [ĐÃ XONG CSDL]
│   │   └── 2026_11_12_210059_add_zone_and_description_to_ticket_types_table.php   # [ĐÃ XONG CSDL]
│   └── seeders/
│       ├── EventSeeder.php                 # [ĐÃ XONG CSDL] Seed thêm zone, description
│       └── OrderSeeder.php                 # [ĐÃ XONG CSDL] Seed thêm checked_in_by
├── public/
│   └── js/
│       └── html5-qrcode.min.js             # [THÊM MỚI] Thư viện quét QR camera chạy offline không phụ thuộc CDN
├── docs/
│   ├── 01_phan_tich_yeu_cau.md             # [CHỈNH SỬA] Cập nhật phạm vi: bỏ Out-of-scope QR/PDF/Mail
│   ├── 02_thiet_ke_co_so_du_lieu.md/.docx  # [CHỈNH SỬA] Cập nhật bảng tickets & ticket_types, đổi DB chính MySQL 8
│   ├── 03..06_bao_cao_*.md/.docx           # [CHỈNH SỬA] Cập nhật công nghệ (MySQL 8, QR, PDF, Mail, GitHub Actions)
│   ├── SPEC.md / DEMO_SCRIPT.md / WORKLOG.md # [CHỈNH SỬA] Cập nhật kịch bản demo QR/Camera/PDF/Mailpit & nhật ký nhóm
│   ├── diagrams/                           # [CHỈNH SỬA] Bổ sung file nguồn .drawio và ảnh PNG mới
│   └── testing/test-cases.md & test-output.txt # [CHỈNH SỬA] Cập nhật kết quả test mới
├── resources/
│   └── views/
│       ├── components/                     # [TÙY CHỌN THÊM] Blade Components tái sử dụng nếu cần
│       ├── pdf/ticket.blade.php            # [THÊM MỚI] Template HTML/CSS in vé PDF (font Unicode + QR)
│       ├── emails/order-paid.blade.php     # [THÊM MỚI] Template email xác nhận đơn hàng & vé
│       ├── storefront/
│       │   ├── show.blade.php              # [CHỈNH SỬA] Hiển thị thêm zone, description cho loại vé
│       │   ├── orders/show.blade.php       # [CHỈNH SỬA] Hiển thị QR / nút tải PDF vé (khi đơn paid)
│       │   └── tickets/index.blade.php     # [CHỈNH SỬA] Hiển thị ảnh QR Code + nút "Tải vé PDF"
│       ├── organizer/
│       │   ├── events/show.blade.php       # [CHỈNH SỬA] Hiển thị zone, description trong danh sách loại vé
│       │   ├── ticket-types/create & edit  # [CHỈNH SỬA] Thêm input zone, description
│       │   └── checkin/form.blade.php      # [CHỈNH SỬA] Nhúng nút bật Camera quét QR (html5-qrcode) + hiển thị checkedInBy
│       └── admin/
│           └── orders/show.blade.php       # [CHỈNH SỬA] Hiển thị thêm thông tin người check-in vé (nếu đã dùng)
├── routes/
│   └── storefront.php                      # [CHỈNH SỬA] Thêm route tải vé PDF (my-tickets/{ticket}/pdf)
├── tests/
│   ├── Unit/QrTicketServiceTest.php        # [THÊM MỚI] Test tạo & xác thực chữ ký QR HMAC
│   └── Feature/
│       ├── AdminManagementTest.php         # [MỞ RỘNG] Test hủy sự kiện tự động hủy đơn pending & vé valid
│       ├── CheckinTest.php                 # [ĐÃ XONG CSDL + MỞ RỘNG] Test quét bằng QR payload hợp lệ & giả mạo
│       ├── OrderAndPaymentTest.php         # [ĐÃ XONG CSDL + MỞ RỘNG] Test gửi Mail::fake & tạo vé
│       └── TicketPdfTest.php               # [THÊM MỚI] Test tải PDF vé & chặn tải trái phép (403)
├── .env.example                            # [CHỈNH SỬA] Đổi mặc định sang MySQL 8, MAIL_MAILER=log, ghi chú Mailpit
├── composer.json                           # [CHỈNH SỬA] Thêm endroid/qr-code, barryvdh/laravel-dompdf
└── README.md                               # [CHỈNH SỬA] Cập nhật hướng dẫn MySQL 8, ext-gd, Mailpit, GitHub Actions
```

---

## PHẦN 3: PHÂN CÔNG CÔNG VIỆC CHO 3 NGƯỜI TRONG 1 THÁNG (4 TUẦN)

### Nguyên tắc phối hợp nhóm (GitHub Flow)
* **Mỗi người code trên nhánh riêng** (`feat/...`, `fix/...`, `chore/...`, `docs/...`), không đẩy trực tiếp lên nhánh chính (`main`/`master`).
* **Không đụng chéo file:** Chia rõ ràng theo phân hệ (`Quang`: Storefront + Email + MySQL/Git; `Định`: QR + PDF + Camera Check-in + Organizer; `Quý`: Admin + Enum + CI/CD + Tests + Sơ đồ `draw.io` & Báo cáo) để tránh *merge conflict*.
* **Quản lý trên GitHub Projects:** Mọi đầu việc tạo thành GitHub Issue, gán người phụ trách, PR ghi `Closes #<id>` và có ít nhất 1 thành viên khác review trước khi merge.

---

### Bảng phân công vai trò tổng thể

| Thành viên | Vai trò chính trong đợt nâng cấp | Mảng phụ trách chính |
| :--- | :--- | :--- |
| **Hồ Đăng Quang** *(Trưởng nhóm)* | **Kiến trúc, Storefront (QR/PDF/Zone), Email & Điều phối Git** | • Quản lý GitHub Repo, Branch Protection, GitHub Projects & review PR<br>• Cấu hình **MySQL 8 Community + DBeaver**, `.env.example` & kiểm thử dữ liệu mẫu trên MySQL<br>• Cập nhật Bootstrap UI phân hệ `storefront/` (hiển thị `zone`, `description`, ảnh QR trên vé, nút tải PDF)<br>• Xây dựng **Email (`OrderPaidMail`) + Mailpit** & cập nhật luồng `OrderController` + `README.md` + `DEMO_SCRIPT.md` |
| **Tống Xuân Định** | **Nghiệp vụ Vé QR, PDF, Camera Check-in & Organizer** | • Cài đặt & xây dựng **`QrTicketService` (`endroid/qr-code` + HMAC)** & **`TicketPdfService` (`barryvdh/laravel-dompdf`)**<br>• Xây dựng route & `TicketController@downloadPdf` (bảo vệ bằng `OrderPolicy`)<br>• Nâng cấp `TicketCheckinService` verify chữ ký QR + tích hợp **Camera quét QR (`html5-qrcode`)** vào `organizer/checkin/form.blade.php`<br>• Cập nhật form/view `organizer/ticket-types/` & `organizer/events/show` cho `zone`, `description` |
| **Nguyễn Hữu Quý** | **Admin, Đồng bộ Enum, CI/CD, Testing & Tài liệu / Sơ đồ** | • Thiết lập **GitHub Actions CI** (Pint + PHPUnit + MySQL 8)<br>• Hoàn thiện logic khi Admin **hủy sự kiện** (tự hủy đơn `pending` hoàn vé & hủy vé `valid`) + hiển thị người soát vé tại `admin/orders/show`<br>• Đồng bộ **Enum casting** (`EventStatus`, `OrderStatus`, `TicketStatus`, `UserRole`) trên Models<br>• Viết bổ sung **PHPUnit Tests** (QR, PDF, Mail, Admin cancel, MySQL) + vẽ lại bộ sơ đồ **`draw.io`** + cập nhật `docs/` & báo cáo Word |

---

### Lịch trình chi tiết theo 4 tuần

#### TUẦN 1: Chuẩn hóa Hạ tầng, GitHub, MySQL 8 & Service Lõi (QR / PDF / CI)
*Mục tiêu tuần 1: Cả 3 máy chạy ổn định trên MySQL 8, cài xong thư viện QR & PDF, GitHub Actions CI chạy xanh.*

* **Quang:**
  * Thiết lập Branch Protection cho nhánh chính, dựng bảng **GitHub Projects** và tạo toàn bộ Issues cho 4 tuần.
  * Cấu hình `.env.example` sang **MySQL 8** (`utf8mb4_unicode_ci`), kiểm tra chạy `php artisan migrate:fresh --seed` trên **MySQL 8 Community + DBeaver**.
  * *Nhánh:* `chore/mysql8-and-github-setup`
* **Định:**
  * Bật `extension=gd` trong PHP, cài `endroid/qr-code` và `barryvdh/laravel-dompdf`.
  * Xây dựng **`QrTicketService`** (tạo ảnh QR SVG/PNG base64 từ `qr_payload`, xác thực chữ ký HMAC) và **`TicketPdfService`** (khởi tạo mẫu [`resources/views/pdf/ticket.blade.php`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/resources/views/pdf/ticket.blade.php) hỗ trợ font tiếng Việt Unicode & bố cục `<table>`).
  * *Nhánh:* `feat/qr-and-pdf-services`
* **Quý:**
  * Tạo workflow **`.github/workflows/ci.yml`** (chạy `composer install`, `pint --test`, `php artisan test` với container service `mysql:8.0`).
  * Đồng bộ **Enum casting** (`EventStatus`, `OrderStatus`, `TicketStatus`, `UserRole`) lên các Model (`Event`, `Order`, `Ticket`, `User`) để loại bỏ hằng số/hàm lặp lại.
  * *Nhánh:* `chore/github-actions-and-enums`

> **Mốc kiểm tra cuối Tuần 1:** Cả 3 PR được merge vào nhánh chính; GitHub Actions chạy pass toàn bộ 37 test trên MySQL 8; cả 3 máy chạy mượt trên MySQL 8 Community.

---

#### TUẦN 2: Hoàn thiện Giao diện Bootstrap & Luồng Chức năng Mới
*Mục tiêu tuần 2: Hoàn thiện 100% tính năng mới trên giao diện Bootstrap cho cả 3 vai trò.*

* **Quang:**
  * Cập nhật giao diện [`storefront/show.blade.php`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/resources/views/storefront/show.blade.php) hiển thị `zone` (khu vực) và `description` (quyền lợi từng loại vé).
  * Cập nhật [`storefront/tickets/index.blade.php`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/resources/views/storefront/tickets/index.blade.php) & [`storefront/orders/show.blade.php`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/resources/views/storefront/orders/show.blade.php) hiển thị **ảnh QR Code** và nút **Tải vé PDF**.
  * Xây dựng **`OrderPaidMail`** (`resources/views/emails/order-paid.blade.php`) đính kèm PDF vé động, tích hợp vào `OrderController@pay` và kiểm thử thực tế qua **Mailpit**.
  * *Nhánh:* `feat/storefront-qr-pdf-and-email`
* **Định:**
  * Thêm route `GET /my-tickets/{ticket}/pdf` và `TicketController@downloadPdf` (kiểm tra quyền sở hữu qua `OrderPolicy`).
  * Nâng cấp `TicketCheckinService` & `CheckinController` nhận được cả mã nhập tay (`TKT-...`) lẫn chuỗi JSON `qr_payload` có verify `qr_signature` chống giả mạo.
  * Tải sẵn `html5-qrcode.min.js` vào `public/js/` và tích hợp **Camera quét QR** song song ô nhập tay vào [`organizer/checkin/form.blade.php`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/resources/views/organizer/checkin/form.blade.php), hiển thị người soát vé (`checkedInBy`).
  * Cập nhật `organizer/ticket-types/create.blade.php`, `edit.blade.php` và `organizer/events/show.blade.php` thêm trường `zone`, `description`.
  * *Nhánh:* `feat/camera-checkin-and-organizer-zone`
* **Quý:**
  * Hoàn thiện logic khi Admin **hủy sự kiện** tại [`Admin\EventController::cancel()`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/app/Http/Controllers/Admin/EventController.php#L63-L71) (trong transaction: tự hủy các đơn `pending` hoàn vé, cập nhật vé `valid` sang `cancelled`).
  * Cập nhật giao diện `admin/orders/show.blade.php` & `admin/events/show.blade.php` hiển thị thêm `zone`, `description` và thông tin nhân viên đã soát vé (`checkedInBy`).
  * *Nhánh:* `feat/admin-cancel-cascade-and-views`

> **Mốc kiểm tra cuối Tuần 2:** Mua vé → thanh toán giả lập → nhận email Mailpit có vé PDF đính kèm → xem QR trên web → tải vé PDF → bật camera quét QR check-in thành công (và chặn được QR sửa chữ ký/vé đã dùng).

---

#### TUẦN 3: Kiểm thử Tự động (PHPUnit), Bảo mật & Sơ đồ `draw.io`
*Mục tiêu tuần 3: Đạt độ phủ test cao trên MySQL 8, hoàn tất bộ sơ đồ thiết kế mới bằng `draw.io`.*

* **Quang:**
  * Rà soát giao diện toàn bộ phân hệ Storefront trên cả Desktop & Mobile, kiểm tra truy vấn tránh lỗi N+1 query (`checkedInBy`, `orderItems.tickets`).
  * Viết Feature Test kiểm tra luồng gửi Email (`Mail::fake()`) sau thanh toán và hiển thị `zone`/`description` trên Storefront.
  * Cập nhật [`README.md`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/README.md) đầy đủ (cài MySQL 8 Community/XAMPP/Laragon, bật `extension=gd`, Mailpit, GitHub Actions).
  * *Nhánh:* `test/mail-storefront-and-readme`
* **Định:**
  * Test thực tế quét QR bằng webcam/camera điện thoại qua `http://localhost:8000` với đầy đủ kịch bản lỗi (QR hợp lệ, QR sửa chữ ký, vé đã dùng, vé chưa thanh toán, vé của BTC khác).
  * Viết Unit Test (`QrTicketServiceTest`) & Feature Test (`TicketPdfTest`, mở rộng `CheckinTest` test payload QR & `checked_in_by`, test lưu `zone`/`description`).
  * Cập nhật [`docs/01_phan_tich_yeu_cau.md`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/docs/01_phan_tich_yeu_cau.md) và [`docs/SPEC.md`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/docs/SPEC.md) (bổ sung quy trình QR, Camera, PDF, Email và 5 cột DB mới).
  * *Nhánh:* `test/qr-pdf-checkin-and-srs`
* **Quý:**
  * Vẽ/chuyển đổi toàn bộ sơ đồ sang **`draw.io`** (`docs/diagrams/*.drawio` + xuất PNG): Use Case tổng quan & chi tiết, **ERD** (kèm 5 cột mới trên `tickets` & `ticket_types`), Sơ đồ chức năng, Activity đặt vé & Sequence thanh toán/QR/PDF.
  * Viết Feature Test cho logic Admin hủy sự kiện & test kiểm chứng trên MySQL 8.
  * Cập nhật [`docs/02_thiet_ke_co_so_du_lieu.md`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/docs/02_thiet_ke_co_so_du_lieu.md), [`docs/testing/test-cases.md`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/docs/testing/test-cases.md) và xuất lại [`test-output.txt`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/docs/testing/test-output.txt).
  * *Nhánh:* `docs/drawio-diagrams-and-admin-tests`

> **Mốc kiểm tra cuối Tuần 3:** Pint sạch 100%, toàn bộ PHPUnit tests pass 100% trên MySQL 8 & GitHub Actions, bộ sơ đồ `draw.io` khớp hoàn toàn với DB & code.

---

#### TUẦN 4: Rà soát Bảo mật, Hoàn thiện Báo cáo Word & Diễn tập Bảo vệ
*Mục tiêu tuần 4: Không còn lỗi vặt, đồng bộ tài liệu Word/Markdown, sẵn sàng demo và phản biện.*

* **Cả 3 thành viên cùng kiểm tra chéo (Bug Bash & Security Check):**
  * Kiểm tra toàn bộ form Blade có đủ `@csrf`, thông báo lỗi validation tiếng Việt rõ ràng, kiểm tra chặn IDOR (tải trộm PDF vé người khác trả 403, check-in chéo sự kiện, sửa loại vé sự kiện người khác).
  * Kiểm tra chạy offline hoàn toàn (khi `MAIL_MAILER=log`, không có Internet vẫn hiển thị QR từ `endroid/qr-code`, quét QR bằng `public/js/html5-qrcode.min.js` và tải PDF bình thường).
* **Quang:**
  * Cập nhật [`docs/DEMO_SCRIPT.md`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/docs/DEMO_SCRIPT.md) (bổ sung kịch bản demo quét QR bằng camera, tải vé PDF, kiểm tra email trong Mailpit, xem CI GitHub Actions).
  * Chủ trì chạy thử kịch bản demo 8–10 phút từ đầu đến cuối.
* **Định:**
  * Test cài đặt sạch từ con số 0 (`git clone` → `composer install` → `.env` MySQL → `migrate:fresh --seed` → `php artisan serve`) theo đúng hướng dẫn trong `README.md`.
* **Quý:**
  * Cập nhật [`docs/WORKLOG.md`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/docs/WORKLOG.md), các báo cáo [`docs/03_*`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/docs/03_bao_cao_tieu_chi_do_an.md) – [`docs/06_*`](file:///c:/Users/Quang/Desktop/DA_PMNM_NC/docs/06_bao_cao_lam_viec_nhom_va_bao_ve.md) và xuất lại các file `.docx` (kèm ảnh chụp GitHub Actions xanh, bảng GitHub Projects, lịch sử commit của cả 3 thành viên).

---

### Tiêu chí nghiệm thu cuối cùng trước khi nộp đồ án
1. **Chức năng & Giao diện:** Chạy mượt 100% trên Blade + Bootstrap 5.3, đủ luồng QR HMAC + Camera Check-in (`html5-qrcode` local) + PDF vé động + Email Mailpit/`log`, không lỗi khi demo offline.
2. **Cơ sở dữ liệu:** Migrate & Seed sạch trên **MySQL 8**, có đủ `qr_payload`, `qr_signature`, `checked_in_by`, `zone`, `description`.
3. **Chất lượng & CI:** `vendor/bin/pint --test` sạch, toàn bộ PHPUnit tests pass 100%, **GitHub Actions** báo xanh trên nhánh chính.
4. **Minh chứng nhóm:** Có lịch sử commit thật và Pull Request của cả **Quang, Định, Quý** trên GitHub, bảng **GitHub Projects** hoàn tất, và bộ tài liệu `docs/` + sơ đồ `draw.io` khớp hoàn toàn với mã nguồn.
