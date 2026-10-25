# HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

> **Đồ án môn học**: Hệ thống Quản lý Bán vé Sự kiện trực tuyến  
> **Framework**: Laravel 13 (PHP 8.3+)  
> **Frontend**: Laravel Blade + Bootstrap 5.3 CDN + Bootstrap Icons CDN (Zero-node / Không yêu cầu npm build)  
> **Cơ sở dữ liệu**: SQLite (Mặc định - Chạy ngay không cần cài đặt DB server) / Tùy chọn MySQL (XAMPP, Laragon, Docker)

---

## MỤC LỤC
1. [Giới thiệu tổng quan](#1-giới-thiệu-tổng-quan)
2. [Yêu cầu hệ thống](#2-yêu-cầu-hệ-thống)
3. [Hướng dẫn cài đặt và khởi chạy](#3-hướng-dẫn-cài-đặt-và-khởi-chạy)
   - [Khởi chạy nhanh trên Windows (PowerShell / CMD)](#31-khoi-chay-nhanh-tren-windows-powershell--cmd)
   - [Khởi chạy trên Linux / macOS](#32-khoi-chay-tren-linux--macos)
4. [Hướng dẫn chuyển đổi sang MySQL (XAMPP / Laragon)](#4-hướng-dẫn-chuyển-đổi-sang-mysql-xampp--laragon)
5. [Danh sách tài khoản dùng thử (Demo Accounts)](#5-danh-sách-tài-khoản-dùng-thử-demo-accounts)
6. [Cấu trúc thư mục dự án](#6-cấu-trúc-thư-mục-dự-án)
7. [Hướng dẫn chạy kiểm thử tự động (Automated Tests)](#7-hướng-dẫn-chạy-kiểm-thử-tự-động-automated-tests)
8. [Tác vụ định kỳ (Background Command & Scheduler)](#8-tác-vụ-định-kỳ-background-command--scheduler)
9. [Hệ thống tài liệu dự án](#9-hệ-thống-tài-liệu-dự-án)

---

## 1. GIỚI THIỆU TỔNG QUAN

Hệ thống Quản lý Bán vé Sự kiện là ứng dụng web toàn diện phục vụ 4 nhóm người dùng chính:
- **Khách vãng lai (Guest)**: Xem danh sách sự kiện đang diễn ra, tìm kiếm theo từ khóa/danh mục/địa điểm, xem chi tiết sự kiện và các hạng vé, đăng ký/đăng nhập.
- **Khách hàng (Customer)**: Đặt vé giữ chỗ 15 phút an toàn với cơ chế khóa bi quan (*pessimistic locking*), thanh toán giả lập (Mock Payment Gateway), nhận vé điện tử kèm mã tra cứu và QR Code bảo mật, xem lịch sử đơn hàng.
- **Ban tổ chức (Organizer)**: Dashboard thống kê doanh thu, quản lý vòng đời sự kiện (Tạo nháp -> Xuất bản -> Hủy), quản lý loại vé và số lượng, theo dõi đơn hàng theo sự kiện, ứng dụng Check-in vé tại cổng kiểm soát với 5 bước xác thực chặt chẽ chống gian lận.
- **Quản trị viên (Admin)**: Dashboard toàn hệ thống (Doanh thu tổng, tăng trưởng đơn hàng, sự kiện mới), quản lý danh mục sự kiện, quản lý địa điểm tổ chức, quản lý người dùng (Khóa/Mở khóa tài khoản, phân quyền), kiểm duyệt và can thiệp trạng thái sự kiện/đơn hàng.

### Điểm nổi bật kỹ thuật:
- **An toàn tồn kho vé**: Áp dụng `lockForUpdate()` kết hợp `DB::transaction()` ngăn chặn 100% tình trạng *race condition* / *overselling* khi nhiều khách hàng cùng đặt vé đồng thời.
- **Tự động hủy đơn quá hạn**: Command `php artisan orders:expire-pending` hoàn trả số lượng vé về kho ngay khi hết hạn 15 phút.
- **Bảo mật đa tầng**: FormRequest validation tiếng Việt chuẩn hóa, Authorization Policy kiểm tra quyền sở hữu (Organizer chỉ quản lý sự kiện của chính mình), Middleware chặn truy cập tài khoản bị khóa ngay lập tức.
- **Kiến trúc rõ ràng**: Tách biệt 4 phân hệ định tuyến độc lập (`auth.php`, `storefront.php`, `organizer.php`, `admin.php`) với 62 routes chuẩn RESTful.

---

## 2. YÊU CẦU HỆ THỐNG

- **PHP**: Phiên bản `>= 8.2` (Khuyến nghị **PHP 8.3**)
  - Các extension PHP bắt buộc: `pdo`, `pdo_sqlite` (cho SQLite mặc định), `pdo_mysql` (nếu dùng MySQL), `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`.
- **Composer**: Phiên bản `2.x`
- **Node.js / npm**: **KHÔNG YÊU CẦU** (Giao diện dùng hoàn toàn Bootstrap 5.3 & Bootstrap Icons qua CDN chính hãng, không cần build asset).
- **Hệ điều hành**: Windows 10/11, macOS, hoặc Linux (Ubuntu, Debian, Fedora, CentOS,...).

---

## 3. HƯỚNG DẪN CÀI ĐẶT VÀ KHỞI CHẠY

### 3.1 Khởi chạy nhanh trên Windows (PowerShell / CMD)

Mặc định dự án đã được cấu hình sẵn **SQLite**, bạn không cần cài thêm MySQL hay Apache:

```powershell
# 1. Điều hướng vào thư mục dự án
cd d:\NMNC\event-ticket

# 2. Cài đặt các thư viện PHP qua Composer (nếu chưa cài)
composer install

# 3. Tạo file cấu hình môi trường từ mẫu
copy .env.example .env

# 4. Tạo file cơ sở dữ liệu SQLite trống (nếu chưa có)
New-Item -ItemType File -Force -Path database/database.sqlite

# 5. Tạo Application Key
php artisan key:generate

# 6. Chạy Migration và nạp dữ liệu mẫu ban đầu (Seeder)
php artisan migrate:fresh --seed --force

# 7. Khởi động Web Server cục bộ
php artisan serve
```

👉 Mở trình duyệt web và truy cập địa chỉ: **[http://localhost:8000](http://localhost:8000)**

---

### 3.2 Khởi chạy trên Linux / macOS

```bash
# 1. Điều hướng vào thư mục dự án
cd /path/to/event-ticket

# 2. Cài đặt các gói phụ thuộc
composer install

# 3. Copy file môi trường và tạo file SQLite
cp .env.example .env
touch database/database.sqlite

# 4. Phân quyền thư mục lưu trữ (Storage & Cache)
chmod -R 775 storage bootstrap/cache

# 5. Sinh khóa ứng dụng
php artisan key:generate

# 6. Khởi tạo cấu trúc bảng và nạp dữ liệu mẫu
php artisan migrate:fresh --seed --force

# 7. Khởi chạy server
php artisan serve
```

---

## 4. HƯỚNG DẪN CHUYỂN ĐỔI SANG MYSQL (XAMPP / LARAGON)

Nếu bạn muốn trình chiếu hoặc chạy trên hệ quản trị cơ sở dữ liệu **MySQL** (ví dụ qua XAMPP hoặc Laragon):

1. **Khởi động MySQL Service** trên XAMPP Control Panel hoặc Laragon.
2. Mở phpMyAdmin hoặc terminal MySQL, tạo mới một Database trống có tên là `event_ticket` với mã hóa `utf8mb4_unicode_ci`:
   ```sql
   CREATE DATABASE event_ticket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Mở file `.env` trong thư mục gốc dự án và điều chỉnh các dòng cấu hình Database như sau:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=event_ticket
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. Chạy lệnh migrate và nạp dữ liệu vào MySQL:
   ```bash
   php artisan migrate:fresh --seed --force
   ```
5. Khởi động lại `php artisan serve` và trải nghiệm.

---

## 5. DANH SÁCH TÀI KHOẢN DÙNG THỬ (DEMO ACCOUNTS)

Hệ thống đã chuẩn bị sẵn các tài khoản mẫu cho từng vai trò trong `database/seeders/UserSeeder.php`.  
**Mật khẩu dùng chung cho tất cả các tài khoản:** `password`

| STT | Vai trò (Role) | Email đăng nhập | Tên hiển thị | Trạng thái | Mục đích kiểm thử chính |
| :---: | :--- | :--- | :--- | :---: | :--- |
| 1 | **Quản trị viên (Admin)** | `admin@eventhub.vn` | Quản Trị Viên Hệ Thống | Hoạt động | Quản lý danh mục, địa điểm, duyệt sự kiện, mở khóa user |
| 2 | **Ban tổ chức (Organizer 1)** | `organizer@eventhub.vn` | Công Ty Sự Kiện Sunrise | Hoạt động | Tạo sự kiện, quản lý vé, xem doanh thu, check-in vé cổng |
| 3 | **Ban tổ chức (Organizer 2)** | `organizer2@eventhub.vn` | Công Ty Âm Nhạc Star Galaxy | Hoạt động | Kiểm thử phân quyền cô lập dữ liệu (không xem được sự kiện của Sunrise) |
| 4 | **Khách hàng 1 (Customer)** | `customer@gmail.com` | Nguyễn Văn Khách | Hoạt động | Đặt vé, thanh toán giả lập, tra cứu vé cá nhân |
| 5 | **Khách hàng 2 (Customer)** | `customer2@gmail.com` | Trần Thị Mua Vé | Hoạt động | Mua vé, kiểm tra đơn hàng |
| 6 | **Khách hàng 3 (Customer)** | `customer3@gmail.com` | Lê Hoàng Long | Hoạt động | Mua vé sự kiện hội thảo / workshop |
| 7 | **Khách hàng bị khóa (Blocked)**| `blocked@gmail.com` | Phạm Văn Bị Khóa | **Bị khóa** | Kiểm tra Middleware `CheckActive`: Tự động văng đăng nhập và báo lỗi |
| 8 | **Ban tổ chức bị khóa (Blocked)**| `organizer_blocked@eventhub.vn` | Công Ty Sự Kiện Cấm | **Bị khóa** | Kiểm tra chặn quyền truy cập ban tổ chức vi phạm |

---

## 6. CẤU TRÚC THƯ MỤC DỰ ÁN

```text
d:\NMNC\event-ticket\
├── app/
│   ├── Enums/                     # Enum định nghĩa vai trò và trạng thái
│   │   ├── EventStatus.php        # DRAFT, PUBLISHED, CANCELLED, COMPLETED
│   │   ├── OrderStatus.php        # PENDING, PAID, CANCELLED, REFUNDED
│   │   ├── TicketStatus.php       # VALID, USED, CANCELLED
│   │   └── UserRole.php           # ADMIN, ORGANIZER, CUSTOMER
│   ├── Exceptions/                # Xử lý ngoại lệ nghiệp vụ (BusinessException)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/             # Controller quản trị (Dashboard, Categories, Venues, Users, Events, Orders)
│   │   │   ├── Auth/              # Controller đăng nhập, đăng ký, đăng xuất
│   │   │   ├── Organizer/         # Controller ban tổ chức (Dashboard, Events, TicketTypes, Orders, Checkin)
│   │   │   ├── ProfileController.php # Cập nhật thông tin và mật khẩu cá nhân
│   │   │   └── Storefront/        # Controller khách hàng (Trang chủ, chi tiết sự kiện, đặt vé, vé cá nhân)
│   │   ├── Middleware/            # CheckRole (đa quyền), CheckActive (chặn tài khoản khóa)
│   │   └── Requests/              # 12 FormRequests chuẩn hóa validation tiếng Việt
│   ├── Models/                    # 8 Eloquent Models (User, Category, Venue, Event, TicketType, Order, OrderItem, Ticket)
│   ├── Policies/                  # Phân quyền chi tiết (EventPolicy, TicketTypePolicy, OrderPolicy)
│   └── Services/                  # Business Services
│       ├── OrderService.php       # Khóa bi quan (lockForUpdate), thanh toán mock, hủy đơn
│       └── TicketCheckinService.php # 5 bước xác thực vé tại cổng kiểm soát
├── bootstrap/app.php              # Cấu hình Routing, Middleware alias
├── database/
│   ├── factories/                 # Factory sinh dữ liệu mẫu
│   ├── migrations/                # 7 Migration tạo bảng hệ thống
│   └── seeders/                   # Seeder dữ liệu mẫu ban đầu
├── docs/                          # Thư viện tài liệu đồ án
│   ├── 01_phan_tich_yeu_cau.md    # Tài liệu đặc tả yêu cầu phần mềm (SRS đầy đủ)
│   ├── DEMO_SCRIPT.md             # Kịch bản demo 8-10 phút & 15 câu hỏi phản biện
│   ├── diagrams/                  # 8 sơ đồ PlantUML + hướng dẫn xuất ảnh PNG
│   ├── testing/                   # Danh sách test cases (TC-01..33) và log chạy PHPUnit
│   ├── SPEC.md                    # Bản hợp đồng kỹ thuật và 62 route contract
│   ├── PROGRESS.md                # Tiến độ thực hiện theo 7 pha
│   └── WORKLOG.md                 # Nhật ký làm việc và phân công nhiệm vụ
├── resources/views/
│   ├── admin/                     # Giao diện quản trị viên
│   ├── auth/                      # Giao diện đăng nhập, đăng ký
│   ├── errors/                    # Trang lỗi tùy biến (403, 404, 419, 500)
│   ├── layouts/
│   │   ├── app.blade.php          # Layout chính Storefront cho khách & người mua
│   │   └── panel.blade.php        # Layout Dashboard có sidebar cho Admin & Organizer
│   ├── organizer/                 # Giao diện ban tổ chức
│   ├── partials/                  # Alert thông báo flash, lỗi validation
│   ├── profile/                   # Giao diện hồ sơ cá nhân
│   └── storefront/                # Giao diện xem sự kiện, giỏ vé, thanh toán
├── routes/
│   ├── admin.php                  # Định tuyến phân hệ Quản trị viên
│   ├── auth.php                   # Định tuyến Xác thực người dùng
│   ├── organizer.php              # Định tuyến phân hệ Ban tổ chức
│   ├── storefront.php             # Định tuyến phân hệ Cửa hàng & Khách hàng
│   └── web.php                    # Route nạp 4 nhóm định tuyến
└── tests/Feature/                 # 33 Feature Tests bảo vệ toàn bộ chức năng trọng yếu
```

---

## 7. HƯỚNG DẪN CHẠY KIỂM THỬ TỰ ĐỘNG (AUTOMATED TESTS)

Hệ thống được trang bị bộ kiểm thử tích hợp toàn diện gồm **33 Feature Tests** với **82 assertions**, kiểm tra 100% các kịch bản quan trọng:
- Xác thực & Phân quyền đăng nhập (`AuthTest`)
- Chặn trái quyền & Bảo vệ tài khoản bị khóa (`AuthorizationTest`)
- Tìm kiếm, lọc sự kiện, giỏ vé phía Storefront (`StorefrontTest`)
- Khóa bi quan, thanh toán giả lập, bảo toàn số lượng vé (`OrderAndPaymentTest`)
- Xác thực vé 5 bước tại cổng kiểm soát (`CheckinTest`)
- Phân hệ quản trị danh mục, địa điểm, người dùng, sự kiện (`AdminManagementTest`)

Để thực hiện kiểm tra toàn bộ hệ thống, chạy lệnh sau tại thư mục gốc:

```bash
php artisan test
```

**Kết quả kỳ vọng:**
```text
   PASS  Tests\Feature\AdminManagementTest
  ✓ admin can access dashboard
  ✓ admin can create category
  ✓ admin can create venue
  ✓ admin can toggle user active status
  ✓ admin cannot lock their own account
  ✓ admin can approve event
  ...
  Tests:    33 passed (82 assertions)
  Duration: ~1.2s
```

*Chi tiết danh mục test cases và mô tả kiểm thử được lưu trữ tại file [docs/testing/test-cases.md](file:///d:/NMNC/event-ticket/docs/testing/test-cases.md).*

---

## 8. TÁC VỤ ĐỊNH KỲ (BACKGROUND COMMAND & SCHEDULER)

### 8.1 Lệnh quét hủy đơn hàng quá hạn giữ chỗ 15 phút
Khi khách hàng đặt vé, hệ thống khóa số lượng vé tương ứng và cho phép giữ chỗ trong 15 phút. Nếu khách hàng không hoàn tất thanh toán, lệnh sau sẽ tự động hủy đơn và hoàn trả vé vào kho:

```bash
# Chạy thủ công 1 lần để kiểm tra
php artisan orders:expire-pending
```

### 8.2 Chạy Scheduler liên tục trong môi trường phát triển (Local)
Để hệ thống tự động kiểm tra và quét đơn quá hạn mỗi phút:

```bash
php artisan schedule:work
```

---

## 9. HỆ THỐNG TÀI LIỆU DỰ ÁN

Toàn bộ tài liệu phân tích, thiết kế, kịch bản báo cáo và sơ đồ kiến trúc đã được biên soạn chỉn chu trong thư mục `docs/`:

1. **[Tài liệu Phân tích Yêu cầu (SRS)](file:///d:/NMNC/event-ticket/docs/01_phan_tich_yeu_cau.md)**: Đặc tả chi tiết 21 yêu cầu chức năng (FR-01 đến FR-21), 5 yêu cầu phi chức năng (NFR-01 đến NFR-05), 3 luồng công việc nghiệp vụ chính và Bảng Ma trận Truy xuất Nguồn gốc (Traceability Matrix).
2. **[Tài liệu Thiết kế Cơ sở Dữ liệu](file:///d:/NMNC/event-ticket/docs/02_thiet_ke_co_so_du_lieu.md)**: Đặc tả chi tiết 8 bảng nghiệp vụ, từ điển dữ liệu (Data Dictionary), sơ đồ ERD, cơ chế khóa bi quan (`lockForUpdate`), giữ chỗ 15 phút và file Word: [docs/02_thiet_ke_co_so_du_lieu.docx](file:///d:/NMNC/event-ticket/docs/02_thiet_ke_co_so_du_lieu.docx).
3. **[Kịch bản Thuyết trình & Phản biện](file:///d:/NMNC/event-ticket/docs/DEMO_SCRIPT.md)**: Kịch bản trình diễn trực tiếp theo từng phút (8-10 phút) cho 3 người báo cáo và 15 câu hỏi phản biện đáp án chi tiết dành cho Hội đồng chấm đồ án.
4. **[Thư viện Sơ đồ Thiết kế (PlantUML)](file:///d:/NMNC/event-ticket/docs/diagrams/README.md)**: 8 file mã nguồn `.puml` chuẩn UML (Use Case tổng quan, Use Case chi tiết 3 tác tử, ERD cơ sở dữ liệu, Sơ đồ chức năng, Activity mua vé, Sequence thanh toán).
5. **[Tài liệu Kiểm thử Phần mềm](file:///d:/NMNC/event-ticket/docs/testing/test-cases.md)**: Bảng chi tiết 33 test cases, mục tiêu kiểm thử, các bước thực hiện, dữ liệu test và kết quả thực tế.
6. **[Nhật ký Dự án & Phân công Công việc](file:///d:/NMNC/event-ticket/docs/WORKLOG.md)**: Ghi chép chi tiết các mốc hoàn thành, phân công nhiệm vụ và cam kết đóng góp của các thành viên.

---

> **Bản quyền**: Đồ án môn học Công nghệ Phần mềm / Phát triển Ứng dụng Web - Năm học 2026.
