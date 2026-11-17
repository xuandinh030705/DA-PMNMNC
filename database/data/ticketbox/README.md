# Dữ liệu Ticketbox

Thư mục này chứa bộ dữ liệu sự kiện thật được chuyển từ repository
[EzTicket-Website](https://github.com/dangquang2k5/EzTicket-Website) về đúng
schema của ứng dụng Laravel trong repo này.

- Nguồn: nhánh `develop-Frontend`, commit `e6db91a`, thư mục `importDataTicketBox/`
  (`ticketbox_merged.json` do nhóm EzTicket thu thập từ ticketbox.vn).
- Bản gốc được giữ nguyên tại `source/ticketbox_merged.json` để đối chiếu.
- Ảnh banner được tải từ Cloudinary của bộ dữ liệu gốc, xử lý thành ảnh ngang
  1600×900 (nền mờ + poster gốc ở giữa) và lưu tại `images/`.

## Cấu trúc

```
database/data/ticketbox/
├── source/ticketbox_merged.json  # dữ liệu gốc (69 sự kiện)
├── build-events.php              # script chuyển đổi dữ liệu gốc -> events.json
├── events.json                   # 14 sự kiện đã map sang schema của app
└── images/                       # banner 16:9 (được copy sang storage khi seed)
```

## Cách dùng

Nạp dữ liệu vào cơ sở dữ liệu hiện tại (không xoá dữ liệu cũ, chạy lại không nhân đôi):

```powershell
php artisan db:seed --class=TicketboxEventSeeder
```

Seeder cũng đã được khai báo trong `DatabaseSeeder`, nên khi cài mới chỉ cần:

```powershell
php artisan migrate:fresh --seed
```

Tạo lại `events.json` và ảnh từ dữ liệu gốc (cần mạng để tải banner):

```powershell
php database/data/ticketbox/build-events.php

# Chỉ tạo lại events.json, giữ nguyên ảnh đã tải:
php database/data/ticketbox/build-events.php --skip-images
```

## Ánh xạ dữ liệu

| Ticketbox | Ứng dụng |
| --- | --- |
| `title`, `url` | `events.title`, `events.slug` |
| `description` (HTML) | `events.description` (bỏ thẻ HTML, chuẩn hoá kiểu chữ, giới hạn ~1400 ký tự) |
| `bannerURL` | `events.banner_path` (`events/ticketbox/{slug}.jpg` trong `storage/app/public`) |
| `venue`, `address` | `venues.name`, `venues.address`; `venues.city` suy ra từ phần cuối địa chỉ |
| `orgName` | tài khoản `users` role `organizer` (`{slug}@ticketbox.local`, mật khẩu `password`) |
| `categoriesV2` + nội dung | `categories` (âm nhạc, sân khấu, hội thảo, thể thao, công nghệ, tham quan) |
| `showings[].ticketTypes[]` | `ticket_types` (gộp hạng vé trùng tên/giá, tối đa 6 hạng/sự kiện) |
| `startTime`, `endTime` | `events.start_time`, `events.end_time` (đổi sang giờ Việt Nam) |

## Quy ước khi chuyển đổi

- **Thời gian**: giữ nguyên giờ địa phương Việt Nam và khoảng cách giữa các sự kiện.
  Khi seed, toàn bộ lịch được dịch về tương lai (sự kiện sớm nhất diễn ra sau
  khoảng 5 ngày) để trang bán vé luôn có sự kiện sắp diễn ra.
- **Giá vé**: giữ nguyên giá thật; nếu một hạng có nhiều đợt (early bird, final
  round...) thì lấy giá thấp nhất.
- **Tồn kho**: dữ liệu gốc không công bố số lượng vé, nên `ticket_types.quantity`
  được chia theo sức chứa ước tính của từng địa điểm (ví dụ Hội trường Quần Ngựa
  588 chỗ) với tỉ lệ 45/25/15/8/5/2% theo thứ tự giá tăng dần.
- **Mô tả**: giữ nguyên toàn bộ nội dung chữ; chỉ chuẩn hoá cách hiển thị: chuyển
  chữ "đậm/nghiêng" Unicode (𝐇, 𝐀, 𝟐...) và ký tự full-width về chữ thường cùng
  kiểu chữ với website, bỏ ký tự vô hình và emoji/biểu tượng trang trí, gộp khoảng
  trắng thừa. Trang chi tiết chia mô tả thành các đoạn ngắn tại dấu kết thúc câu
  (không thêm/bớt từ nào).
- **Vé miễn phí**: bỏ qua các hạng vé giá 0.
- Ảnh banner và logo ban tổ chức thuộc bản quyền của ban tổ chức sự kiện, chỉ dùng
  cho mục đích học tập/demo.
