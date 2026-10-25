# TIẾN ĐỘ DỰ ÁN (PROGRESS.md)

## Checklist các Phase
- [x] **Phase 0: Khởi tạo**
  - [x] Kiểm tra php, composer, git
  - [x] Khởi tạo Laravel project (event-ticket)
  - [x] Cấu hình .env, .env.example, timezone Asia/Ho_Chi_Minh, APP_LOCALE=vi, DB sqlite
  - [x] Laravel 11/12/13: AuthorizesRequests trong Controller.php, BusinessException.php, 4 route files
  - [x] Ghi docs/SPEC.md, docs/PROGRESS.md, docs/WORKLOG.md
  - [x] Git init & commit chore: init project and spec
- [x] **Phase 1: Database & Phân tích yêu cầu (A1 song song A2)**
  - [x] A1: docs/01_phan_tich_yeu_cau.md, docs/DEMO_SCRIPT.md, docs/WORKLOG.md
  - [x] A2: Migrations, Models, Factories, Seeders, Enums
  - [x] Cổng kiểm tra: `php artisan migrate:fresh --seed --force` (8 users, 8 events, 4 orders, 3 tickets)
  - [x] Commit: `feat(db): migrations, models, seeders` và `docs: phan tich yeu cau`
- [ ] **Phase 2: Auth, Middleware, Policies, Layout (A3)**
  - [ ] A3: routes/auth.php, Auth Controllers & Requests, Middleware role + active, Policies, Layouts, Errors (403, 404, 419, 500)
  - [ ] Cổng kiểm tra: migrate, route:list, view:cache
  - [ ] Commit: `feat(auth): auth, middleware, policies, layout`
- [ ] **Phase 3: Storefront & Organizer (A4 song song A5)**
  - [ ] A4: routes/storefront.php, Storefront Controllers & Requests, OrderService, ExpirePendingOrders, views
  - [ ] A5: routes/organizer.php, Organizer Controllers & Requests, TicketCheckinService, views
  - [ ] Cổng kiểm tra: migrate, route:list, view:cache
  - [ ] Commit: `feat(storefront): ...` và `feat(organizer): ...`
- [ ] **Phase 4: Admin (A6)**
  - [ ] A6: routes/admin.php, Admin Controllers & Requests, views (Categories, Venues, Users, Events, Orders, Dashboard)
  - [ ] Cổng kiểm tra: migrate, route:list, view:cache
  - [ ] Commit: `feat(admin): ...`
- [ ] **Phase 5: QA Security Tests & Diagrams (A7 song song A8)**
  - [ ] A7: PHPUnit Feature tests (>= 20 tests), test-cases.md, test-output.txt, rà soát bảo mật & Blade
  - [ ] A8: docs/diagrams/ (8 file PlantUML + README.md)
  - [ ] Cổng kiểm tra: php artisan test
  - [ ] Commit: `test: ...` và `docs(diagrams): ...`
- [ ] **Phase 6: README, Bảng truy vết, Hoàn thiện & Báo cáo cuối (Leader + A1)**
  - [ ] README.md đầy đủ
  - [ ] docs/01_phan_tich_yeu_cau.md: Bảng truy vết FR -> route/controller
  - [ ] Cập nhật docs/WORKLOG.md
  - [ ] Cổng kiểm tra cuối cùng
  - [ ] Commit: `docs: readme and final`

---

## Giả định
1. Sử dụng SQLite làm cơ sở dữ liệu mặc định (`database/database.sqlite`), lưu trữ nhanh gọn và không cần cấu hình thêm daemon DB.
2. Tiền tệ định dạng bằng helper hoặc Blade directive theo quy chuẩn `number_format($val, 0, ',', '.') . ' đ'`.
3. Mã đơn hàng `ORD-` + 8 ký tự in hoa ngẫu nhiên; mã vé `TKT-` + 10 ký tự in hoa ngẫu nhiên.
4. Thời gian giữ chỗ đơn hàng pending là 15 phút tính từ `created_at` (`expires_at = now()->addMinutes(15)`).
5. Ảnh banner lưu trong `storage/app/public/banners`, liên kết công khai qua `public/storage`. Nếu không tải ảnh, hiển thị placeholder ảnh SVG/CSS màu gradient đẹp mắt.
6. Bootstrap 5.3 + Bootstrap Icons được nhúng qua CDN jsDelivr.

---

## BLOCKED
*(Hiện tại không có mục nào bị chặn)*
