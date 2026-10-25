# NHẬT KÝ LÀM VIỆC VÀ PHÂN CÔNG NHÓM (WORKLOG.md)

## 1. Bảng phân công nhóm

| STT | Thành viên | Vai trò trong nhóm | Mảng phụ trách | PLO phụ trách |
|-----|------------|-------------------|----------------|---------------|
| 1 | [Họ tên thành viên 1] | Trưởng nhóm (Leader / Analyst) | Quản lý dự án, Phân tích yêu cầu, Tài liệu nghiệp vụ & Kịch bản demo | PLO1, Mục 7 |
| 2 | [Họ tên thành viên 2] | Backend & Database Developer | Thiết kế CSDL, Migrations, Models, Seeders, ERD | PLO2 |
| 3 | [Họ tên thành viên 3] | Frontend & UI/UX Developer | Thiết kế Layouts (Bootstrap 5.3), Blade Views, Auth & Profile | PLO3 |
| 4 | [Họ tên thành viên 4] | Fullstack Developer (Storefront) | Chức năng Đặt vé, Thanh toán giả lập, Chi tiết sự kiện & Giỏ hàng | PLO3 |
| 5 | [Họ tên thành viên 5] | Fullstack Developer (Organizer & Admin) | Quản lý Sự kiện, Loại vé, Check-in vé, Phê duyệt & Quản trị hệ thống | PLO3 |
| 6 | [Họ tên thành viên 6] | QA & Security Engineer | Viết Unit/Feature Tests, Rà soát bảo mật CSRF/XSS/Auth, Vẽ sơ đồ kiến trúc | PLO3, PLO2, PLO6 |

---

## 2. Nhật ký làm việc theo ngày

### Ngày 25/10/2026
- **Khởi tạo dự án (Phase 0)**:
  - Kiểm tra môi trường: PHP 8.3.33, Composer v2.11, Git.
  - Khởi tạo project Laravel 11/13 (`event-ticket`).
  - Thiết lập múi giờ `Asia/Ho_Chi_Minh`, ngôn ngữ tiếng Việt `vi`, kết nối SQLite.
  - Tích hợp trait `AuthorizesRequests`, tạo `BusinessException`, tạo cấu trúc route tách biệt.
  - Thiết lập tài liệu đặc tả `docs/SPEC.md`, tiến độ `docs/PROGRESS.md` và nhật ký `docs/WORKLOG.md`.
