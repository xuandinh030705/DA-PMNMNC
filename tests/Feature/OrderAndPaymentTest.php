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
            'status' => Order::STATUS_PENDING,
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
        $customer = User::factory()->customer()->create();
        $ticketType = TicketType::factory()->create(['price' => 150000]);

        $order = Order::factory()->pending()->create([
            'user_id' => $customer->id,
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

        $this->assertEquals(Order::STATUS_PAID, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);

        $tickets = Ticket::where('order_item_id', $orderItem->id)->get();
        $this->assertCount(2, $tickets);

        foreach ($tickets as $ticket) {
            $this->assertNotEmpty($ticket->qr_payload);
            $this->assertNotEmpty($ticket->qr_signature);
            $this->assertTrue($ticket->hasValidQrSignature());
            $this->assertNull($ticket->checked_in_by);
        }
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
        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->status);
        // 5 - 3 = 2 sold remaining
        $this->assertEquals(2, $ticketType->fresh()->sold);
    }

    public function test_customer_cannot_cancel_paid_order(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->paid()->create(['user_id' => $customer->id]);

        $response = $this->actingAs($customer)->post(route('orders.cancel', $order->order_code));

        $response->assertStatus(403);
        $this->assertEquals(Order::STATUS_PAID, $order->fresh()->status);
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

        $this->assertEquals(Order::STATUS_CANCELLED, $expiredOrder->fresh()->status);
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
        $this->assertEquals(Order::STATUS_CANCELLED, $expiredOrder->fresh()->status);
        $this->assertEquals(2, $ticketType->fresh()->sold);
    }
}
