<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Services\TicketPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_pdf_is_rendered_in_memory(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'title' => 'Đêm nhạc Acoustic mùa thu',
        ]);
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'name' => 'Vé VIP',
            'zone' => 'Khu A',
            'description' => 'Bao gồm nước uống và gặp gỡ nghệ sĩ.',
        ]);
        $order = Order::factory()->paid()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
        ]);
        $ticket = Ticket::factory()->valid()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => 'TKT-PDF1234567',
        ]);

        $pdf = $this->app->make(TicketPdfService::class)->output($ticket);

        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(1000, strlen($pdf));
    }

    public function test_pdf_filename_uses_ticket_code(): void
    {
        $ticket = Ticket::factory()->valid()->create(['ticket_code' => 'TKT-FILENAME99']);

        $this->assertSame(
            've-TKT-FILENAME99.pdf',
            $this->app->make(TicketPdfService::class)->filenameFor($ticket),
        );
    }

    public function test_ticket_owner_can_download_the_ticket_pdf(): void
    {
        $customer = User::factory()->customer()->create();
        $ticket = $this->createTicketFor($customer, 'TKT-PDFOWNER1');

        $response = $this->actingAs($customer)->get(route('tickets.pdf', $ticket));

        $response->assertOk();
        $response->assertDownload('ve-TKT-PDFOWNER1.pdf');
        $this->assertStringStartsWith('%PDF-', (string) $response->getContent());
    }

    public function test_customer_cannot_download_someone_elses_ticket_pdf(): void
    {
        $owner = User::factory()->customer()->create();
        $stranger = User::factory()->customer()->create();
        $ticket = $this->createTicketFor($owner, 'TKT-PDFOWNER2');

        $this->actingAs($stranger)
            ->get(route('tickets.pdf', $ticket))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_when_downloading_ticket_pdf(): void
    {
        $owner = User::factory()->customer()->create();
        $ticket = $this->createTicketFor($owner, 'TKT-PDFOWNER3');

        $this->get(route('tickets.pdf', $ticket))
            ->assertRedirect(route('login'));
    }

    private function createTicketFor(User $customer, string $ticketCode): Ticket
    {
        $order = Order::factory()->paid()->create(['user_id' => $customer->id]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => TicketType::factory(),
        ]);

        return Ticket::factory()->valid()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => $ticketCode,
        ]);
    }
}
