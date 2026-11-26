# TIẾN ĐỘ DỰ ÁN (PROGRESS.md)

## Checklist các giai đoạn xây dựng nền tảng (Phase 0 – Phase 6)
- [x] **Phase 0: Khởi tạo**
  - [x] Kiểm tra php, composer, git
  - [x] Khởi tạo Laravel project (`event-ticket`)
  - [x] Cấu hình `.env`, `.env.example`, timezone `Asia/Ho_Chi_Minh`, `APP_LOCALE=vi`
  - [x] Laravel 11/12/13: `AuthorizesRequests` trong `Controller.php`, `BusinessException.php`, 4 route files
  - [x] Ghi `docs/SPEC.md`, `docs/PROGRESS.md`, `docs/WORKLOG.md`
- [x] **Phase 1: Database & Phân tích yêu cầu**
  - [x] `docs/01_phan_tich_yeu_cau.md`, `docs/DEMO_SCRIPT.md`, `docs/WORKLOG.md`
  - [x] Migrations, Models, Factories, Seeders, Enums
  - [x] Cổng kiểm tra: `php artisan migrate:fresh --seed --force`
- [x] **Phase 2: Auth, Middleware, Policies, Layout**
  - [x] `routes/auth.php`, Auth Controllers & Requests, Middleware `role` + `active`, Policies, Layouts, Errors (403, 404, 419, 500)
- [x] **Phase 3: Storefront & Organizer**
  - [x] `routes/storefront.php`, Storefront Controllers & Requests, `OrderService`, `ExpirePendingOrders`, views
  - [x] `routes/organizer.php`, Organizer Controllers & Requests, `TicketCheckinService`, views
- [x] **Phase 4: Admin**
  - [x] `routes/admin.php`, Admin Controllers & Requests, views (Categories, Venues, Users, Events, Orders, Dashboard)
- [x] **Phase 5: QA Security Tests & Diagrams**
  - [x] PHPUnit Feature/Unit tests, `test-cases.md`, `test-output.txt`, rà soát bảo mật CSRF/XSS
  - [x] `docs/diagrams/` (8 file PlantUML chính + 6 sequence + ảnh PNG)
- [x] **Phase 6: README, Bảng truy vết & Rà soát ban đầu**
  - [x] `README.md`, Bảng truy vết FR trong `docs/01_phan_tich_yeu_cau.md`, bộ báo cáo Word (`.docx`)

---

## Checklist Đợt Nâng cấp 4 Tuần (Theo `KE_HOACH_NANG_CAP_1_THANG.md`)
- [x] **Tuần 1: Hạ tầng MySQL 8, Enum, CI & Service Lõi**
  - [x] Chuẩn hóa `.env.example` sang **MySQL 8.0 (`utf8mb4_unicode_ci`)**
  - [x] Cài `endroid/qr-code` & `barryvdh/laravel-dompdf`; xây dựng `QrTicketService` & `TicketPdfService`
  - [x] Thiết lập GitHub Actions CI (`.github/workflows/ci.yml`) chạy Pint + migrate/seed + PHPUnit trên `mysql:8.0`
  - [x] Đồng bộ 4 Enums (`EventStatus`, `OrderStatus`, `TicketStatus`, `UserRole`) vào Models
  - [x] Chạy 2 migration bổ sung 5 cột: `tickets.qr_payload`, `tickets.qr_signature`, `tickets.checked_in_by`, `ticket_types.zone`, `ticket_types.description`
- [x] **Tuần 2: Tính năng & Giao diện Bootstrap**
  - [x] Storefront hiển thị `zone`, `description`, ảnh QR và nút tải vé PDF
  - [x] Xây dựng `OrderPaidMail` (`emails/order-paid.blade.php`) đính kèm PDF trong RAM
  - [x] Route `GET /my-tickets/{ticket}/pdf` (`tickets.pdf`) bảo vệ bởi `OrderPolicy`
  - [x] Tích hợp Camera QR (`public/js/html5-qrcode.min.js` offline) & xác thực 6 bước HMAC-SHA256 + ghi `checked_in_by`
  - [x] Admin hủy sự kiện cascade (`Admin\EventController::cancel`) & hiển thị `zone`, `description`, `checkedInBy`
