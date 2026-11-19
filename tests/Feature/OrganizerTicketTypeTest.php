<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerTicketTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_contains_zone_and_description_inputs(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->draft()->create(['organizer_id' => $organizer->id]);

        $response = $this->actingAs($organizer)
            ->get(route('organizer.events.ticket-types.create', $event));

        $response->assertOk();
        $response->assertSee('name="zone"', false);
        $response->assertSee('name="description"', false);
        $response->assertSee('Quyền lợi của loại vé');
    }

    public function test_organizer_can_store_zone_and_description(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->draft()->create(['organizer_id' => $organizer->id]);

        $response = $this->actingAs($organizer)->post(
            route('organizer.events.ticket-types.store', $event),
            [
                'name' => 'Vé VIP',
                'zone' => 'Khu A - Sát sân khấu',
                'description' => 'Bao gồm nước uống, quà tặng và gặp gỡ nghệ sĩ.',
                'price' => 500000,
                'quantity' => 100,
                'sale_start' => now()->subDay()->format('Y-m-d H:i:s'),
                'sale_end' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'max_per_order' => 4,
            ],
        );

        $response->assertRedirect(route('organizer.events.show', $event));

        $this->assertDatabaseHas('ticket_types', [
            'event_id' => $event->id,
            'name' => 'Vé VIP',
            'zone' => 'Khu A - Sát sân khấu',
            'description' => 'Bao gồm nước uống, quà tặng và gặp gỡ nghệ sĩ.',
        ]);
    }

    public function test_event_page_displays_zone_and_description_of_each_ticket_type(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);
        TicketType::factory()->create([
            'event_id' => $event->id,
            'name' => 'Vé Tiêu Chuẩn',
            'zone' => 'Khu B',
            'description' => 'Lối vào khu vực khán đài B.',
        ]);

        $response = $this->actingAs($organizer)->get(route('organizer.events.show', $event));

        $response->assertOk();
        $response->assertSee('Khu vực');
        $response->assertSee('Khu B');
        $response->assertSee('Lối vào khu vực khán đài B.');
    }

    public function test_edit_form_prefills_zone_and_description(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->draft()->create(['organizer_id' => $organizer->id]);
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'name' => 'Vé VIP',
            'zone' => 'Khu A - Sát sân khấu',
            'description' => 'Bao gồm nước uống, quà tặng và gặp gỡ nghệ sĩ.',
        ]);

        $response = $this->actingAs($organizer)
            ->get(route('organizer.ticket-types.edit', $ticketType));

        $response->assertOk();
        $response->assertSee('Khu A - Sát sân khấu');
        $response->assertSee('Bao gồm nước uống, quà tặng và gặp gỡ nghệ sĩ.');
    }
}
