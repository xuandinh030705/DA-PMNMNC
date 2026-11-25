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

    public function test_organizer_can_update_and_clear_zone_and_description(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->draft()->create(['organizer_id' => $organizer->id]);
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'name' => 'Vé VIP',
            'zone' => 'Khu A',
            'description' => 'Quyền lợi cũ.',
            'price' => 500000,
            'quantity' => 50,
            'max_per_order' => 4,
        ]);

        $basePayload = [
            'name' => 'Vé VIP Nâng Cấp',
            'price' => 650000,
            'quantity' => 60,
            'sale_start' => now()->subDay()->format('Y-m-d H:i:s'),
            'sale_end' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'max_per_order' => 4,
        ];

        $this->actingAs($organizer)
            ->put(route('organizer.ticket-types.update', $ticketType), [
                ...$basePayload,
                'zone' => 'Khu VVIP - Hàng đầu',
                'description' => 'Bao gồm check-in lối riêng và hậu trường.',
            ])
            ->assertRedirect(route('organizer.events.show', $event));

        $this->assertDatabaseHas('ticket_types', [
            'id' => $ticketType->id,
            'name' => 'Vé VIP Nâng Cấp',
            'zone' => 'Khu VVIP - Hàng đầu',
            'description' => 'Bao gồm check-in lối riêng và hậu trường.',
        ]);

        $this->actingAs($organizer)
            ->put(route('organizer.ticket-types.update', $ticketType), [
                ...$basePayload,
                'zone' => null,
                'description' => null,
            ])
            ->assertRedirect(route('organizer.events.show', $event));

        $this->assertDatabaseHas('ticket_types', [
            'id' => $ticketType->id,
            'zone' => null,
            'description' => null,
        ]);
    }

    public function test_validation_rejects_overlong_zone_and_description(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->draft()->create(['organizer_id' => $organizer->id]);

        $response = $this->actingAs($organizer)->post(
            route('organizer.events.ticket-types.store', $event),
            [
                'name' => 'Vé Quá Dài',
                'zone' => str_repeat('A', 101),
                'description' => str_repeat('B', 1001),
                'price' => 200000,
                'quantity' => 20,
                'sale_start' => now()->subDay()->format('Y-m-d H:i:s'),
                'sale_end' => now()->addDays(5)->format('Y-m-d H:i:s'),
                'max_per_order' => 2,
            ],
        );

        $response->assertSessionHasErrors(['zone', 'description']);
        $this->assertDatabaseMissing('ticket_types', [
            'event_id' => $event->id,
            'name' => 'Vé Quá Dài',
        ]);
    }

    public function test_another_organizer_cannot_update_ticket_type_zone_or_description(): void
    {
        $owner = User::factory()->organizer()->create();
        $intruder = User::factory()->organizer()->create();
        $event = Event::factory()->draft()->create(['organizer_id' => $owner->id]);
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'zone' => 'Khu Gốc',
            'description' => 'Quyền lợi gốc.',
        ]);

        $this->actingAs($intruder)
            ->put(route('organizer.ticket-types.update', $ticketType), [
                'name' => 'Vé Bị Sửa',
                'zone' => 'Khu Hacked',
                'description' => 'Bị thay đổi trái phép.',
                'price' => 100000,
                'quantity' => 10,
                'sale_start' => now()->subDay()->format('Y-m-d H:i:s'),
                'sale_end' => now()->addDays(5)->format('Y-m-d H:i:s'),
                'max_per_order' => 2,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('ticket_types', [
            'id' => $ticketType->id,
            'zone' => 'Khu Gốc',
            'description' => 'Quyền lợi gốc.',
        ]);
    }
}
