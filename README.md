# HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

> **Đồ án môn học**: Phần mềm Nguồn mở Nâng cao — Hệ thống Quản lý Bán vé Sự kiện trực tuyến  
> **Nhóm thực hiện**: **Nhóm 8 — Lớp 68PM2** (Hồ Đăng Quang - `0021568`, Tống Xuân Định - `0008268`, Nguyễn Hữu Quý - `0022968`)  
> **Framework**: Laravel 13 (PHP 8.3+)  
> **Frontend**: Laravel Blade + Bootstrap 5.3 CDN + Bootstrap Icons CDN (Zero-node / Không yêu cầu npm build)  
> **Cơ sở dữ liệu**: **MySQL 8.0 Community Server / DBeaver** (Mặc định) — Hỗ trợ XAMPP / Laragon & SQLite dự phòng  
> **Tính năng nâng cao**: Vé QR ký số HMAC-SHA256, Quét Camera Check-in offline, Xuất PDF động khổ A5, Email Mailpit, GitHub Actions CI  

---

## MỤC LỤC
1. [Giới thiệu tổng quan](#1-giới-thiệu-tổng-quan)
2. [Yêu cầu hệ thống & PHP Extensions](#2-yêu-cầu-hệ-thống--php-extensions)
3. [Hướng dẫn cài đặt và khởi chạy (MySQL 8 Mặc định)](#3-hướng-dẫn-cài-đặt-và-khởi-chạy-mysql-8-mặc-định)
4. [Tùy chọn cơ sở dữ liệu (MySQL 8 / XAMPP / SQLite)](#4-tùy-chọn-cơ-sở-dữ-liệu-mysql-8--xampp--sqlite)
5. [Cấu hình Email thông báo & Mailpit](#5-cấu-hình-email-thông-báo--mailpit)
6. [Cổng soát vé Camera QR & Kiểm thử ngoại tuyến](#6-cổng-soát-vé-camera-qr--kiểm-thử-ngoại-tuyến)
7. [Danh sách tài khoản dùng thử (Demo Accounts)](#7-danh-sách-tài-khoản-dùng-thử-demo-accounts)
8. [Cấu trúc thư mục dự án](#8-cấu-trúc-thư-mục-dự-án)
9. [Kiểm thử tự động (PHPUnit - 89 Tests) & GitHub Actions CI](#9-kiểm-thử-tự-động-phpunit---89-tests--github-actions-ci)
10. [Tác vụ định kỳ (Background Command & Scheduler)](#10-tác-vụ-định-kỳ-background-command--scheduler)
11. [Hệ thống tài liệu dự án](#11-hệ-thống-tài-liệu-dự-án)

---

## 1. GIỚI THIỆU TỔNG QUAN

Hệ thống Quản lý Bán vé Sự kiện là ứng dụng web phục vụ 4 nhóm người dùng chính:
- **Khách vãng lai (Guest)**: Xem danh sách sự kiện đang mở bán, tìm kiếm theo từ khóa (`q`), lọc theo danh mục/thành phố/khoảng ngày, sắp xếp theo thời gian hoặc giá vé, xem chi tiết sự kiện kèm khu vực (`zone`) & quyền lợi (`description`), đăng ký/đăng nhập.
- **Khách hàng (Customer)**: Đặt vé giữ chỗ 15 phút an toàn với cơ chế khóa bi quan (*pessimistic locking*), thanh toán giả lập (Mock Payment: `bank_transfer`, `momo`, `card`), nhận email xác nhận kèm vé PDF đính kèm, nhận vé điện tử kèm **mã QR ký số HMAC-SHA256**, tải vé PDF on-the-fly, xem lịch sử đơn hàng và tự hủy đơn khi đang `pending`.
- **Ban tổ chức (Organizer)**: Dashboard thống kê doanh thu và số vé, quản lý vòng đời sự kiện (Tạo nháp -> Thiết lập vé & zone/description -> Gửi duyệt), theo dõi đơn hàng, và ứng dụng **Check-in vé tại cổng bằng Camera quét mã QR** hoặc nhập tay, hiển thị thông tin nhân viên soát vé.
- **Quản trị viên (Admin)**: Dashboard toàn hệ thống, quản lý danh mục và địa điểm (ràng buộc `RESTRICT`), quản lý người dùng (khóa/mở khóa tài khoản, phân quyền), kiểm duyệt (`approve` / `reject` / `cancel` có cascade tự hủy đơn `pending` hoàn vé và hủy vé `valid`), tra cứu toàn bộ đơn hàng và nhân viên soát vé.

### Điểm nổi bật kỹ thuật:
- **An toàn tồn kho vé**: Áp dụng `lockForUpdate()` kết hợp `DB::transaction()` trong `OrderService` ngăn chặn triệt để tình trạng *race condition* / *overselling* khi nhiều khách hàng cùng đặt vé đồng thời.
- **Bảo mật mã QR bằng HMAC-SHA256**: Payload QR dạng JSON `{"v":1,"ticket_code":"TKT-..."}` được ký bằng `hash_hmac('sha256', payload, APP_KEY)`. Mọi hành vi tự ý sửa đổi mã vé đều bị phát hiện và từ chối check-in ngay lập tức.
- **Xuất vé PDF động khổ A5**: Sinh trực tiếp trong bộ nhớ RAM qua `barryvdh/laravel-dompdf`, font tiếng Việt DejaVu Sans chuẩn Unicode, bảo vệ quyền tải qua `OrderPolicy`.
- **Gửi Email tự động qua Mailpit / Log**: Sau khi thanh toán thành công, `OrderPaidMail` gửi email thông báo đính kèm toàn bộ vé PDF tạo động trong RAM.
- **Quét Camera offline 100%**: Sử dụng thư viện `html5-qrcode.min.js` lưu cục bộ, tự cấp quyền webcam trên `http://localhost:8000`, có ô nhập tay dự phòng an toàn.
- **Tự động hủy đơn quá hạn**: Lệnh `php artisan orders:expire-pending` (đã đăng ký chạy mỗi phút trong `routes/console.php`) kết hợp lazy-check khi xem hoặc thanh toán đơn hàng.
- **CI/CD hiện đại**: Tích hợp GitHub Actions chạy tự động trên container `mysql:8.0`, kiểm tra định dạng code với Laravel Pint và chạy bộ test PHPUnit.

---

## 2. YÊU CẦU HỆ THỐNG & PHP EXTENSIONS

- **PHP**: Phiên bản `>= 8.3` (Đã kiểm thử và chạy ổn định trên **PHP 8.5.9**).
- **PHP Extensions bắt buộc**:
  - `pdo_mysql` (Kết nối CSDL MySQL 8).
  - `gd` (**BẮT BUỘC**: Dùng để render ảnh QR Code `endroid/qr-code` và nhúng vào vé PDF `dompdf`).
  - `pdo_sqlite` (Tùy chọn chạy SQLite dự phòng).
  - `mbstring`, `openssl`, `fileinfo`, `zip`, `xml`, `ctype`, `tokenizer`.

> [!IMPORTANT]
> **Cách bật extension `gd` và `pdo_mysql` trong `php.ini`:**
> 1. Mở file `php.ini` (ví dụ: `C:\php\php.ini` hoặc trong thư mục cài PHP của XAMPP/Laragon).
> 2. Tìm các dòng sau và bỏ dấu chấm phẩy `;` ở đầu dòng:
>    ```ini
>    extension=gd
>    extension=pdo_mysql
>    extension=fileinfo
>    extension=mbstring
>    ```
> 3. Lưu file và khởi động lại terminal hoặc web server.

- **Composer**: Phiên bản `2.x`.
- **Node.js / npm**: **Không yêu cầu** (Giao diện dùng Blade + Bootstrap 5.3 & Bootstrap Icons qua CDN; camera QR dùng JS local; không cần build asset).
- **Hệ điều hành**: Windows 10/11, macOS hoặc Linux.

---

## 3. HƯỚNG DẪN CÀI ĐẶT VÀ KHỞI CHẠY (MYSQL 8 MẶC ĐỊNH)

### Các bước cài đặt chuẩn trên Windows (PowerShell / CMD):

```powershell
# 1. Điều hướng vào thư mục dự án
cd C:\Users\Quang\Desktop\DA-PMNMNC

# 2. Cài đặt các gói phụ thuộc qua Composer
composer install

# 3. Tạo file cấu hình môi trường từ mẫu (.env.example đã cấu hình sẵn MySQL 8)
copy .env.example .env

# 4. Khởi tạo Application Key và liên kết thư mục lưu trữ storage
php artisan key:generate
php artisan storage:link

# 5. Tạo Database trên MySQL 8 (qua DBeaver, MySQL CLI hoặc phpMyAdmin):
# CREATE DATABASE event_ticket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 6. Chạy Migration và nạp dữ liệu mẫu ban đầu (Seeders)
php artisan migrate:fresh --seed --force

# 7. Khởi động Web Server cục bộ
php artisan serve
```

Mở trình duyệt web và truy cập: **[http://localhost:8000](http://localhost:8000)**

---

## 4. TÙY CHỌN CƠ SỞ DỮ LIỆU (MYSQL 8 / XAMPP / SQLITE)

Dự án hỗ trợ linh hoạt 3 phương án cơ sở dữ liệu:

### Phương án A: MySQL 8 Community Server / DBeaver (Khuyên dùng)
File `.env` mặc định đã thiết lập:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=event_ticket
DB_USERNAME=root
DB_PASSWORD=
```
*(Nếu MySQL của bạn có mật khẩu root, hãy điền vào `DB_PASSWORD`)*.

### Phương án B: XAMPP / Laragon
1. Bật **MySQL** trên XAMPP Control Panel hoặc Laragon.
2. Mở phpMyAdmin hoặc DBeaver, tạo database `event_ticket` với collation `utf8mb4_unicode_ci`.
3. Giữ nguyên cấu hình MySQL trong `.env` và chạy `php artisan migrate:fresh --seed --force`.

### Phương án C: SQLite dự phòng (Chạy tức thì không cần cài DB server)
Khi chấm bài ở môi trường không có sẵn MySQL, bạn chỉ cần sửa 2 dòng trong `.env`:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```
Sau đó tạo file rỗng và migrate:
```powershell
New-Item -ItemType File -Force -Path database/database.sqlite
php artisan migrate:fresh --seed --force
```

---

## 5. CẤU HÌNH EMAIL THÔNG BÁO & MAILPIT

Khi khách hàng thanh toán thành công, hệ thống tự động kích hoạt `OrderPaidMail` gửi email xác nhận kèm toàn bộ file vé PDF tạo động trong bộ nhớ.

### Chế độ xem trực quan với Mailpit (Khuyên dùng khi demo / test):
1. Tải công cụ Mailpit gọn nhẹ (chỉ 1 file `.exe` không cần cài đặt): [Mailpit Releases trên GitHub](https://github.com/axllent/mailpit/releases).
2. Chạy `mailpit.exe` trong cửa sổ lệnh riêng (lắng nghe SMTP cổng `1025` và giao diện web cổng `8025`).
3. Cấu hình trong `.env`:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=127.0.0.1
   MAIL_PORT=1025
   MAIL_FROM_ADDRESS="no-reply@eventticket.local"
   MAIL_FROM_NAME="${APP_NAME}"
   ```
4. Truy cập giao diện xem email tại: **[http://localhost:8025](http://localhost:8025)** để xem email gửi đến, click tải file PDF vé đính kèm trực tiếp.

### Chế độ mặc định an toàn khi demo Offline:
Trong `.env.example`, hệ thống để sẵn `MAIL_MAILER=log`. Khi thanh toán, nội dung email sẽ được ghi an toàn vào `storage/logs/laravel.log`, hoàn toàn không gây lỗi kể cả khi máy không bật server mail.

---

## 6. CỔNG SOÁT VÉ CAMERA QR & KIỂM THỬ NGOẠI TUYẾN

Ban tổ chức truy cập chức năng soát vé tại menu **Soát vé** (`/organizer/checkin`):
1. **Quét bằng Camera:**
   - Bấm nút **"Bật camera"** $\rightarrow$ Trình duyệt sẽ yêu cầu cấp quyền webcam.
   - Nhờ chạy qua tên miền cục bộ `http://localhost:8000`, trình duyệt mặc định coi đây là *Secure Context* và cho phép mở camera mà không cần HTTPS.
   - Khi đưa mã QR của vé trước camera, thư viện `html5-qrcode.min.js` (lưu tại `public/js/`) giải mã chuỗi JSON và tự động gửi kiểm tra.
2. **Nhập mã bằng tay dự phòng:**
   - Nếu máy tính không có webcam hoặc camera bị che, nhân viên soát vé nhập trực tiếp mã `TKT-XXXXXXXXXX` vào ô nhập liệu phía trên và nhấn **"Xác Nhận Check-in"**.
3. **Cơ chế xác thực 6 bước tại Backend:**
   - (1) Kiểm tra mã vé tồn tại.
   - (2) Kiểm tra chữ ký số HMAC-SHA256 (nếu gửi từ camera QR).
   - (3) Kiểm tra đơn hàng đã thanh toán (`PAID`).
   - (4) Kiểm tra quyền sở hữu sự kiện của Organizer.
   - (5) Kiểm tra vé chưa bị hủy.
   - (6) Kiểm tra vé chưa từng check-in trước đó (chống tái sử dụng vé).

---

## 7. DANH SÁCH TÀI KHOẢN DÙNG THỬ (DEMO ACCOUNTS)

Hệ thống đã chuẩn bị sẵn 8 tài khoản mẫu trong [`database/seeders/UserSeeder.php`](./database/seeders/UserSeeder.php).  
**Mật khẩu dùng chung cho tất cả tài khoản:** `password`

| STT | Vai trò (Role) | Email đăng nhập | Tên hiển thị | Trạng thái | Mục đích kiểm thử chính |
| :---: | :--- | :--- | :--- | :---: | :--- |
| 1 | **Admin** | `admin@example.com` | Quản Trị Viên Hệ Thống | Hoạt động | Quản lý danh mục, địa điểm, duyệt sự kiện, hủy sự kiện cascade, khóa/mở user |
| 2 | **Organizer 1** | `organizer1@example.com` | Ban Tổ Chức Sao Mai | Hoạt động | Tạo sự kiện, quản lý loại vé có zone & description, xem đơn hàng, check-in vé bằng camera |
| 3 | **Organizer 2** | `organizer2@example.com` | Công Ty Âm Nhạc Trẻ | Hoạt động | Kiểm thử phân quyền cô lập dữ liệu giữa các Ban tổ chức |
| 4 | **Customer 1** | `customer1@example.com` | Nguyễn Văn An | Hoạt động | Đặt vé, thanh toán giả lập, xem mã QR, tải vé PDF, nhận email |
| 5 | **Customer 2** | `customer2@example.com` | Trần Thị Bình | Hoạt động | Đặt vé, kiểm tra đơn đã thanh toán |
| 6 | **Customer 3** | `customer3@example.com` | Lê Hoàng Cường | Hoạt động | Thanh toán hoặc hủy đơn hàng đang `pending` |
| 7 | **Customer 4** | `customer4@example.com` | Phạm Thúy Dung | Hoạt động | Đặt vé, kiểm thử luồng người mua |
| 8 | **Customer 5** | `customer5@example.com` | Hoàng Minh Đức | Hoạt động | Đặt vé, kiểm thử khóa tài khoản từ trang Admin |

*(Lưu ý: Để kiểm thử tính năng chặn tài khoản bị khóa bởi Middleware `CheckActive`, đăng nhập bằng `admin@example.com`, vào mục **Quản Lý Người Dùng** khóa một tài khoản bất kỳ, sau đó đăng nhập lại bằng tài khoản đó.)*

> **Dữ liệu thật từ Ticketbox:** `TicketboxEventSeeder` nạp thêm 14 sự kiện thật (kèm ảnh banner) và 14 tài khoản ban tổ chức tương ứng (`{ten-ban-to-chuc}@ticketbox.local`), mật khẩu `password`.

---

## 8. CẤU TRÚC THƯ MỤC DỰ ÁN

```text
DA_PMNM_NC/
├── .github/
│   └── workflows/
│       └── ci.yml                     # Workflow CI GitHub Actions (MySQL 8, Pint, PHPUnit)
├── app/
│   ├── Console/Commands/              # ExpirePendingOrders (orders:expire-pending)
│   ├── Enums/                         # EventStatus, OrderStatus, TicketStatus, UserRole
│   ├── Exceptions/                    # BusinessException
│   ├── Http/
│   │   ├── Controllers/               # Auth, Storefront, Organizer, Admin, ProfileController
│   │   ├── Middleware/                # CheckRole, CheckActive
│   │   └── Requests/                  # FormRequests chuẩn hóa validation tiếng Việt
│   ├── Mail/
│   │   └── OrderPaidMail.php          # Mailable gửi xác nhận đơn & đính kèm PDF vé động
│   ├── Models/                        # 8 Eloquent Models đồng bộ Enum Casts & Relationships
│   ├── Policies/                      # EventPolicy, TicketTypePolicy, OrderPolicy
│   └── Services/
│       ├── OrderService.php           # DB transaction + lockForUpdate đặt/thanh toán vé
│       ├── TicketCheckinService.php   # DB transaction + verify chữ ký QR HMAC + ghi checked_in_by
│       ├── QrTicketService.php        # Sinh mã QR SVG/PNG base64 và verify chữ ký số
│       └── TicketPdfService.php       # Render vé PDF khổ A5 on-the-fly qua DomPDF
├── bootstrap/app.php                  # Cấu hình Routing, Middleware alias
├── database/
│   ├── data/ticketbox/                # Dữ liệu 14 sự kiện thật Ticketbox
│   ├── factories/                     # Factory sinh dữ liệu test
│   ├── migrations/                    # 12 Migrations (bao gồm cột QR, checked_in_by, zone, description)
│   └── seeders/                       # Seeders nạp tài khoản mẫu và sự kiện
├── docs/                              # Tài liệu phân tích yêu cầu, thiết kế CSDL, báo cáo rubric
│   ├── diagrams/                      # Thư viện sơ đồ hệ thống (PlantUML, PNG và Draw.io)
│   └── testing/                       # Bảng test cases & log kết quả chạy thực tế
├── public/
│   └── js/
│       └── html5-qrcode.min.js        # Thư viện quét camera QR lưu local chạy offline
├── resources/views/
│   ├── emails/order-paid.blade.php    # Template email xác nhận đơn hàng
│   ├── pdf/ticket.blade.php           # Template in vé PDF khổ A5 font Unicode
│   ├── layouts/ (app, panel)          # Master Layouts chuẩn Bootstrap 5.3
│   └── storefront / organizer / admin # Giao diện Blade phân hệ theo từng vai trò
├── routes/                            # auth.php, storefront.php, organizer.php, admin.php
└── tests/
    ├── Unit/                          # Unit tests (DomainModelsTest, QrTicketServiceTest)
    └── Feature/                       # Feature tests (Auth, Admin, Organizer, Checkin, TicketPdf,...)
```

---

## 9. KIỂM THỬ TỰ ĐỘNG (PHPUNIT - 89 TESTS) & GITHUB ACTIONS CI

### 9.1 Bộ kiểm thử tự động toàn diện (89 Tests — 333 Assertions)

Dự án đạt độ phủ kiểm thử cao, kiểm chứng tự động toàn bộ các luồng nghiệp vụ cốt lõi:

| Nhóm kiểm thử (Test Suite) | Loại | Số test | Số assertion | Nội dung kiểm chứng chính |
| :--- | :---: | :---: | :---: | :--- |
| `DomainModelsTest` | Unit | 2 | 29 | Logic accessor `remaining`, ánh xạ Enum và helper vai trò User. |
| `QrTicketServiceTest` | Unit | 13 | 27 | Tạo ảnh SVG/PNG QR, tạo chữ ký số HMAC-SHA256, phát hiện payload giả mạo hoặc đổi `APP_KEY`. |
| `AuthTest` | Feature | 6 | 26 | Đăng ký, đăng nhập, sai mật khẩu, chặn user bị khóa, cập nhật profile, đăng xuất. |
| `AuthorizationTest` | Feature | 5 | 8 | Phân quyền vai trò, chặn truy cập chéo IDOR, chặn admin tự khóa/hạ quyền. |
| `StorefrontTest` | Feature | 5 | 14 | Duyệt sự kiện, tìm kiếm từ khóa, lọc danh mục/thành phố, hiển thị `zone`/`description`, 404 sự kiện nháp. |
| `OrderAndPaymentTest` | Feature | 12 | 47 | Đặt vé giữ chỗ 15p (`lockForUpdate`), thanh toán giả lập, `Mail::fake()`, hiển thị QR & nút tải PDF, chống N+1 query ở `orders.show`, hủy đơn hoàn vé, tự động hủy đơn quá hạn. |
| `OrganizerTicketTypeTest` | Feature | 7 | 26 | Form tạo/sửa loại vé hỗ trợ `zone` & `description`, kiểm tra độ dài & phân quyền sửa xóa, hiển thị trong trang sự kiện BTC. |
| `CheckinTest` | Feature | 19 | 61 | Soát vé 6 bước bằng mã tay & JSON QR có chữ ký HMAC, chặn mã giả mạo/sai cú pháp/hủy/hết hạn/chéo BTC, quét camera offline, hiển thị nhân viên soát vé. |
| `TicketPdfTest` | Feature | 9 | 27 | Render PDF trong RAM không ghi đĩa, font tiếng Việt Unicode, tên file chuẩn mã vé, kiểm tra quyền qua `OrderPolicy` (chặn tải trộm vé). |
| `AdminManagementTest` | Feature | 11 | 68 | Phê duyệt/từ chối sự kiện, **Admin hủy sự kiện cascade** tự hủy đơn `pending` hoàn vé & hủy vé `valid`, hiển thị người soát vé, CRUD danh mục/địa điểm ràng buộc `RESTRICT`. |
| **TỔNG CỘNG** | — | **89 tests** | **333 assertions** | **Passed 100% (0 failures, 0 errors)** |

Chạy toàn bộ kiểm thử bằng lệnh:
```bash
php artisan test
```
Hoặc xem tên chi tiết từng ca kiểm thử:
```bash
php vendor/bin/phpunit --testdox
```

Kiểm tra định dạng code chuẩn Laravel:
```bash
vendor/bin/pint --test
```

### 9.2 Tích hợp Tự động hóa CI (GitHub Actions)
File cấu hình [`.github/workflows/ci.yml`](./.github/workflows/ci.yml) được kích hoạt tự động mỗi khi có thao tác `push` hoặc `pull_request` vào các nhánh `main`, `master`, `develop`:
- Khởi tạo service container **MySQL 8.0** trên môi trường Ubuntu.
- Cài đặt môi trường PHP 8.5 cùng các extension (`pdo_mysql`, `gd`,...).
- Chạy kiểm tra chuẩn định dạng code `vendor/bin/pint --test`.
- Chạy kiểm tra thực thi `php artisan migrate:fresh --seed` trên MySQL 8 thật.
- Chạy toàn bộ 89 test case qua `php artisan test`.

---

## 10. TÁC VỤ ĐỊNH KỲ (BACKGROUND COMMAND & SCHEDULER)

### 10.1 Lệnh quét hủy đơn hàng quá hạn giữ chỗ 15 phút
```bash
php artisan orders:expire-pending
```

### 10.2 Chạy Scheduler tự động mỗi phút trên Local
Lệnh `orders:expire-pending` đã được đăng ký định kỳ mỗi phút trong [`routes/console.php`](./routes/console.php):
```bash
php artisan schedule:work
```

---

## 11. HỆ THỐNG TÀI LIỆU DỰ ÁN

1. **[Tài liệu Phân tích Yêu cầu (SRS)](./docs/01_phan_tich_yeu_cau.md)**
2. **[Tài liệu Thiết kế Cơ sở Dữ liệu](./docs/02_thiet_ke_co_so_du_lieu.md)** ([File Word](./docs/02_thiet_ke_co_so_du_lieu.docx))
3. **[Báo cáo Tiêu chí Đồ án - Mục 1 & 2](./docs/03_bao_cao_tieu_chi_do_an.md)** ([File Word](./docs/03_bao_cao_tieu_chi_do_an.docx))
4. **[Báo cáo Xây dựng Chức năng - Mục 3](./docs/04_bao_cao_xay_dung_chuc_nang.md)** ([File Word](./docs/04_bao_cao_xay_dung_chuc_nang.docx))
5. **[Báo cáo Kiểm thử, Bảo mật & Triển khai - Mục 4 & 5](./docs/05_bao_cao_kiem_thu_bao_mat_trien_khai.md)** ([File Word](./docs/05_bao_cao_kiem_thu_bao_mat_trien_khai.docx))
6. **[Báo cáo Làm việc nhóm & Bảo vệ - Mục 6 & 7](./docs/06_bao_cao_lam_viec_nhom_va_bao_ve.md)** ([File Word](./docs/06_bao_cao_lam_viec_nhom_va_bao_ve.docx))
7. **[Kế hoạch nâng cấp 1 tháng (4 tuần)](./KE_HOACH_NANG_CAP_1_THANG.md)**
8. **[Kịch bản Thuyết trình & Phản biện](./docs/DEMO_SCRIPT.md)**
9. **[Thư viện Sơ đồ Thiết kế (PlantUML, PNG & Draw.io)](./docs/diagrams/README.md)** ([Bộ Prompt UML cho Draw.io](./docs/diagrams/DRAWIO_UML_PROMPTS.md))
10. **[Tài liệu Kiểm thử Phần mềm](./docs/testing/test-cases.md)** (89 tests / 333 assertions)
11. **[Nhật ký Dự án & Phân công Công việc](./docs/WORKLOG.md)**
