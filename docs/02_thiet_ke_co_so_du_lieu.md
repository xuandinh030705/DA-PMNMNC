# TÀI LIỆU THIẾT KẾ CƠ SỞ DỮ LIỆU (DATABASE DESIGN DOCUMENT)
## HỆ THỐNG QUẢN LÝ BÁN VÉ SỰ KIỆN (EVENT TICKET MANAGEMENT SYSTEM)

- **Mã tài liệu**: DDD-ETMS-v1.0 (Final Release)
- **Nền tảng**: Laravel 13 (PHP 8.3+)
- **Hệ quản trị CSDL**: SQLite (Mặc định) / Hỗ trợ MySQL 8.0+ (InnoDB)
- **Chuẩn hóa**: Dạng chuẩn 3 (Third Normal Form - 3NF)
- **File Word đính kèm**: [02_thiet_ke_co_so_du_lieu.docx](file:///d:/NMNC/event-ticket/docs/02_thiet_ke_co_so_du_lieu.docx)

---

### 1. Tổng quan Kiến trúc Dữ liệu & Quy ước Thiết kế

#### 1.1. Mục tiêu kiến trúc
Cơ sở dữ liệu của dự án được thiết kế đáp ứng các tiêu chí:
- **Tính toàn vẹn dữ liệu (Data Integrity)**: Toàn bộ quan hệ được ràng buộc chặt chẽ bằng Foreign Keys với các hành vi `CASCADE` và `RESTRICT` phù hợp.
- **Tính nhất quán giao dịch (ACID & Concurrency Control)**: Sử dụng Database Transaction kết hợp khóa bi quan (`lockForUpdate()`) chống bán vượt vé (overbooking).
- **Chuẩn hóa 3NF**: Loại bỏ hoàn toàn dư thừa dữ liệu và các phụ thuộc bắc cầu.

#### 1.2. Quy ước đặt tên (Naming Conventions)
- **Tên bảng**: Danh từ số nhiều, chữ thường, cách nhau bằng dấu gạch dưới (snake_case), ví dụ: `ticket_types`, `order_items`.
- **Khóa chính**: `id` kiểu số nguyên lớn tự tăng (BIGINT UNSIGNED Auto Increment).
- **Khóa ngoại**: `{tên_bảng_số_ít}_id`, ví dụ: `organizer_id` trỏ tới `users.id`.
- **Dấu thời gian**: Cặp `created_at` và `updated_at` (TIMESTAMP) tự động quản lý bởi Eloquent.
- **Xóa mềm (Soft Delete)**: Cột `deleted_at` trên bảng `events`.

---

### 2. Sơ đồ Quan hệ Thực thể (ERD) & Phân tích Quan hệ

```
  +------------------+             +--------------------+
  |      users       |             |     categories     |
  +------------------+             +--------------------+
           |                                  |
           | 1                                | 1
           | organizes                        | categorizes
           v N                                v N
  +------------------+             +--------------------+
  |      venues      | 1 hosts N   |       events       |
  +------------------+ ----------> +--------------------+
                                              |
                                              | 1
                                              | has
                                              v N
                                   +--------------------+
                                   |    ticket_types    |
                                   +--------------------+
                                              |
  +------------------+                        | 1
  |      orders      |                        | referenced in
  +------------------+                        v N
           | 1                     +--------------------+
           | contains              |    order_items     |
           v N                     +--------------------+
  +------------------+                        |
  |   order_items    |                        | 1 generates N
  +------------------+                        v
                                   +--------------------+
                                   |      tickets       |
                                   +--------------------+
```

#### Ma trận quan hệ thực thể:
| Thực thể 1 | Quan hệ | Thực thể 2 | Khóa ngoại | On Delete | Mô tả nghiệp vụ |
|---|---|---|---|---|---|
| `users` | 1 - N | `events` | `events.organizer_id -> users.id` | CASCADE | Ban tổ chức sở hữu và quản lý nhiều sự kiện. |
| `categories` | 1 - N | `events` | `events.category_id -> categories.id` | RESTRICT | Danh mục sự kiện. Chặn xóa danh mục đang có sự kiện. |
| `venues` | 1 - N | `events` | `events.venue_id -> venues.id` | RESTRICT | Địa điểm tổ chức. Chặn xóa địa điểm đang có sự kiện. |
| `events` | 1 - N | `ticket_types` | `ticket_types.event_id -> events.id` | CASCADE | Một sự kiện có nhiều loại vé (VIP, Tiêu chuẩn,...). |
| `users` | 1 - N | `orders` | `orders.user_id -> users.id` | CASCADE | Một khách hàng có thể đặt nhiều đơn hàng. |
| `orders` | 1 - N | `order_items` | `order_items.order_id -> orders.id` | CASCADE | Một đơn hàng gồm nhiều dòng loại vé khác nhau. |
| `ticket_types` | 1 - N | `order_items` | `order_items.ticket_type_id -> ticket_types.id` | CASCADE | Một loại vé xuất hiện trong nhiều đơn hàng. |
| `order_items` | 1 - N | `tickets` | `tickets.order_item_id -> order_items.id` | CASCADE | Một mục đặt vé sinh ra các vé điện tử độc nhất sau khi thanh toán. |

---

### 3. Từ điển Dữ liệu Chi tiết (Data Dictionary)

#### 3.1. Bảng `users`
Lưu trữ thông tin tài khoản người dùng của toàn bộ hệ thống.

| Tên Cột | Kiểu Dữ Liệu | Khóa | Null? | Mặc Định | Ý Nghĩa Nghiệp Vụ |
|---|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK | No | Auto Increment | Khóa chính duy nhất. |
| `name` | VARCHAR(255) | - | No | - | Tên đầy đủ của người dùng. |
| `email` | VARCHAR(255) | UK | No | - | Email đăng nhập duy nhất toàn sàn. |
| `password` | VARCHAR(255) | - | No | - | Mật khẩu mã hóa Bcrypt. |
| `phone` | VARCHAR(20) | - | Yes | NULL | Số điện thoại liên hệ. |
| `role` | VARCHAR(50) | - | No | 'customer' | Vai trò: `admin`, `organizer`, `customer`. |
| `is_active` | TINYINT(1) | - | No | 1 (TRUE) | 1 = Đang hoạt động, 0 = Bị khóa tài khoản. |
| `remember_token` | VARCHAR(100) | - | Yes | NULL | Token duy trì phiên đăng nhập. |
| `created_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm tạo tài khoản. |
| `updated_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm cập nhật tài khoản. |

#### 3.2. Bảng `categories`
Phân loại sự kiện theo chủ đề, lĩnh vực.

| Tên Cột | Kiểu Dữ Liệu | Khóa | Null? | Mặc Định | Ý Nghĩa Nghiệp Vụ |
|---|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK | No | Auto Increment | Khóa chính duy nhất. |
| `name` | VARCHAR(255) | UK | No | - | Tên danh mục (ví dụ: 'Âm nhạc & Lễ hội'). |
| `slug` | VARCHAR(255) | UK | No | - | URL thân thiện SEO duy nhất. |
| `description` | TEXT | - | Yes | NULL | Mô tả chi tiết về danh mục. |
| `created_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm tạo danh mục. |
| `updated_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm cập nhật danh mục. |

#### 3.3. Bảng `venues`
Quản lý địa điểm tổ chức và sức chứa tối đa.

| Tên Cột | Kiểu Dữ Liệu | Khóa | Null? | Mặc Định | Ý Nghĩa Nghiệp Vụ |
|---|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK | No | Auto Increment | Khóa chính duy nhất. |
| `name` | VARCHAR(255) | - | No | - | Tên địa điểm (ví dụ: 'Trung tâm Hội nghị Quốc gia'). |
| `address` | VARCHAR(255) | - | No | - | Địa chỉ số nhà, tên đường. |
| `city` | VARCHAR(100) | - | No | - | Tỉnh / Thành phố trực thuộc. |
| `capacity` | INT UNSIGNED | - | No | - | Sức chứa tối đa (người). |
| `created_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm tạo địa điểm. |
| `updated_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm cập nhật địa điểm. |

#### 3.4. Bảng `events`
Lưu trữ thông tin chi tiết sự kiện và trạng thái duyệt.

| Tên Cột | Kiểu Dữ Liệu | Khóa | Null? | Mặc Định | Ý Nghĩa Nghiệp Vụ |
|---|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK | No | Auto Increment | Khóa chính duy nhất. |
| `organizer_id` | BIGINT UNSIGNED | FK | No | - | Khóa ngoại trỏ về `users.id`. |
| `category_id` | BIGINT UNSIGNED | FK | No | - | Khóa ngoại trỏ về `categories.id`. |
| `venue_id` | BIGINT UNSIGNED | FK | No | - | Khóa ngoại trỏ về `venues.id`. |
| `title` | VARCHAR(255) | - | No | - | Tiêu đề sự kiện. |
| `slug` | VARCHAR(255) | UK | No | - | Đường dẫn URL duy nhất. |
| `description` | TEXT | - | No | - | Nội dung mô tả chi tiết sự kiện. |
| `banner_path` | VARCHAR(255) | - | Yes | NULL | Đường dẫn file ảnh banner trong storage. |
| `start_time` | DATETIME | - | No | - | Thời gian bắt đầu sự kiện. |
| `end_time` | DATETIME | - | No | - | Thời gian kết thúc sự kiện. |
| `status` | VARCHAR(50) | - | No | 'draft' | Trạng thái: `draft`, `pending`, `published`, `cancelled`. |
| `created_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm tạo sự kiện. |
| `updated_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm cập nhật sự kiện. |
| `deleted_at` | TIMESTAMP | - | Yes | NULL | Thời điểm xóa mềm (Soft Delete). |

#### 3.5. Bảng `ticket_types`
Quản lý các hạng vé, giá bán, tổng số lượng phát hành và số lượng đã bán.

| Tên Cột | Kiểu Dữ Liệu | Khóa | Null? | Mặc Định | Ý Nghĩa Nghiệp Vụ |
|---|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK | No | Auto Increment | Khóa chính duy nhất. |
| `event_id` | BIGINT UNSIGNED | FK | No | - | Khóa ngoại trỏ về `events.id`. |
| `name` | VARCHAR(255) | - | No | - | Tên hạng vé (Ví dụ: 'VIP', 'Standard'). |
| `price` | DECIMAL(12,0) | - | No | - | Đơn giá vé tính bằng VNĐ. |
| `quantity` | INT UNSIGNED | - | No | - | Tổng số lượng vé phát hành. |
| `sold` | INT UNSIGNED | - | No | 0 | Số lượng vé đã đặt/bán. |
| `sale_start` | DATETIME | - | No | - | Thời điểm mở bán vé trực tuyến. |
| `sale_end` | DATETIME | - | No | - | Thời điểm đóng cổng bán vé. |
| `max_per_order` | INT UNSIGNED | - | No | 10 | Giới hạn vé tối đa được mua trong 1 đơn. |
| `created_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm tạo loại vé. |
| `updated_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm cập nhật loại vé. |

#### 3.6. Bảng `orders`
Lưu trữ đơn hàng, khách mua, tổng tiền và hạn giữ chỗ.

| Tên Cột | Kiểu Dữ Liệu | Khóa | Null? | Mặc Định | Ý Nghĩa Nghiệp Vụ |
|---|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK | No | Auto Increment | Khóa chính duy nhất. |
| `user_id` | BIGINT UNSIGNED | FK | No | - | Khóa ngoại trỏ về `users.id`. |
| `order_code` | VARCHAR(50) | UK | No | - | Mã đơn hàng duy nhất (`ORD-XXXXXXXX`). |
| `customer_name` | VARCHAR(255) | - | No | - | Tên người nhận vé. |
| `customer_email` | VARCHAR(255) | - | No | - | Email nhận vé điện tử. |
| `customer_phone` | VARCHAR(20) | - | Yes | NULL | Số điện thoại liên hệ. |
| `total_amount` | DECIMAL(12,0) | - | No | - | Tổng tiền đơn hàng (VNĐ). |
| `status` | VARCHAR(50) | - | No | 'pending' | Trạng thái: `pending`, `paid`, `cancelled`, `refunded`. |
| `payment_method` | VARCHAR(50) | - | Yes | NULL | Phương thức: `bank_transfer`, `momo`, `card`. |
| `paid_at` | DATETIME | - | Yes | NULL | Thời điểm hoàn tất thanh toán. |
| `expires_at` | DATETIME | - | Yes | NULL | Hạn giữ chỗ = `created_at + 15 phút`. |
| `created_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm tạo đơn hàng. |
| `updated_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm cập nhật đơn hàng. |

#### 3.7. Bảng `order_items`
Lưu chi tiết các loại vé trong đơn hàng kèm đơn giá tại thời điểm mua.

| Tên Cột | Kiểu Dữ Liệu | Khóa | Null? | Mặc Định | Ý Nghĩa Nghiệp Vụ |
|---|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK | No | Auto Increment | Khóa chính duy nhất. |
| `order_id` | BIGINT UNSIGNED | FK | No | - | Khóa ngoại trỏ về `orders.id`. |
| `ticket_type_id` | BIGINT UNSIGNED | FK | No | - | Khóa ngoại trỏ về `ticket_types.id`. |
| `quantity` | INT UNSIGNED | - | No | - | Số lượng vé đặt mua. |
| `unit_price` | DECIMAL(12,0) | - | No | - | Đơn giá vé lúc đặt hàng (VNĐ). |
| `subtotal` | DECIMAL(12,0) | - | No | - | Thành tiền = `quantity * unit_price`. |
| `created_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm tạo dòng đơn hàng. |
| `updated_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm cập nhật. |

#### 3.8. Bảng `tickets`
Lưu trữ vé điện tử với mã vé duy nhất phục vụ Check-in tại cổng.

| Tên Cột | Kiểu Dữ Liệu | Khóa | Null? | Mặc Định | Ý Nghĩa Nghiệp Vụ |
|---|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK | No | Auto Increment | Khóa chính duy nhất. |
| `order_item_id` | BIGINT UNSIGNED | FK | No | - | Khóa ngoại trỏ về `order_items.id`. |
| `ticket_code` | VARCHAR(50) | UK | No | - | Mã tra cứu vé độc nhất (`TKT-XXXXXXXXXX`). |
| `status` | VARCHAR(50) | - | No | 'valid' | Trạng thái: `valid` (hợp lệ), `used` (đã dùng), `cancelled`. |
| `checked_in_at` | DATETIME | - | Yes | NULL | Thời điểm quét mã check-in vào cửa. |
| `created_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm sinh vé điện tử. |
| `updated_at` | TIMESTAMP | - | Yes | CURRENT_TIMESTAMP | Thời điểm cập nhật trạng thái vé. |

---

### 4. Kiểm soát Đồng thời & Ràng buộc Nghiệp vụ

1. **Khóa bi quan (`lockForUpdate()`)**:
   Khi khách hàng tạo đơn hàng, hệ thống thực thi trong một `DB::transaction()` và gọi `lockForUpdate()` trên các bản ghi `ticket_types` được chọn. Bất kỳ giao dịch nào khác cố gắng mua vé cùng loại sẽ phải xếp hàng chờ cho đến khi transaction hoàn tất, ngăn chặn 100% rủi ro bán vượt tồn kho (*race condition*).
2. **Quy trình giữ chỗ 15 phút**:
   Sau khi đơn hàng `pending` được khởi tạo, số lượng vé được tạm khóa (`sold += quantity`). Nếu sau 15 phút chưa thanh toán:
   - Command định kỳ `php artisan orders:expire-pending` quét và hủy đơn, trừ ngược `sold`.
   - Lazy Check kiểm tra tức thời khi khách thanh toán trễ để từ chối giao dịch.
3. **Bảo vệ toàn vẹn tham chiếu**:
   Các danh mục (`categories`) và địa điểm (`venues`) áp dụng `ON DELETE RESTRICT` để ngăn việc xóa dữ liệu đang được các sự kiện tham chiếu.

---

### 5. Chiến lược Đánh chỉ mục (Indexing Strategy)

- **Unique Indexes**:
  - `users(email)`
  - `categories(slug)`, `categories(name)`
  - `events(slug)`
  - `orders(order_code)`
  - `tickets(ticket_code)`
- **Composite Indexes**:
  - `events(status, start_time)`: Tối ưu lọc sự kiện đang mở bán theo ngày.
  - `orders(user_id, status)`: Tối ưu tải lịch sử đơn hàng của khách.
- **Foreign Key Indexes**:
  - Tự động tạo chỉ mục trên các cột `_id` phục vụ JOIN và eager loading chống N+1 query.
