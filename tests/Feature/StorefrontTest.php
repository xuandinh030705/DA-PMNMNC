<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_browse_published_events_on_home_page(): void
    {
        $publishedEvent = Event::factory()->published()->create([
            'title' => 'Đại Nhạc Hội Mùa Hè 2026',
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Đại Nhạc Hội Mùa Hè 2026');
    }

    public function test_can_search_events_by_title_keyword(): void
    {
        $matching = Event::factory()->published()->create([
            'title' => 'Chạy Bộ Marathon Hà Nội',
        ]);
        $other = Event::factory()->published()->create([
            'title' => 'Đêm Nhạc Jazz Sài Gòn',
        ]);

        $response = $this->get(route('home', ['q' => 'Marathon']));

        $response->assertStatus(200);
        $response->assertSee('Chạy Bộ Marathon Hà Nội');
        $response->assertDontSee('Đêm Nhạc Jazz Sài Gòn');
    }

    public function test_can_filter_events_by_category_and_city(): void
    {
        $catSport = Category::factory()->create(['name' => 'Thể Thao']);
        $catMusic = Category::factory()->create(['name' => 'Âm Nhạc']);

        $venueHN = Venue::factory()->create(['city' => 'Hà Nội']);
        $venueSG = Venue::factory()->create(['city' => 'TP. Hồ Chí Minh']);

        $eventSportHN = Event::factory()->published()->create([
            'title' => 'Giải Bóng Đá Hà Nội',
            'category_id' => $catSport->id,
            'venue_id' => $venueHN->id,
        ]);

        $eventMusicSG = Event::factory()->published()->create([
            'title' => 'Liveshow Rock Sài Gòn',
            'category_id' => $catMusic->id,
            'venue_id' => $venueSG->id,
        ]);

        $response = $this->get(route('home', [
            'category' => $catSport->id,
            'city' => 'Hà Nội',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Giải Bóng Đá Hà Nội');
        $response->assertDontSee('Liveshow Rock Sài Gòn');
    }

    public function test_unpublished_event_returns_404_on_storefront(): void
    {
        $draftEvent = Event::factory()->draft()->create([
            'title' => 'Sự Kiện Nháp Bí Mật',
        ]);

        $response = $this->get(route('events.show', $draftEvent->slug));

        $response->assertStatus(404);
    }

    public function test_can_view_published_event_details_and_displays_ticket_type_zone_and_description(): void
    {
        $event = Event::factory()->published()->create([
            'title' => 'Đại Nhạc Hội Mùa Đông 2026',
        ]);

        TicketType::factory()->create([
            'event_id' => $event->id,
            'name' => 'Vé VIP Khán Đài A',
            'zone' => 'Khu A - Tầng 1',
            'description' => 'Bao gồm nước uống và quà lưu niệm',
            'price' => 500000,
            'quantity' => 100,
            'sold' => 0,
            'sale_start' => now()->subDay(),
            'sale_end' => now()->addDays(5),
        ]);

        $response = $this->get(route('events.show', $event->slug));

        $response->assertStatus(200);
        $response->assertSee('Đại Nhạc Hội Mùa Đông 2026');
        $response->assertSee('Vé VIP Khán Đài A');
        $response->assertSee('Khu A - Tầng 1');
        $response->assertSee('Bao gồm nước uống và quà lưu niệm');
    }

    public function test_storefront_price_sorting_puts_events_without_ticket_types_last(): void
    {
        $noTierEvent = Event::factory()->published()->create(['title' => 'Sự Kiện Chưa Có Vé']);
        $cheapEvent = Event::factory()->published()->create(['title' => 'Sự Kiện Giá Rẻ']);
        $expensiveEvent = Event::factory()->published()->create(['title' => 'Sự Kiện Giá Cao']);

        TicketType::factory()->create([
            'event_id' => $cheapEvent->id,
            'price' => 100000,
        ]);
        TicketType::factory()->create([
            'event_id' => $expensiveEvent->id,
            'price' => 500000,
        ]);

        $ascResponse = $this->get(route('home', ['sort' => 'price_asc']));
        $ascResponse->assertOk();
        $ascIds = $ascResponse->viewData('events')->pluck('id')->all();
        $this->assertSame([$cheapEvent->id, $expensiveEvent->id, $noTierEvent->id], $ascIds);

        $descResponse = $this->get(route('home', ['sort' => 'price_desc']));
        $descResponse->assertOk();
        $descIds = $descResponse->viewData('events')->pluck('id')->all();
        $this->assertSame([$expensiveEvent->id, $cheapEvent->id, $noTierEvent->id], $descIds);
    }
}
