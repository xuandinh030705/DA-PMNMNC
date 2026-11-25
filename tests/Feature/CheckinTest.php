<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
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
        $this->assertSame(TicketStatus::USED, $freshTicket->status);
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
        $this->assertNotSame(TicketStatus::USED, $ticket->fresh()->status);
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
        $this->assertSame(TicketStatus::VALID, $ticket->fresh()->status);
    }

    public function test_checkin_accepts_a_signed_qr_payload(): void
    {
        $organizer = User::factory()->organizer()->create(['name' => 'Tống Xuân Định']);
        $ticket = $this->createTicketFor($organizer, 'TKT-QRVALID123', 'Khu VIP A');

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => $ticket->qr_payload,
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('success_ticket', function ($summary) use ($organizer): bool {
            return is_array($summary)
                && $summary['ticket_code'] === 'TKT-QRVALID123'
                && $summary['zone'] === 'Khu VIP A'
                && $summary['checked_in_by'] === $organizer->name
                && $summary['qr_signature_valid'] === true;
        });

        $freshTicket = $ticket->fresh();
        $this->assertSame(TicketStatus::USED, $freshTicket->status);
        $this->assertNotNull($freshTicket->checked_in_at);
        $this->assertSame($organizer->id, $freshTicket->checked_in_by);
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
        $response->assertSessionMissing('_old_input');
        $this->assertSame(TicketStatus::VALID, $ticket->fresh()->status);
        $this->assertNull($ticket->fresh()->checked_in_by);
    }

    public function test_checkin_rejects_malformed_qr_json(): void
    {
        $organizer = User::factory()->organizer()->create();

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => '{"v":1,',
        ]);

        $response->assertSessionHas('error');
        $response->assertSessionMissing('success_ticket');
        $response->assertSessionMissing('_old_input');
    }

    public function test_checkin_fails_when_ticket_is_cancelled_via_manual_code_and_qr_payload(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);
        $ticketType = TicketType::factory()->create(['event_id' => $event->id]);
        $order = Order::factory()->paid()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
        ]);
        $ticket = Ticket::factory()->cancelled()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => 'TKT-CANCEL0001',
        ]);

        $this->actingAs($organizer)
            ->post(route('organizer.checkin.store'), ['ticket_code' => 'TKT-CANCEL0001'])
            ->assertSessionHas('error');

        $this->actingAs($organizer)
            ->post(route('organizer.checkin.store'), ['ticket_code' => $ticket->qr_payload])
            ->assertSessionHas('error');

        $freshTicket = $ticket->fresh();
        $this->assertSame(TicketStatus::CANCELLED, $freshTicket->status);
        $this->assertNull($freshTicket->checked_in_at);
        $this->assertNull($freshTicket->checked_in_by);
    }

    public function test_checkin_with_qr_payload_fails_when_order_is_not_paid(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);
        $ticketType = TicketType::factory()->create(['event_id' => $event->id]);
        $pendingOrder = Order::factory()->pending()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $pendingOrder->id,
            'ticket_type_id' => $ticketType->id,
        ]);
        $ticket = Ticket::factory()->valid()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => 'TKT-QRPEND0001',
        ]);

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => $ticket->qr_payload,
        ]);

        $response->assertSessionHas('error');
        $response->assertSessionMissing('_old_input');
        $this->assertSame(TicketStatus::VALID, $ticket->fresh()->status);
        $this->assertNull($ticket->fresh()->checked_in_by);
    }

    public function test_checkin_with_qr_payload_fails_when_ticket_is_already_used(): void
    {
        $organizer = User::factory()->organizer()->create();
        $ticket = $this->createTicketFor($organizer, 'TKT-QRUSED0001');

        $this->actingAs($organizer)
            ->post(route('organizer.checkin.store'), ['ticket_code' => $ticket->qr_payload])
            ->assertSessionHas('success');

        $firstCheckinAt = $ticket->fresh()->checked_in_at;

        $secondResponse = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => $ticket->qr_payload,
        ]);

        $secondResponse->assertSessionHas('error');
        $this->assertSame($organizer->id, $ticket->fresh()->checked_in_by);
        $this->assertTrue($firstCheckinAt->equalTo($ticket->fresh()->checked_in_at));
    }

    public function test_checkin_with_qr_payload_fails_when_ticket_belongs_to_another_organizer(): void
    {
        $organizer1 = User::factory()->organizer()->create();
        $organizer2 = User::factory()->organizer()->create();
        $ticket = $this->createTicketFor($organizer2, 'TKT-QRORG20001');

        $response = $this->actingAs($organizer1)->post(route('organizer.checkin.store'), [
            'ticket_code' => $ticket->qr_payload,
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(TicketStatus::VALID, $ticket->fresh()->status);
        $this->assertNull($ticket->fresh()->checked_in_by);
    }

    public function test_checkin_with_qr_payload_fails_when_stored_signature_is_corrupted(): void
    {
        $organizer = User::factory()->organizer()->create();
        $ticket = $this->createTicketFor($organizer, 'TKT-QRCORRUPT1');

        Ticket::whereKey($ticket->id)->update([
            'qr_signature' => str_repeat('f', 64),
        ]);

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => $ticket->qr_payload,
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(TicketStatus::VALID, $ticket->fresh()->status);
        $this->assertNull($ticket->fresh()->checked_in_by);
    }

    public function test_checkin_with_qr_payload_fails_when_ticket_does_not_exist(): void
    {
        $organizer = User::factory()->organizer()->create();
        $ghostPayload = Ticket::buildQrPayload('TKT-GHOST99999');

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => $ghostPayload,
        ]);

        $response->assertSessionHas('error');
        $response->assertSessionMissing('_old_input');
    }

    public function test_checkin_rejects_empty_and_whitespace_only_input(): void
    {
        $organizer = User::factory()->organizer()->create();

        $this->actingAs($organizer)
            ->post(route('organizer.checkin.store'), ['ticket_code' => ''])
            ->assertSessionHasErrors('ticket_code');

        $this->actingAs($organizer)
            ->post(route('organizer.checkin.store'), ['ticket_code' => '   '])
            ->assertSessionHasErrors('ticket_code');
    }

    public function test_customer_cannot_access_organizer_checkin_routes(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('organizer.checkin.form'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->post(route('organizer.checkin.store'), ['ticket_code' => 'TKT-ANY1234567'])
            ->assertForbidden();
    }

    public function test_checkin_accepts_lowercase_typed_ticket_code(): void
    {
        $organizer = User::factory()->organizer()->create();
        $ticket = $this->createTicketFor($organizer, 'TKT-LOWERCASE1');

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => 'tkt-lowercase1',
        ]);

        $response->assertSessionHas('success');
        $this->assertSame(TicketStatus::USED, $ticket->fresh()->status);
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

    public function test_checkin_success_card_renders_the_flashed_summary(): void
    {
        $organizer = User::factory()->organizer()->create();
        $this->createTicketFor($organizer, 'TKT-SUMMARY01');

        $response = $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => 'TKT-SUMMARY01',
        ]);

        $response->assertSessionHas('success_ticket', fn ($value) => is_array($value));

        $this->actingAs($organizer)
            ->get(route('organizer.checkin.form'))
            ->assertOk()
            ->assertSee('TKT-SUMMARY01')
            ->assertSee('Nhân viên soát vé');
    }

    private function createTicketFor(User $organizer, string $ticketCode, ?string $zone = null): Ticket
    {
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'zone' => $zone,
        ]);
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
