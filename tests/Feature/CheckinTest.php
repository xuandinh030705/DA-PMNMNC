<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckinTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_can_checkin_valid_paid_ticket_successfully(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);
        $ticketType = TicketType::factory()->create(['event_id' => $event->id]);
        $order = Order::factory()->paid()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
        ]);
        $ticket = Ticket::factory()->valid()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => 'TKT-VALID1234',
        ]);

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => 'TKT-VALID1234',
        ]);

        $response->assertSessionHas('success');
        $freshTicket = $ticket->fresh();
        $this->assertEquals(Ticket::STATUS_USED, $freshTicket->status);
        $this->assertNotNull($freshTicket->checked_in_at);
        $this->assertSame($organizer->id, $freshTicket->checked_in_by);
        $this->assertSame($organizer->id, $freshTicket->checkedInBy?->id);
    }

    public function test_checkin_fails_when_ticket_code_does_not_exist(): void
    {
        $organizer = User::factory()->organizer()->create();

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => 'TKT-NOTFOUND99',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_checkin_fails_when_order_is_not_paid(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);
        $ticketType = TicketType::factory()->create(['event_id' => $event->id]);
        $pendingOrder = Order::factory()->pending()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $pendingOrder->id,
            'ticket_type_id' => $ticketType->id,
        ]);
        $ticket = Ticket::factory()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => 'TKT-PENDING12',
        ]);

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => 'TKT-PENDING12',
        ]);

        $response->assertSessionHas('error');
        $this->assertNotEquals(Ticket::STATUS_USED, $ticket->fresh()->status);
    }

    public function test_checkin_fails_when_ticket_is_already_used(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);
        $ticketType = TicketType::factory()->create(['event_id' => $event->id]);
        $order = Order::factory()->paid()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
        ]);
        $ticket = Ticket::factory()->used()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => 'TKT-USED12345',
        ]);

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => 'TKT-USED12345',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_checkin_fails_when_ticket_belongs_to_another_organizer(): void
    {
        $organizer1 = User::factory()->organizer()->create();
        $organizer2 = User::factory()->organizer()->create();

        // Event belongs to organizer2
        $eventOfOrg2 = Event::factory()->create(['organizer_id' => $organizer2->id]);
        $ticketType = TicketType::factory()->create(['event_id' => $eventOfOrg2->id]);
        $order = Order::factory()->paid()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
        ]);
        $ticket = Ticket::factory()->valid()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => 'TKT-ORG2-TICKET',
        ]);

        // Organizer1 attempts to check-in
        $response = $this->actingAs($organizer1)->post(route('organizer.checkin.store'), [
            'ticket_code' => 'TKT-ORG2-TICKET',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(Ticket::STATUS_VALID, $ticket->fresh()->status);
    }

    public function test_checkin_accepts_a_signed_qr_payload(): void
    {
        $organizer = User::factory()->organizer()->create();
        $ticket = $this->createTicketFor($organizer, 'TKT-QRVALID123');

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => $ticket->qr_payload,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(Ticket::STATUS_USED, $ticket->fresh()->status);
    }

    public function test_checkin_rejects_a_qr_payload_with_tampered_signature(): void
    {
        $organizer = User::factory()->organizer()->create();
        $ticket = $this->createTicketFor($organizer, 'TKT-QRTAMPER1');

        // Same ticket code but re-serialized payload => HMAC no longer matches.
        $tamperedPayload = json_encode(['v' => 1, 'ticket_code' => 'TKT-QRTAMPER1', 'extra' => 'forged']);

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => $tamperedPayload,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(Ticket::STATUS_VALID, $ticket->fresh()->status);
    }

    public function test_checkin_rejects_malformed_qr_json(): void
    {
        $organizer = User::factory()->organizer()->create();

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => '{"v":1,',
        ]);

        $response->assertSessionHas('error');
        $response->assertSessionMissing('success_ticket');
    }

    public function test_checkin_accepts_lowercase_typed_ticket_code(): void
    {
        $organizer = User::factory()->organizer()->create();
        $ticket = $this->createTicketFor($organizer, 'TKT-LOWERCASE1');

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => 'tkt-lowercase1',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(Ticket::STATUS_USED, $ticket->fresh()->status);
    }

    public function test_checkin_form_provides_camera_scanner_and_manual_input(): void
    {
        $organizer = User::factory()->organizer()->create();

        $response = $this->actingAs($organizer)->get(route('organizer.checkin.form'));

        $response->assertOk();
        $response->assertSee('name="ticket_code"', false);
        $response->assertSee('id="qr-reader"', false);
        $response->assertSee('id="btn-start-camera"', false);
        $response->assertSee('js/html5-qrcode.min.js', false);
    }

    private function createTicketFor(User $organizer, string $ticketCode): Ticket
    {
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);
        $ticketType = TicketType::factory()->create(['event_id' => $event->id]);
        $order = Order::factory()->paid()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        return Ticket::factory()->valid()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => $ticketCode,
        ]);
    }
}
