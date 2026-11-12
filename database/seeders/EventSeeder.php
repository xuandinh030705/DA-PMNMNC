<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $org1 = User::where('email', 'organizer1@example.com')->first();
        $org2 = User::where('email', 'organizer2@example.com')->first();

        $catMusic = Category::where('slug', 'like', '%am-nhac%')->first() ?? Category::first();
        $catTech = Category::where('slug', 'like', '%cong-nghe%')->first() ?? Category::first();
        $catSport = Category::where('slug', 'like', '%the-thao%')->first() ?? Category::first();
        $catArt = Category::where('slug', 'like', '%nghe-thuat%')->first() ?? Category::first();
        $catWorkshop = Category::where('slug', 'like', '%hoi-thao%')->first() ?? Category::first();

        $venues = Venue::all();
        $v1 = $venues[0] ?? Venue::first();
        $v2 = $venues[1] ?? Venue::first();
        $v3 = $venues[2] ?? Venue::first();
        $v4 = $venues[3] ?? Venue::first();

        $eventsData = [
            // 1. Published (Music)
            [
                'organizer_id' => $org1->id,
                'category_id' => $catMusic->id,
                'venue_id' => $v1->id,
                'title' => 'Đại Nhạc Hội Mùa Hè - Summer Vibes 2026',
                'description' => 'Đại nhạc hội ngoài trời quy tụ dàn nghệ sĩ hàng đầu Việt Nam và quốc tế. Trải nghiệm hệ thống âm thanh ánh sáng chuẩn concert quốc tế cùng không gian bùng nổ năng lượng tuổi trẻ.',
                'start_time' => now()->addDays(15)->setHour(19)->setMinute(0),
                'end_time' => now()->addDays(15)->setHour(23)->setMinute(0),
                'status' => Event::STATUS_PUBLISHED,
                'ticket_types' => [
                    ['name' => 'Vé GA (Đứng Tự Do)', 'zone' => 'Khu Standing GA', 'description' => 'Khu vực đứng tự do ngoài trời, vào cổng tiêu chuẩn.', 'price' => 350000, 'quantity' => 1500, 'max_per_order' => 10],
                    ['name' => 'Vé VIP (Khu Vực Gần Sân Khấu)', 'zone' => 'Khu VIP A', 'description' => 'Vị trí gần sân khấu, có lối check-in ưu tiên và 1 đồ uống.', 'price' => 950000, 'quantity' => 400, 'max_per_order' => 5],
                    ['name' => 'Vé VVIP (Trọn Gói Quà Tặng & Backstage)', 'zone' => 'Khu VVIP Lounge', 'description' => 'Ghế ngồi riêng sát sân khấu, tặng bộ quà lưu niệm và vào khu vực lounge.', 'price' => 2200000, 'quantity' => 100, 'max_per_order' => 4],
                ],
            ],
            // 2. Published (Tech)
            [
                'organizer_id' => $org2->id,
                'category_id' => $catTech->id,
                'venue_id' => $v2->id,
                'title' => 'Diễn Đàn Trí Tuệ Nhân Tạo & Chuyển Đổi Số AI Summit',
                'description' => 'Quy tụ hơn 50 chuyên gia công nghệ hàng đầu thế giới thảo luận về GenAI, tự động hóa và ứng dụng công nghệ đột phá trong kinh doanh và đời sống.',
                'start_time' => now()->addDays(20)->setHour(8)->setMinute(30),
                'end_time' => now()->addDays(21)->setHour(17)->setMinute(30),
                'status' => Event::STATUS_PUBLISHED,
                'ticket_types' => [
                    ['name' => 'Vé Tiêu Chuẩn (2 Ngày)', 'price' => 500000, 'quantity' => 800, 'max_per_order' => 10],
                    ['name' => 'Vé Doanh Nghiệp (Bao Gồm Networking & Tiệc Trưa)', 'price' => 1800000, 'quantity' => 200, 'max_per_order' => 5],
                ],
            ],
            // 3. Published (Sport)
            [
                'organizer_id' => $org1->id,
                'category_id' => $catSport->id,
                'venue_id' => $v3->id,
                'title' => 'Giải Chạy Marathon Quốc Tế Thành Phố Đà Nẵng 2026',
                'description' => 'Cung đường chạy ven biển đẹp nhất Việt Nam, chào đón hàng nghìn vận động viên chuyên nghiệp và phong trào chinh phục các cự ly 10km, 21km và 42km.',
                'start_time' => now()->addDays(25)->setHour(4)->setMinute(30),
                'end_time' => now()->addDays(25)->setHour(11)->setMinute(0),
                'status' => Event::STATUS_PUBLISHED,
                'ticket_types' => [
                    ['name' => 'Cự Ly 10KM (Bib + Huy Chương + Áo)', 'price' => 450000, 'quantity' => 1000, 'max_per_order' => 6],
                    ['name' => 'Cự Ly 21KM Bán Marathon', 'price' => 650000, 'quantity' => 800, 'max_per_order' => 6],
                    ['name' => 'Cự Ly 42KM Full Marathon', 'price' => 850000, 'quantity' => 500, 'max_per_order' => 4],
                ],
            ],
            // 4. Published (Art)
            [
                'organizer_id' => $org2->id,
                'category_id' => $catArt->id,
                'venue_id' => $v4->id,
                'title' => 'Đêm Hòa Nhạc Thính Phòng Giao Hưởng Bốn Mùa',
                'description' => 'Dưới sự chỉ huy của nhạc trưởng quốc tế tài ba cùng dàn nhạc giao hưởng quốc gia, khán giả sẽ được đắm chìm trong những kiệt tác âm nhạc cổ điển bất hủ.',
                'start_time' => now()->addDays(12)->setHour(20)->setMinute(0),
                'end_time' => now()->addDays(12)->setHour(22)->setMinute(30),
                'status' => Event::STATUS_PUBLISHED,
                'ticket_types' => [
                    ['name' => 'Hạng Bạc (Tầng 2)', 'price' => 300000, 'quantity' => 300, 'max_per_order' => 8],
                    ['name' => 'Hạng Vàng (Tầng 1 Trung Tâm)', 'price' => 700000, 'quantity' => 200, 'max_per_order' => 4],
                ],
            ],
            // 5. Published (Workshop)
            [
                'organizer_id' => $org1->id,
                'category_id' => $catWorkshop->id,
                'venue_id' => $v1->id,
                'title' => 'Hội Thảo Khởi Nghiệp Đổi Mới Sáng Tạo & Đầu Tư Thiên Thần',
                'description' => 'Cơ hội lắng nghe chia sẻ từ các nhà sáng lập kỳ lân công nghệ và các quỹ đầu tư mạo hiểm hàng đầu khu vực Đông Nam Á.',
                'start_time' => now()->addDays(18)->setHour(9)->setMinute(0),
                'end_time' => now()->addDays(18)->setHour(16)->setMinute(30),
                'status' => Event::STATUS_PUBLISHED,
                'ticket_types' => [
                    ['name' => 'Vé Sinh Viên & Khởi Nghiệp', 'price' => 150000, 'quantity' => 500, 'max_per_order' => 5],
                    ['name' => 'Vé Tiêu Chuẩn Đầy Đủ Tài Liệu', 'price' => 400000, 'quantity' => 300, 'max_per_order' => 8],
                ],
            ],
            // 6. Draft (1 draft)
            [
                'organizer_id' => $org1->id,
                'category_id' => $catMusic->id,
                'venue_id' => $v2->id,
                'title' => 'Lễ Hội Âm Nhạc Indie & Acoustic Dưới Trăng (Bản Nháp)',
                'description' => 'Sự kiện âm nhạc acoustic không gian mở dành cho những tâm hồn yêu mến giai điệu mộc mạc và chân thành.',
                'start_time' => now()->addDays(35)->setHour(18)->setMinute(0),
                'end_time' => now()->addDays(35)->setHour(22)->setMinute(0),
                'status' => Event::STATUS_DRAFT,
                'ticket_types' => [
                    ['name' => 'Vé Tiêu Chuẩn Dự Kiến', 'price' => 200000, 'quantity' => 500, 'max_per_order' => 5],
                ],
            ],
            // 7. Pending (1 pending)
            [
                'organizer_id' => $org2->id,
                'category_id' => $catWorkshop->id,
                'venue_id' => $v4->id,
                'title' => 'Diễn Đàn Lãnh Đạo Trẻ & Kỹ Năng Đàm Phán Toàn Cầu (Chờ Duyệt)',
                'description' => 'Chương trình đào tạo đặc biệt phát triển tư duy phản biện, kỹ năng thương lượng và kết nối mạng lưới các nhà lãnh đạo tương lai.',
                'start_time' => now()->addDays(28)->setHour(8)->setMinute(30),
                'end_time' => now()->addDays(28)->setHour(17)->setMinute(0),
                'status' => Event::STATUS_PENDING,
                'ticket_types' => [
                    ['name' => 'Vé Học Viên Chính Thức', 'price' => 600000, 'quantity' => 150, 'max_per_order' => 4],
                    ['name' => 'Vé VIP Kèm Mentor 1-1', 'price' => 1500000, 'quantity' => 30, 'max_per_order' => 2],
                ],
            ],
            // 8. Cancelled (1 cancelled)
            [
                'organizer_id' => $org1->id,
                'category_id' => $catSport->id,
                'venue_id' => $v3->id,
                'title' => 'Giải Đua Xe Địa Hình Mở Rộng 2026 (Đã Hủy Do Thời Tiết)',
                'description' => 'Giải thi đấu địa hình chuyên nghiệp. Sự kiện đã bị hủy để đảm bảo an toàn tuyệt đối cho người tham gia.',
                'start_time' => now()->addDays(10)->setHour(8)->setMinute(0),
                'end_time' => now()->addDays(10)->setHour(17)->setMinute(0),
                'status' => Event::STATUS_CANCELLED,
                'ticket_types' => [
                    ['name' => 'Vé Khán Đài Xem Trực Tiếp', 'price' => 250000, 'quantity' => 600, 'max_per_order' => 6],
                ],
            ],
        ];

        foreach ($eventsData as $data) {
            $ticketTypes = $data['ticket_types'];
            unset($data['ticket_types']);

            $slug = Str::slug($data['title']);
            $event = Event::firstOrCreate(
                ['slug' => $slug],
                $data
            );

            foreach ($ticketTypes as $tt) {
                TicketType::firstOrCreate(
                    [
                        'event_id' => $event->id,
                        'name' => $tt['name'],
                    ],
                    [
                        'zone' => $tt['zone'] ?? null,
                        'description' => $tt['description'] ?? null,
                        'price' => $tt['price'],
                        'quantity' => $tt['quantity'],
                        'sold' => 0,
                        'sale_start' => now()->subDays(5),
                        'sale_end' => $event->start_time,
                        'max_per_order' => $tt['max_per_order'],
                    ]
                );
            }
        }
    }
}
