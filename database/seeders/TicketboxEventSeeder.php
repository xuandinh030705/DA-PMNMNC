<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Nạp dữ liệu sự kiện thật từ Ticketbox (nguồn: repo EzTicket-Website,
 * nhánh develop-Frontend, thư mục importDataTicketBox) vào schema của ứng dụng.
 *
 * Dữ liệu đã được chuẩn hoá sẵn trong database/data/ticketbox/events.json
 * (kèm banner trong database/data/ticketbox/images). Seeder chỉ đọc dữ liệu
 * cục bộ nên chạy được khi không có mạng.
 */
class TicketboxEventSeeder extends Seeder
{
    /**
     * Danh mục dùng cho dữ liệu Ticketbox (slug => thông tin hiển thị).
     */
    private const CATEGORIES = [
        'am-nhac-hoa-nhac' => [
            'name' => 'Âm nhạc & Hòa nhạc',
            'description' => 'Các buổi biểu diễn âm nhạc đỉnh cao, liveshow và concert hoành tráng.',
        ],
        'nghe-thuat-san-khau' => [
            'name' => 'Nghệ thuật & Sân khấu',
            'description' => 'Vở kịch, triển lãm tranh, hòa nhạc thính phòng và nghệ thuật đương đại.',
        ],
        'hoi-thao-workshop' => [
            'name' => 'Hội thảo & Workshop',
            'description' => 'Các sự kiện chia sẻ kiến thức chuyên sâu, kết nối doanh nghiệp và nâng cao kỹ năng.',
        ],
        'the-thao-giai-dau' => [
            'name' => 'Thể thao & Giải đấu',
            'description' => 'Các giải chạy marathon, bóng đá, e-sports và hoạt động thể thao sôi động.',
        ],
        'cong-nghe-trien-lam' => [
            'name' => 'Công nghệ & Triển lãm',
            'description' => 'Triển lãm thiết bị công nghệ hiện đại, ngày hội khởi nghiệp và đổi mới sáng tạo.',
        ],
        'tham-quan-trai-nghiem' => [
            'name' => 'Tham quan & Trải nghiệm',
            'description' => 'Tour tham quan, trải nghiệm văn hoá và các hoạt động khám phá địa phương.',
        ],
    ];

