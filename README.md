# HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

> **Đồ án môn học**: Phần mềm Nguồn mở Nâng cao — Hệ thống Quản lý Bán vé Sự kiện trực tuyến  
> **Nhóm thực hiện**: **Nhóm 8 — Lớp 68PM2** (Hồ Đăng Quang - `0021568`, Tống Xuân Định - `0008268`, Nguyễn Hữu Quý - `0022968`)  
> **Framework**: Laravel 13 (PHP 8.3+)  
> **Frontend**: Laravel Blade + Bootstrap 5.3 CDN + Bootstrap Icons CDN (Zero-node / Không yêu cầu npm build)  
> **Cơ sở dữ liệu**: SQLite (Mặc định - Chạy ngay không cần cài đặt DB server) / Tùy chọn MySQL (XAMPP, Laragon, Docker)

---

## MỤC LỤC
1. [Giới thiệu tổng quan](#1-giới-thiệu-tổng-quan)
2. [Yêu cầu hệ thống](#2-yêu-cầu-hệ-thống)
3. [Hướng dẫn cài đặt và khởi chạy](#3-hướng-dẫn-cài-đặt-và-khởi-chạy)
4. [Hướng dẫn chuyển đổi sang MySQL (XAMPP / Laragon)](#4-hướng-dẫn-chuyển-đổi-sang-mysql-xampp--laragon)
5. [Danh sách tài khoản dùng thử (Demo Accounts)](#5-danh-sách-tài-khoản-dùng-thử-demo-accounts)
6. [Cấu trúc thư mục dự án](#6-cấu-trúc-thư-mục-dự-án)
7. [Hướng dẫn chạy kiểm thử tự động (Automated Tests)](#7-hướng-dẫn-chạy-kiểm-thử-tự-động-automated-tests)
8. [Tác vụ định kỳ (Background Command & Scheduler)](#8-tác-vụ-định-kỳ-background-command--scheduler)
9. [Hệ thống tài liệu dự án](#9-hệ-thống-tài-liệu-dự-án)

---

## 1. GIỚI THIỆU TỔNG QUAN

Hệ thống Quản lý Bán vé Sự kiện là ứng dụng web phục vụ 4 nhóm người dùng chính:
- **Khách vãng lai (Guest)**: Xem danh sách sự kiện đang mở bán, tìm kiếm theo từ khóa (`q`), lọc theo danh mục/thành phố/khoảng ngày, sắp xếp theo thời gian hoặc giá vé, xem chi tiết sự kiện và các hạng vé, đăng ký/đăng nhập.
- **Khách hàng (Customer)**: Đặt vé giữ chỗ 15 phút an toàn với cơ chế khóa bi quan (*pessimistic locking*), thanh toán giả lập (Mock Payment Gateway: `bank_transfer`, `momo`, `card`), nhận thẻ vé điện tử kèm mã `TKT-XXXXXXXXXX` định dạng monospace và nút in vé, xem lịch sử đơn hàng và tự hủy đơn khi đang `pending`.
- **Ban tổ chức (Organizer)**: Dashboard thống kê doanh thu, quản lý vòng đời sự kiện (Tạo nháp -> Thêm loại vé -> Gửi duyệt), theo dõi đơn hàng của các sự kiện mình tổ chức, và ứng dụng Check-in vé tại cổng kiểm soát với 5 bước xác thực chặt chẽ.
- **Quản trị viên (Admin)**: Dashboard toàn hệ thống (thống kê người dùng theo vai trò, sự kiện theo trạng thái, doanh thu từ đơn `paid`, top sự kiện bán chạy), quản lý danh mục sự kiện, quản lý địa điểm tổ chức, quản lý người dùng (khóa/mở khóa tài khoản, phân quyền), kiểm duyệt (`approve` / `reject` / `cancel`) sự kiện và tra cứu toàn bộ đơn hàng.

### Điểm nổi bật kỹ thuật:
- **An toàn tồn kho vé**: Áp dụng `lockForUpdate()` kết hợp `DB::transaction()` trong `OrderService` ngăn chặn tình trạng *race condition* / *overselling* khi nhiều khách hàng cùng đặt vé đồng thời.
- **Tự động hủy đơn quá hạn**: Lệnh `php artisan orders:expire-pending` (đã đăng ký chạy mỗi phút trong `routes/console.php`) kết hợp lazy-check khi xem hoặc thanh toán đơn hàng giúp thu hồi số lượng vé giữ chỗ quá 15 phút.
- **Bảo mật đa tầng**: 11 FormRequest validation tiếng Việt chuẩn hóa, 3 Authorization Policies (`EventPolicy`, `TicketTypePolicy`, `OrderPolicy`) chống IDOR, Middleware `CheckRole` & `CheckActive` chặn truy cập trái quyền và tự động đăng xuất tài khoản bị khóa.
- **Kiến trúc rõ ràng**: Tách biệt 4 phân hệ định tuyến độc lập (`auth.php`, `storefront.php`, `organizer.php`, `admin.php`) với 57 application routes chuẩn RESTful.

---

## 2. YÊU CẦU HỆ THỐNG

- **PHP**: Phiên bản `>= 8.3` (Đã kiểm thử trên PHP 8.5.9)
  - Các extension PHP bắt buộc: `pdo`, `pdo_sqlite` (cho SQLite mặc định), `pdo_mysql` (nếu dùng MySQL), `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`.
- **Composer**: Phiên bản `2.x`
- **Node.js / npm**: **Không yêu cầu** (Giao diện dùng hoàn toàn Bootstrap 5.3 & Bootstrap Icons qua CDN jsDelivr, không cần build asset).
- **Hệ điều hành**: Windows 10/11, macOS hoặc Linux.

---

## 3. HƯỚNG DẪN CÀI ĐẶT VÀ KHỞI CHẠY

### 3.1 Khởi chạy nhanh trên Windows (PowerShell / CMD)

Mặc định dự án đã được cấu hình sẵn **SQLite**, bạn không cần cài thêm MySQL hay Apache:

```powershell
# 1. Điều hướng vào thư mục dự án
cd path\to\DA_PMNM_NC

# 2. Cài đặt các thư viện PHP qua Composer
composer install

# 3. Tạo file cấu hình môi trường từ mẫu (nếu chưa có)
copy .env.example .env

# 4. Tạo file cơ sở dữ liệu SQLite trống (nếu chưa có)
New-Item -ItemType File -Force -Path database/database.sqlite

# 5. Tạo Application Key và liên kết thư mục public/storage
php artisan key:generate
php artisan storage:link

# 6. Chạy Migration và nạp dữ liệu mẫu ban đầu (Seeder)
php artisan migrate:fresh --seed --force

# 7. Khởi động Web Server cục bộ
php artisan serve
```

Mở trình duyệt web và truy cập địa chỉ: **[http://localhost:8000](http://localhost:8000)**

### 3.2 Khởi chạy trên Linux / macOS

```bash
cd /path/to/DA_PMNM_NC
composer install
cp .env.example .env
touch database/database.sqlite
chmod -R 775 storage bootstrap/cache
php artisan key:generate
php artisan storage:link
php artisan migrate:fresh --seed --force
php artisan serve
```

---

## 4. HƯỚNG DẪN CHUYỂN ĐỔI SANG MYSQL (XAMPP / LARAGON)

1. Khởi động **MySQL Service** trên XAMPP Control Panel hoặc Laragon.
2. Tạo mới một Database trống tên `event_ticket` với mã hóa `utf8mb4_unicode_ci`:
   ```sql
   CREATE DATABASE event_ticket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Mở file `.env` trong thư mục gốc dự án và cập nhật cấu hình:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=event_ticket
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. Chạy lại migration và seeder:
   ```bash
   php artisan migrate:fresh --seed --force
   ```

---

## 5. DANH SÁCH TÀI KHOẢN DÙNG THỬ (DEMO ACCOUNTS)

Hệ thống đã chuẩn bị sẵn 8 tài khoản mẫu trong [`database/seeders/UserSeeder.php`](./database/seeders/UserSeeder.php).  
**Mật khẩu dùng chung cho tất cả tài khoản:** `password`

| STT | Vai trò (Role) | Email đăng nhập | Tên hiển thị | Trạng thái | Mục đích kiểm thử chính |
| :---: | :--- | :--- | :--- | :---: | :--- |
| 1 | **Admin** | `admin@example.com` | Quản Trị Viên Hệ Thống | Hoạt động | Quản lý danh mục, địa điểm, duyệt sự kiện, khóa/mở user |
| 2 | **Organizer 1** | `organizer1@example.com` | Ban Tổ Chức Sao Mai | Hoạt động | Tạo sự kiện, quản lý loại vé, xem đơn hàng, check-in vé |
| 3 | **Organizer 2** | `organizer2@example.com` | Công Ty Âm Nhạc Trẻ | Hoạt động | Kiểm thử phân quyền cô lập dữ liệu giữa các Organizer |
| 4 | **Customer 1** | `customer1@example.com` | Nguyễn Văn An | Hoạt động | Đặt vé, thanh toán giả lập, xem đơn hàng & vé của tôi |
| 5 | **Customer 2** | `customer2@example.com` | Trần Thị Bình | Hoạt động | Đặt vé, kiểm tra đơn đã thanh toán |
| 6 | **Customer 3** | `customer3@example.com` | Lê Hoàng Cường | Hoạt động | Thanh toán hoặc hủy đơn hàng đang `pending` |
| 7 | **Customer 4** | `customer4@example.com` | Phạm Thúy Dung | Hoạt động | Đặt vé, kiểm thử luồng người mua |
| 8 | **Customer 5** | `customer5@example.com` | Hoàng Minh Đức | Hoạt động | Đặt vé, kiểm thử khóa tài khoản từ trang Admin |

*(Lưu ý: Để kiểm thử tính năng chặn tài khoản bị khóa bởi Middleware `CheckActive`, đăng nhập bằng `admin@example.com`, vào mục **Quản Lý Người Dùng** khóa một tài khoản bất kỳ, sau đó đăng nhập lại bằng tài khoản đó.)*

---

## 6. CẤU TRÚC THƯ MỤC DỰ ÁN

```text
DA_PMNM_NC/
├── app/
│   ├── Console/Commands/          # ExpirePendingOrders (orders:expire-pending)
│   ├── Enums/                     # EventStatus, OrderStatus, TicketStatus, UserRole
│   ├── Exceptions/                # BusinessException
│   ├── Http/
│   │   ├── Controllers/           # Auth, Storefront, Organizer, Admin, ProfileController
│   │   ├── Middleware/            # CheckRole, CheckActive
│   │   └── Requests/              # 11 FormRequests chuẩn hóa validation tiếng Việt
│   ├── Models/                    # 8 Eloquent Models (User, Category, Venue, Event, TicketType, Order, OrderItem, Ticket)
│   ├── Policies/                  # EventPolicy, TicketTypePolicy, OrderPolicy
│   └── Services/                  # OrderService, TicketCheckinService
├── bootstrap/app.php              # Cấu hình Routing, Middleware alias
├── database/
│   ├── factories/                 # 8 Factory sinh dữ liệu test
│   ├── migrations/                # 10 Migrations (3 hệ thống + 7 nghiệp vụ)
│   └── seeders/                   # 6 Seeders (DatabaseSeeder + 5 entity seeders)
├── docs/                          # Tài liệu phân tích, thiết kế, báo cáo, sơ đồ & test
│   ├── diagrams/                  # 8 sơ đồ PlantUML (.puml) & ảnh xuất PNG (images/*.png)
│   └── testing/                   # Bảng test cases & kết quả chạy PHPUnit
├── resources/views/               # Giao diện Blade (layouts/app, layouts/panel, auth, storefront, organizer, admin, errors)
├── routes/                        # web.php nạp auth.php, storefront.php, organizer.php, admin.php; console.php
└── tests/
    ├── Unit/                      # 2 Unit tests (DomainModelsTest)
    └── Feature/                   # 35 Feature tests (6 file theo phân hệ)
```

---

## 7. HƯỚNG DẪN CHẠY KIỂM THỬ TỰ ĐỘNG (AUTOMATED TESTS)

Hệ thống được trang bị bộ kiểm thử tự động gồm **37 tests (2 Unit + 35 Feature)** với **104 assertions**:
- `DomainModelsTest` (2 tests, 9 assertions): Kiểm tra logic accessor `remaining` và ánh xạ vai trò/trạng thái.
- `AuthTest` (6 tests, 24 assertions): Đăng ký, đăng nhập, mật khẩu sai, chặn tài khoản bị khóa, cập nhật hồ sơ, đăng xuất.
- `AuthorizationTest` (5 tests, 8 assertions): Phân quyền vai trò, chặn truy cập chéo IDOR, chặn admin tự khóa/tự hạ quyền.
- `StorefrontTest` (4 tests, 9 assertions): Duyệt sự kiện công khai, tìm kiếm từ khóa, lọc danh mục/thành phố, 404 với sự kiện nháp.
- `OrderAndPaymentTest` (9 tests, 26 assertions): Đặt vé giữ chỗ 15 phút, kiểm tra giới hạn & tồn kho, thanh toán giả lập, hủy đơn hoàn vé, tự động hủy đơn hết hạn qua command và khi bấm thanh toán muộn.
- `CheckinTest` (5 tests, 9 assertions): Kiểm tra 5 bước soát vé hợp lệ, không tồn tại, chưa thanh toán, đã dùng, khác ban tổ chức.
- `AdminManagementTest` (6 tests, 19 assertions): Phê duyệt/từ chối/hủy sự kiện, chặn xóa danh mục có sự kiện, khóa/mở user, dashboard & CRUD danh mục/địa điểm.

Chạy toàn bộ kiểm thử bằng lệnh:
```bash
php artisan test
```
Hoặc chạy chi tiết từng test case:
```bash
php vendor/bin/phpunit --testdox
```

*Chi tiết danh sách test cases và kết quả thực tế được lưu tại [docs/testing/test-cases.md](./docs/testing/test-cases.md) và [docs/testing/test-output.txt](./docs/testing/test-output.txt).*

---

## 8. TÁC VỤ ĐỊNH KỲ (BACKGROUND COMMAND & SCHEDULER)

### 8.1 Lệnh quét hủy đơn hàng quá hạn giữ chỗ 15 phút
```bash
php artisan orders:expire-pending
```

### 8.2 Chạy Scheduler tự động mỗi phút trên Local
Lệnh `orders:expire-pending` đã được đăng ký định kỳ mỗi phút trong [`routes/console.php`](./routes/console.php):
```bash
php artisan schedule:work
```

---

## 9. HỆ THỐNG TÀI LIỆU DỰ ÁN

1. **[Tài liệu Phân tích Yêu cầu (SRS)](./docs/01_phan_tich_yeu_cau.md)**
2. **[Tài liệu Thiết kế Cơ sở Dữ liệu](./docs/02_thiet_ke_co_so_du_lieu.md)** ([File Word](./docs/02_thiet_ke_co_so_du_lieu.docx))
3. **[Báo cáo Tiêu chí Đồ án - Mục 1 & 2](./docs/03_bao_cao_tieu_chi_do_an.md)** ([File Word](./docs/03_bao_cao_tieu_chi_do_an.docx))
4. **[Báo cáo Xây dựng Chức năng - Mục 3](./docs/04_bao_cao_xay_dung_chuc_nang.md)** ([File Word](./docs/04_bao_cao_xay_dung_chuc_nang.docx))
5. **[Báo cáo Kiểm thử, Bảo mật & Triển khai - Mục 4 & 5](./docs/05_bao_cao_kiem_thu_bao_mat_trien_khai.md)** ([File Word](./docs/05_bao_cao_kiem_thu_bao_mat_trien_khai.docx))
6. **[Báo cáo Làm việc nhóm & Bảo vệ - Mục 6 & 7](./docs/06_bao_cao_lam_viec_nhom_va_bao_ve.md)** ([File Word](./docs/06_bao_cao_lam_viec_nhom_va_bao_ve.docx))
7. **[Kịch bản Thuyết trình & Phản biện](./docs/DEMO_SCRIPT.md)**
8. **[Thư viện Sơ đồ Thiết kế (PlantUML & PNG)](./docs/diagrams/README.md)**
9. **[Tài liệu Kiểm thử Phần mềm](./docs/testing/test-cases.md)**
10. **[Nhật ký Dự án & Phân công Công việc](./docs/WORKLOG.md)**
