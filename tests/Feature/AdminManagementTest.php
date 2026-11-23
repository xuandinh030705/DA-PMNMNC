<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_pending_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->pending()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.approve', $event));

        $response->assertSessionHas('success');
        $this->assertSame(EventStatus::PUBLISHED, $event->fresh()->status);
    }

    public function test_admin_can_reject_pending_event_back_to_draft(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->pending()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.reject', $event));

        $response->assertSessionHas('warning');
        $this->assertSame(EventStatus::DRAFT, $event->fresh()->status);
    }

    public function test_admin_can_cancel_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->published()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.cancel', $event));

        $response->assertSessionHas('success');
        $this->assertSame(EventStatus::CANCELLED, $event->fresh()->status);
    }

    public function test_admin_cancelling_event_cascades_to_pending_orders_restores_sold_and_cancels_valid_tickets(): void
    {
        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->published()->create(['organizer_id' => $organizer->id]);

        $vipType = TicketType::factory()->create([
            'event_id' => $event->id,
            'name' => 'Vé VIP',
            'quantity' => 50,
            'sold' => 7,
        ]);
        $standardType = TicketType::factory()->create([
            'event_id' => $event->id,
            'name' => 'Vé Thường',
            'quantity' => 100,
            'sold' => 4,
        ]);

        // Pending order 1: 2 VIP + 3 Standard (reserved)
        $pendingOrder1 = Order::factory()->pending()->create();
        OrderItem::factory()->create([
            'order_id' => $pendingOrder1->id,
            'ticket_type_id' => $vipType->id,
            'quantity' => 2,
        ]);
        OrderItem::factory()->create([
            'order_id' => $pendingOrder1->id,
            'ticket_type_id' => $standardType->id,
            'quantity' => 3,
        ]);

        // Pending order 2: 1 VIP (reserved)
        $pendingOrder2 = Order::factory()->pending()->create();
        OrderItem::factory()->create([
            'order_id' => $pendingOrder2->id,
            'ticket_type_id' => $vipType->id,
            'quantity' => 1,
        ]);

        // Already cancelled order: 2 VIP (must NOT decrement sold again)
        $alreadyCancelledOrder = Order::factory()->cancelled()->create();
        OrderItem::factory()->create([
            'order_id' => $alreadyCancelledOrder->id,
            'ticket_type_id' => $vipType->id,
            'quantity' => 2,
        ]);

        // Paid order: 4 VIP (3 valid + 1 used) and 1 Standard (1 valid)
        $paidOrder = Order::factory()->paid()->create();
        $paidVipItem = OrderItem::factory()->create([
            'order_id' => $paidOrder->id,
            'ticket_type_id' => $vipType->id,
            'quantity' => 4,
        ]);
        $paidStandardItem = OrderItem::factory()->create([
            'order_id' => $paidOrder->id,
            'ticket_type_id' => $standardType->id,
            'quantity' => 1,
        ]);

        $validTickets = Ticket::factory()->count(3)->valid()->create([
            'order_item_id' => $paidVipItem->id,
        ]);
        $validStandardTicket = Ticket::factory()->valid()->create([
            'order_item_id' => $paidStandardItem->id,
        ]);
        $checkedInAt = now()->subHour()->startOfSecond();
        $usedTicket = Ticket::factory()->used()->create([
            'order_item_id' => $paidVipItem->id,
            'checked_in_at' => $checkedInAt,
            'checked_in_by' => $organizer->id,
        ]);

        // Unrelated event: must NOT be affected
        $otherEvent = Event::factory()->published()->create();
        $otherType = TicketType::factory()->create([
            'event_id' => $otherEvent->id,
            'quantity' => 30,
            'sold' => 5,
        ]);
        $otherPendingOrder = Order::factory()->pending()->create();
        OrderItem::factory()->create([
            'order_id' => $otherPendingOrder->id,
            'ticket_type_id' => $otherType->id,
            'quantity' => 2,
        ]);
        $otherPaidOrder = Order::factory()->paid()->create();
        $otherPaidItem = OrderItem::factory()->create([
            'order_id' => $otherPaidOrder->id,
            'ticket_type_id' => $otherType->id,
            'quantity' => 3,
        ]);
        $otherValidTicket = Ticket::factory()->valid()->create([
            'order_item_id' => $otherPaidItem->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.events.cancel', $event));

        $response->assertSessionHas('success');
        $this->assertSame(EventStatus::CANCELLED, $event->fresh()->status);

        // Pending orders cancelled & reserved sold restored (7 - 2 - 1 = 4; 4 - 3 = 1)
        $this->assertSame(OrderStatus::CANCELLED, $pendingOrder1->fresh()->status);
        $this->assertSame(OrderStatus::CANCELLED, $pendingOrder2->fresh()->status);
        $this->assertSame(4, $vipType->fresh()->sold);
        $this->assertSame(1, $standardType->fresh()->sold);

        // Paid & already-cancelled orders preserve status
        $this->assertSame(OrderStatus::PAID, $paidOrder->fresh()->status);
        $this->assertSame(OrderStatus::CANCELLED, $alreadyCancelledOrder->fresh()->status);

        // Valid tickets cancelled; used ticket preserved intact
        foreach ($validTickets as $ticket) {
            $this->assertSame(TicketStatus::CANCELLED, $ticket->fresh()->status);
        }
        $this->assertSame(TicketStatus::CANCELLED, $validStandardTicket->fresh()->status);

        $freshUsedTicket = $usedTicket->fresh();
        $this->assertSame(TicketStatus::USED, $freshUsedTicket->status);
        $this->assertSame($organizer->id, $freshUsedTicket->checked_in_by);
        $this->assertTrue($checkedInAt->equalTo($freshUsedTicket->checked_in_at));

        // Unrelated event, orders, stock, and tickets untouched
        $this->assertSame(EventStatus::PUBLISHED, $otherEvent->fresh()->status);
        $this->assertSame(OrderStatus::PENDING, $otherPendingOrder->fresh()->status);
        $this->assertSame(5, $otherType->fresh()->sold);
        $this->assertSame(TicketStatus::VALID, $otherValidTicket->fresh()->status);
    }

    public function test_admin_cancelling_event_blocks_checkin_and_payment_and_guards_sold_underflow(): void
    {
        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->published()->create(['organizer_id' => $organizer->id]);

        // Intentionally set sold lower than pending quantity to ensure unsigned int never underflows
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'quantity' => 50,
            'sold' => 1,
        ]);

        $pendingOrder = Order::factory()->pending()->create(['user_id' => $customer->id]);
        OrderItem::factory()->create([
            'order_id' => $pendingOrder->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 3,
        ]);

        $paidOrder = Order::factory()->paid()->create(['user_id' => $customer->id]);
        $paidItem = OrderItem::factory()->create([
            'order_id' => $paidOrder->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 1,
        ]);
        $ticket = Ticket::factory()->valid()->create([
            'order_item_id' => $paidItem->id,
            'ticket_code' => 'TKT-CANCELLED9',
        ]);

        $this->actingAs($admin)->post(route('admin.events.cancel', $event))
            ->assertSessionHas('success');

        $this->assertSame(0, $ticketType->fresh()->sold);
        $this->assertSame(OrderStatus::CANCELLED, $pendingOrder->fresh()->status);
        $this->assertSame(TicketStatus::CANCELLED, $ticket->fresh()->status);

        // Organizer cannot check-in cancelled ticket
        $this->actingAs($organizer)->post(route('organizer.checkin.store'), [
            'ticket_code' => 'TKT-CANCELLED9',
        ])->assertSessionHas('error');
        $this->assertSame(TicketStatus::CANCELLED, $ticket->fresh()->status);

        // Customer cannot pay auto-cancelled pending order
        $this->actingAs($customer)->post(route('orders.pay', $pendingOrder->order_code), [
            'payment_method' => 'bank_transfer',
        ])->assertStatus(403);
        $this->assertSame(OrderStatus::CANCELLED, $pendingOrder->fresh()->status);
    }

    public function test_admin_cannot_cancel_already_cancelled_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->cancelled()->create();

        $this->actingAs($admin)
            ->post(route('admin.events.cancel', $event))
            ->assertStatus(403);
    }

    public function test_admin_event_show_displays_ticket_type_zone_and_description(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->pending()->create();

        TicketType::factory()->create([
            'event_id' => $event->id,
            'name' => 'Vé VVIP Lounge',
            'zone' => 'Khán đài VVIP A',
            'description' => 'Ghế ngồi sát sân khấu, lối check-in ưu tiên và quà lưu niệm.',
        ]);

        TicketType::factory()->create([
            'event_id' => $event->id,
            'name' => 'Vé Phổ Thông',
            'zone' => null,
            'description' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.show', $event));

        $response->assertOk();
        $response->assertSee('Khu vực');
        $response->assertSee('Vé VVIP Lounge');
        $response->assertSee('Khán đài VVIP A');
        $response->assertSee('Ghế ngồi sát sân khấu, lối check-in ưu tiên và quà lưu niệm.');
        $response->assertSee('Vé Phổ Thông');
    }

    public function test_admin_order_show_displays_zone_description_and_checked_in_staff(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->organizer()->create([
            'name' => 'Nguyễn Nhân Viên Soát Vé',
            'email' => 'staff.checkin@example.com',
        ]);
        $event = Event::factory()->published()->create();

        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'name' => 'Vé VIP Sân Khấu',
            'zone' => 'Khu VIP B1',
            'description' => 'Bao gồm đồ uống miễn phí và lối vào riêng.',
        ]);

        $order = Order::factory()->paid()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 3,
        ]);

        $usedTicket = Ticket::factory()->used()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => 'TKT-CHECKED001',
            'checked_in_by' => $staff->id,
        ]);

        $usedTicketWithoutStaff = Ticket::factory()->used()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => 'TKT-CHECKED002',
            'checked_in_by' => null,
        ]);

        $cancelledTicket = Ticket::factory()->cancelled()->create([
            'order_item_id' => $orderItem->id,
            'ticket_code' => 'TKT-VOID000003',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order->order_code));

        $response->assertOk();
        $response->assertSee('Vé VIP Sân Khấu');
        $response->assertSee('Khu VIP B1');
        $response->assertSee('Bao gồm đồ uống miễn phí và lối vào riêng.');
        $response->assertSee($usedTicket->ticket_code);
        $response->assertSee('Nguyễn Nhân Viên Soát Vé');
        $response->assertSee('staff.checkin@example.com');
        $response->assertSee($usedTicketWithoutStaff->ticket_code);
        $response->assertSee('Không xác định');
        $response->assertSee($cancelledTicket->ticket_code);
        $response->assertSee('Vé đã bị vô hiệu hóa');
    }

    public function test_admin_cannot_delete_category_with_existing_events(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Event::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_toggle_user_lock_status(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.users.toggle', $customer));

        $response->assertSessionHas('success');
        $this->assertFalse((bool) $customer->fresh()->is_active);

        // Toggle back to active
        $this->actingAs($admin)->post(route('admin.users.toggle', $customer));
        $this->assertTrue((bool) $customer->fresh()->is_active);
    }

    public function test_admin_can_access_dashboard_and_manage_categories_and_venues(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.users.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.users.edit', $admin))->assertStatus(200);

        $customer = User::factory()->customer()->create();
        $this->actingAs($admin)->put(route('admin.users.update', $customer), [
            'name' => $customer->name,
            'role' => UserRole::ORGANIZER->value,
            'is_active' => 1,
        ])->assertRedirect(route('admin.users.index'));
        $this->assertSame(UserRole::ORGANIZER, $customer->fresh()->role);

        $catResponse = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Hội Nghị Công Nghệ',
            'description' => 'Các sự kiện công nghệ chuyên sâu',
        ]);
        $catResponse->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Hội Nghị Công Nghệ']);

        $venueResponse = $this->actingAs($admin)->post(route('admin.venues.store'), [
            'name' => 'Trung Tâm Triển Lãm Mới',
            'address' => 'Số 10 Đường Láng',
            'city' => 'Hà Nội',
            'capacity' => 2500,
        ]);
        $venueResponse->assertRedirect(route('admin.venues.index'));
        $this->assertDatabaseHas('venues', ['name' => 'Trung Tâm Triển Lãm Mới']);
    }
}