- [ ] **Tuần 3: Kiểm thử, Hiệu năng & Sơ đồ `draw.io`**
  - [x] Rà soát và sửa lỗi N+1 query tại `Storefront\OrderController@show`, `Organizer\OrderController@show` & `OrderPaidMail`
  - [x] Cập nhật `README.md`, `docs/01_phan_tich_yeu_cau.md`, `docs/02_thiet_ke_co_so_du_lieu.md`, `docs/SPEC.md`
  - [x] Tạo bộ Prompt & mã UML mới nhất cho `draw.io` tại [`docs/diagrams/DRAWIO_UML_PROMPTS.md`](./diagrams/DRAWIO_UML_PROMPTS.md)
  - [ ] Tự vẽ và xuất các file `.drawio` + `.png` mới từ `DRAWIO_UML_PROMPTS.md`
- [x] **Tuần 4: Rà soát Bảo mật/Offline, Hoàn thiện Báo cáo & Kịch bản Bảo vệ**
  - [x] Bổ sung test phòng thủ: lỗi mail transport không rollback đơn `paid`, luồng E2E Checkout→PDF→QR Check-in, chặn Organizer xem chéo đơn hàng, kiểm thử hồi quy khóa ngoại `RESTRICT` xóa mềm, concurrency `OrderService`, sort giá, giao diện vé hủy & `organizer/orders/show` (**103/103 tests, 411 assertions PASSED**)
  - [x] Rà soát 100% `@csrf`, không `{!! !!}` ở Blade, 11 FormRequests tiếng Việt, `pint --test` sạch 100%
  - [x] Cập nhật `docs/DEMO_SCRIPT.md`, `docs/03_*`–`06_*`, `docs/testing/*`, `docs/WORKLOG.md` và tái tạo 5 file `.docx`

---

## Giả định & Thiết lập chuẩn hiện hành
1. **Cơ sở dữ liệu mặc định**: **MySQL 8.0+ Community Server** (`InnoDB`, `utf8mb4_unicode_ci`) theo `.env.example` (hỗ trợ XAMPP / Laragon / Docker; đồng thời hỗ trợ SQLite làm phương án dự phòng và `phpunit.xml` dùng SQLite `:memory:` khi chạy test nhanh).
2. **Định dạng hiển thị**: Tiền tệ VNĐ theo chuẩn `number_format($val, 0, ',', '.') . ' đ'`, ngày giờ `d/m/Y H:i`, múi giờ `Asia/Ho_Chi_Minh`, ngôn ngữ `APP_LOCALE=vi`.
3. **Định danh & Chữ ký vé**: Mã đơn `ORD-` + 8 ký tự in hoa; mã vé `TKT-` + 10 ký tự in hoa; payload QR JSON `{"v":1,"ticket_code":"TKT-..."}` ký bằng `hash_hmac('sha256', payload, APP_KEY)`.
4. **Giữ chỗ & Tự động hủy**: Thời hạn giữ chỗ đơn `pending` là 15 phút (`expires_at = now()->addMinutes(15)`), thu hồi qua lazy check và scheduler `orders:expire-pending` (mỗi phút trong `routes/console.php`).
5. **PDF, Email & Camera Offline**: PDF khổ A5 sinh động trong RAM qua `barryvdh/laravel-dompdf` (cần PHP `extension=gd` để nhúng QR PNG), không lưu file lên ổ đĩa; email dùng `OrderPaidMail` (`MAIL_MAILER=log` mặc định khi offline, dev dùng Mailpit); camera check-in dùng `public/js/html5-qrcode.min.js` lưu cục bộ.
6. **Giao diện Zero-Node**: Bootstrap 5.3 + Bootstrap Icons qua CDN jsDelivr, không yêu cầu Node.js / `npm run build`.
