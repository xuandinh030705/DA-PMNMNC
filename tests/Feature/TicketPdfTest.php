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
}
