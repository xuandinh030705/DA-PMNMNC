<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Mail\OrderPaidMail;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_checkout_tickets_and_create_pending_order(): void
    {
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->published()->create([
            'start_time' => now()->addDays(10),
            'end_time' => now()->addDays(10)->addHours(4),
        ]);

        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'price' => 200000,
            'quantity' => 100,
            'sold' => 0,
            'max_per_order' => 5,
            'sale_start' => now()->subDay(),
            'sale_end' => now()->addDays(5),
        ]);

        $response = $this->actingAs($customer)->post(route('checkout.store', $event->slug), [
            'tickets' => [
                $ticketType->id => 2,
            ],
            'customer_name' => 'Nguyễn Khách Hàng',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '0912345678',
        ]);

        $response->assertSessionHas('success');

        // Check sold count incremented (reserved)
        $this->assertEquals(2, $ticketType->fresh()->sold);

        // Check order created with pending status and 15 min expiration
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'status' => OrderStatus::PENDING->value,
            'total_amount' => 400000,
        ]);
    }

    public function test_checkout_fails_when_no_tickets_selected(): void
    {
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->published()->create();
        $ticketType = TicketType::factory()->create(['event_id' => $event->id]);

        $response = $this->actingAs($customer)->post(route('checkout.store', $event->slug), [
            'tickets' => [
                $ticketType->id => 0,
            ],
            'customer_name' => 'Nguyễn Khách Hàng',
            'customer_email' => 'customer@example.com',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, Order::count());
    }

    public function test_checkout_fails_when_quantity_exceeds_max_per_order(): void
    {
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->published()->create();
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'quantity' => 100,
            'sold' => 0,
            'max_per_order' => 3,
            'sale_start' => now()->subDay(),
            'sale_end' => now()->addDays(5),
        ]);

        $response = $this->actingAs($customer)->post(route('checkout.store', $event->slug), [
            'tickets' => [
                $ticketType->id => 5, // Exceeds max_per_order of 3
            ],
            'customer_name' => 'Nguyễn Khách Hàng',
            'customer_email' => 'customer@example.com',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, $ticketType->fresh()->sold);
    }

    public function test_checkout_fails_when_quantity_exceeds_available_remaining(): void
    {
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->published()->create();
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'quantity' => 10,
            'sold' => 9, // Only 1 ticket left
            'max_per_order' => 10,
            'sale_start' => now()->subDay(),
            'sale_end' => now()->addDays(5),
        ]);

        $response = $this->actingAs($customer)->post(route('checkout.store', $event->slug), [
            'tickets' => [
                $ticketType->id => 2, // Requesting 2 when only 1 remaining
            ],
            'customer_name' => 'Nguyễn Khách Hàng',
            'customer_email' => 'customer@example.com',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(9, $ticketType->fresh()->sold);
    }

    public function test_customer_can_pay_order_and_tickets_are_generated(): void
    {
        Mail::fake();

        $customer = User::factory()->customer()->create();
        $ticketType = TicketType::factory()->create(['price' => 150000]);

        $order = Order::factory()->pending()->create([
            'user_id' => $customer->id,
            'customer_email' => $customer->email,
            'total_amount' => 300000,
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 2,
            'unit_price' => 150000,
            'subtotal' => 300000,
        ]);

        $response = $this->actingAs($customer)->post(route('orders.pay', $order->order_code), [
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertSessionHas('success');

        $this->assertSame(OrderStatus::PAID, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);

        $tickets = Ticket::where('order_item_id', $orderItem->id)->get();
        $this->assertCount(2, $tickets);

        foreach ($tickets as $ticket) {
            $this->assertNotEmpty($ticket->qr_payload);
            $this->assertNotEmpty($ticket->qr_signature);
            $this->assertTrue($ticket->hasValidQrSignature());
            $this->assertNull($ticket->checked_in_by);
        }

        Mail::assertSent(OrderPaidMail::class, function (OrderPaidMail $mail) use ($order, $customer) {
            return $mail->order->id === $order->id
                && $mail->order->isPaid()
                && $mail->order->payment_method === 'bank_transfer'
                && $mail->order->paid_at !== null
                && $mail->hasTo($customer->email);
        });
    }

    public function test_order_paid_mail_builds_attachments_and_content(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->paid()->create([
            'user_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_name' => $customer->name,
        ]);

        $ticketType = TicketType::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 1,
        ]);

        Ticket::factory()->valid()->create([
            'order_item_id' => $orderItem->id,
        ]);

        $mail = new OrderPaidMail($order);

        $mail->assertHasSubject('Xác nhận đơn hàng #'.$order->order_code.' - Vé điện tử của bạn');
        $this->assertCount(1, $mail->attachments());
    }

    public function test_storefront_tickets_and_order_show_render_qr_code_and_pdf_download(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->paid()->create([
            'user_id' => $customer->id,
            'customer_email' => $customer->email,
        ]);

        $ticketType = TicketType::factory()->create(['zone' => 'Khu A VIP']);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 1,
        ]);

        $ticket = Ticket::factory()->valid()->create([
            'order_item_id' => $orderItem->id,
        ]);

        $ticketsResponse = $this->actingAs($customer)->get(route('tickets.index'));
        $ticketsResponse->assertOk();
        $ticketsResponse->assertSee($ticket->ticket_code);
        $ticketsResponse->assertSee('Tải vé PDF');
        $ticketsResponse->assertSee(route('tickets.pdf', $ticket));

        $orderResponse = $this->actingAs($customer)->get(route('orders.show', $order->order_code));
        $orderResponse->assertOk();
        $orderResponse->assertSee($ticket->ticket_code);
        $orderResponse->assertSee(route('tickets.pdf', $ticket));
    }

    public function test_storefront_order_show_avoids_n_plus_one_queries_on_multiple_tickets(): void
    {
        $customer = User::factory()->customer()->create();
        $ticketType = TicketType::factory()->create(['zone' => 'Khu A']);

        $singleOrder = Order::factory()->paid()->create(['user_id' => $customer->id]);
        $singleItem = OrderItem::factory()->create([
            'order_id' => $singleOrder->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 1,
        ]);
        Ticket::factory()->valid()->create(['order_item_id' => $singleItem->id]);

        $multiOrder = Order::factory()->paid()->create(['user_id' => $customer->id]);
        $multiItem = OrderItem::factory()->create([
            'order_id' => $multiOrder->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 6,
        ]);
        Ticket::factory()->count(6)->valid()->create(['order_item_id' => $multiItem->id]);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($customer)->get(route('orders.show', $singleOrder->order_code))->assertOk();
        $singleOrderQueryCount = count(DB::getQueryLog());

        DB::flushQueryLog();
        $this->actingAs($customer)->get(route('orders.show', $multiOrder->order_code))->assertOk();
        $multiOrderQueryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($singleOrderQueryCount, $multiOrderQueryCount);
    }

    public function test_customer_can_cancel_pending_order_and_tickets_are_returned(): void
    {
        $customer = User::factory()->customer()->create();
        $ticketType = TicketType::factory()->create([
            'quantity' => 100,
            'sold' => 5,
        ]);

        $order = Order::factory()->pending()->create(['user_id' => $customer->id]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 3,
        ]);

        $response = $this->actingAs($customer)->post(route('orders.cancel', $order->order_code));

        $response->assertSessionHas('success');
        $this->assertSame(OrderStatus::CANCELLED, $order->fresh()->status);
        // 5 - 3 = 2 sold remaining
        $this->assertEquals(2, $ticketType->fresh()->sold);
    }

    public function test_customer_cannot_cancel_paid_order(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->paid()->create(['user_id' => $customer->id]);

        $response = $this->actingAs($customer)->post(route('orders.cancel', $order->order_code));

        $response->assertStatus(403);
        $this->assertSame(OrderStatus::PAID, $order->fresh()->status);
    }

    public function test_artisan_command_expires_pending_orders_and_returns_sold_tickets(): void
    {
        $ticketType = TicketType::factory()->create([
            'quantity' => 50,
            'sold' => 10,
        ]);

        // Expired order created with expires_at in the past
        $expiredOrder = Order::factory()->pending()->create([
            'expires_at' => now()->subMinute(),
        ]);

        OrderItem::factory()->create([
            'order_id' => $expiredOrder->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 4,
        ]);

        $this->artisan('orders:expire-pending')->assertSuccessful();

        $this->assertSame(OrderStatus::CANCELLED, $expiredOrder->fresh()->status);
        $this->assertEquals(6, $ticketType->fresh()->sold); // 10 - 4 = 6
    }

    public function test_paying_expired_order_cancels_order_and_restores_tickets(): void
    {
        $customer = User::factory()->customer()->create();
        $ticketType = TicketType::factory()->create([
            'quantity' => 50,
            'sold' => 5,
        ]);

        $expiredOrder = Order::factory()->pending()->create([
            'user_id' => $customer->id,
            'expires_at' => now()->subMinute(),
        ]);

        OrderItem::factory()->create([
            'order_id' => $expiredOrder->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 3,
        ]);

        $response = $this->actingAs($customer)
            ->from(route('orders.show', $expiredOrder->order_code))
            ->post(route('orders.pay', $expiredOrder->order_code), [
                'payment_method' => 'bank_transfer',
            ]);

        $response->assertRedirect(route('orders.show', $expiredOrder->order_code));
        $response->assertSessionHas('error');
        $this->assertSame(OrderStatus::CANCELLED, $expiredOrder->fresh()->status);
        $this->assertEquals(2, $ticketType->fresh()->sold);
    }
}