    public function run(): void
    {
        $dataFile = database_path('data/ticketbox/events.json');

        if (! File::exists($dataFile)) {
            $this->command?->warn('TicketboxEventSeeder: không tìm thấy '.$dataFile.', bỏ qua.');

            return;
        }

        $payload = json_decode(File::get($dataFile), true, 512, JSON_THROW_ON_ERROR);
        $eventsData = $payload['events'] ?? [];

        if ($eventsData === []) {
            $this->command?->warn('TicketboxEventSeeder: events.json không có sự kiện nào.');

            return;
        }

        $categories = $this->seedCategories();
        $offset = $this->dateOffset($eventsData);
        $imageSourceDir = database_path('data/ticketbox/images');

        $created = 0;
        $updated = 0;

        foreach ($eventsData as $data) {
            $organizer = $this->seedOrganizer($data['organizer']);
            $venue = $this->seedVenue($data['venue'], $data['ticket_types']);
            $category = $categories[$data['category']] ?? reset($categories);

            $start = Carbon::parse($data['start_time'])->addDays($offset);
            $end = Carbon::parse($data['end_time'])->addDays($offset);
            if ($end->lessThanOrEqualTo($start)) {
                $end = $start->copy()->addHours(2);
            }

            $bannerPath = $this->publishBanner($data, $imageSourceDir);

            $event = Event::withTrashed()->firstOrNew(['slug' => $data['slug']]);
            $isNew = ! $event->exists;

            if (! $isNew && ! $event->trashed() && $event->start_time?->isFuture()) {
                // Sự kiện vẫn đang diễn ra: giữ nguyên lịch đã nạp trước đó.
                $start = $event->start_time;
                $end = $event->end_time ?? $end;
            }

            $event->fill([
                'organizer_id' => $organizer->id,
                'category_id' => $category->id,
                'venue_id' => $venue->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'start_time' => $start,
                'end_time' => $end,
                'status' => Event::STATUS_PUBLISHED,
            ]);

            if ($bannerPath !== null) {
                $event->banner_path = $bannerPath;
            }

            if (! $isNew && $event->trashed()) {
                $event->restore();
            }

            $event->save();
            $isNew ? $created++ : $updated++;

            $this->seedTicketTypes($event, $data['ticket_types'], $isNew);
        }

        $this->command?->info(sprintf(
            'TicketboxEventSeeder: %d sự kiện mới, %d sự kiện cập nhật (nguồn: %s).',
            $created,
            $updated,
            $payload['source'] ?? 'Ticketbox'
        ));
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $categories = [];

        foreach (self::CATEGORIES as $slug => $info) {
            $categories[$slug] = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $info['name'], 'description' => $info['description']]
            );
        }

        return $categories;
    }

    /**
     * Dịch toàn bộ lịch sự kiện về tương lai gần để bản demo luôn có sự kiện
     * sắp diễn ra, vẫn giữ nguyên khoảng cách giữa các sự kiện.
     *
     * @param  array<int, array<string, mixed>>  $eventsData
     */
    private function dateOffset(array $eventsData): int
    {
        $earliest = collect($eventsData)
            ->map(fn (array $event) => Carbon::parse($event['start_time']))
            ->min();

        if (! $earliest) {
            return 0;
        }

        $anchor = now()->addDays(5)->startOfDay();

        return max(0, (int) round($earliest->copy()->startOfDay()->diffInDays($anchor)));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function seedOrganizer(array $data): User
    {
        return User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make('password'),
                'role' => User::ROLE_ORGANIZER,
                'is_active' => true,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $venueData
     * @param  array<int, array<string, mixed>>  $ticketTypes
     */
    private function seedVenue(array $venueData, array $ticketTypes): Venue
    {
        $capacity = (int) ($venueData['capacity'] ?? 0);
        if ($capacity <= 0) {
            $capacity = (int) collect($ticketTypes)->sum('quantity');
        }

        return Venue::firstOrCreate(
            ['name' => $venueData['name']],
            [
                'address' => $venueData['address'],
                'city' => $venueData['city'],
                'capacity' => max(1, $capacity),
            ]
        );
    }

    /**
     * Sao chép banner từ thư mục dữ liệu sang storage công khai và trả về
     * đường dẫn tương đối dùng cho cột events.banner_path.
     *
     * @param  array<string, mixed>  $data
     */
    private function publishBanner(array $data, string $imageSourceDir): ?string
    {
        if (empty($data['banner'])) {
            return null;
        }

        $source = $imageSourceDir.'/'.basename($data['banner']);
        if (! File::exists($source)) {
            return null;
        }

        $target = $data['banner'];
        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, File::get($source));
        }

        return $target;
    }

    /**
     * @param  array<int, array<string, mixed>>  $tiers
     */
    private function seedTicketTypes(Event $event, array $tiers, bool $isNew): void
    {
        $saleStart = $event->start_time->copy()->subDays(30)->max(now()->subDays(10));
        $saleEnd = $event->start_time->copy()->subHour();

        foreach ($tiers as $tier) {
            $existing = TicketType::where('event_id', $event->id)
                ->where('name', $tier['name'])
                ->first();

            if ($existing) {
                if ($isNew) {
                    continue;
                }

                // Chỉ cập nhật giá/tồn kho khi vé chưa bán ra, tránh phá vỡ đơn hàng demo.
                if ($existing->sold === 0) {
                    $existing->update([
                        'price' => $tier['price'],
                        'quantity' => $tier['quantity'],
                        'max_per_order' => $tier['max_per_order'],
                        'sale_start' => $saleStart,
                        'sale_end' => $saleEnd,
                    ]);
                }

                continue;
            }

            TicketType::create([
                'event_id' => $event->id,
                'name' => $tier['name'],
                'price' => $tier['price'],
                'quantity' => $tier['quantity'],
                'sold' => 0,
                'sale_start' => $saleStart,
                'sale_end' => $saleEnd,
                'max_per_order' => $tier['max_per_order'],
            ]);
        }
    }
}
