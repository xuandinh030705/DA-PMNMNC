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

    public function test_ticket_pdf_renders_in_memory_without_persisting_files_on_disk(): void
    {
        $organizer = User::factory()->organizer()->create(['name' => 'Tống Xuân Định']);
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'zone' => 'Khu A',
            'description' => 'Ưu tiên check-in nhanh.',
        ]);
        $order = Order::factory()->paid()->create(['user_id' => $customer->id]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
        ]);
        $usedTicket = Ticket::factory()->used()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => 'TKT-MEMONLY123',
            'checked_in_by' => $organizer->id,
        ]);

        $pdfService = $this->app->make(TicketPdfService::class);
        $pdfBytes = $pdfService->output($usedTicket);

        $this->assertStringStartsWith('%PDF-', $pdfBytes);
        $this->assertFileDoesNotExist(storage_path('app/'.$pdfService->filenameFor($usedTicket)));
        $this->assertFileDoesNotExist(storage_path('app/public/'.$pdfService->filenameFor($usedTicket)));
    }

    public function test_pdf_view_renders_vietnamese_unicode_zone_description_qr_and_checkin_metadata(): void
    {
        $organizer = User::factory()->organizer()->create(['name' => 'Nhân viên Định']);
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'title' => 'Hội nghị Công nghệ Mở Việt Nam',
        ]);
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'name' => 'Vé Hạng Nhất',
            'zone' => 'Khán đài A1',
            'description' => 'Tặng bộ tài liệu kỹ thuật và tiệc trưa.',
        ]);
        $order = Order::factory()->paid()->create([
            'customer_name' => 'Nguyễn Văn An',
        ]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
        ]);
        $ticket = Ticket::factory()->used()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => 'TKT-UNICODE123',
            'checked_in_by' => $organizer->id,
        ]);
        $ticket->load(['orderItem.order', 'orderItem.ticketType.event.venue', 'checkedInBy']);

        $html = view('pdf.ticket', [
            'ticket' => $ticket,
            'order' => $order,
            'ticketType' => $ticketType,
            'event' => $event,
            'qrCodeDataUri' => 'data:image/png;base64,FAKEQRDATA',
        ])->render();

        $this->assertStringContainsString('DejaVu Sans', $html);
        $this->assertStringContainsString('Hội nghị Công nghệ Mở Việt Nam', $html);
        $this->assertStringContainsString('Khán đài A1', $html);
        $this->assertStringContainsString('Tặng bộ tài liệu kỹ thuật và tiệc trưa.', $html);
        $this->assertStringContainsString('TKT-UNICODE123', $html);
        $this->assertStringContainsString('data:image/png;base64,FAKEQRDATA', $html);
        $this->assertStringContainsString('Nhân viên Định', $html);
    }

    public function test_event_organizer_and_admin_can_download_pdf_while_other_organizer_is_forbidden(): void
    {
        $eventOrganizer = User::factory()->organizer()->create();
        $otherOrganizer = User::factory()->organizer()->create();
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $event = Event::factory()->create(['organizer_id' => $eventOrganizer->id]);
        $ticketType = TicketType::factory()->create(['event_id' => $event->id]);
        $order = Order::factory()->paid()->create(['user_id' => $customer->id]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
        ]);
        $ticket = Ticket::factory()->valid()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => 'TKT-POLICY1234',
        ]);

        $this->actingAs($eventOrganizer)
            ->get(route('tickets.pdf', $ticket))
            ->assertOk()
            ->assertDownload('ve-TKT-POLICY1234.pdf');

        $this->actingAs($admin)
            ->get(route('tickets.pdf', $ticket))
            ->assertOk()
            ->assertDownload('ve-TKT-POLICY1234.pdf');

        $this->actingAs($otherOrganizer)
            ->get(route('tickets.pdf', $ticket))
            ->assertForbidden();
    }

    public function test_ticket_owner_can_download_pdf_when_legacy_qr_payload_is_null(): void
    {
        $customer = User::factory()->customer()->create();
        $ticket = $this->createTicketFor($customer, 'TKT-LEGACYQR01');

        Ticket::whereKey($ticket->id)->update([
            'qr_payload' => null,
            'qr_signature' => null,
        ]);

        $response = $this->actingAs($customer)->get(route('tickets.pdf', $ticket->fresh()));

        $response->assertOk();
        $response->assertDownload('ve-TKT-LEGACYQR01.pdf');
        $this->assertStringStartsWith('%PDF-', (string) $response->getContent());
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
