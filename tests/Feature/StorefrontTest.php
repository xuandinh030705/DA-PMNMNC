<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
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
}
