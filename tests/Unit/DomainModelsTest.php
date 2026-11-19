<?php

namespace Tests\Unit;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class DomainModelsTest extends TestCase
{
    public function test_ticket_type_remaining_accessor_never_returns_negative(): void
    {
        $ticketType = new TicketType([
            'quantity' => 10,
            'sold' => 4,
        ]);

        $this->assertSame(6, $ticketType->remaining);

        $oversold = new TicketType([
            'quantity' => 5,
            'sold' => 7,
        ]);

        $this->assertSame(0, $oversold->remaining);
    }

    public function test_user_role_helpers_and_enum_alignment(): void
    {
        $admin = new User(['role' => UserRole::ADMIN->value]);
        $organizer = new User(['role' => UserRole::ORGANIZER]);
        $customer = new User(['role' => UserRole::CUSTOMER->value]);

        $this->assertSame(UserRole::ADMIN, $admin->role);
        $this->assertSame(UserRole::ORGANIZER, $organizer->role);
        $this->assertSame(UserRole::CUSTOMER, $customer->role);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isOrganizer());
        $this->assertTrue($organizer->isOrganizer());
        $this->assertTrue($customer->isCustomer());

        $this->assertSame(UserRole::ADMIN->label(), $admin->roleLabel());
        $this->assertSame(UserRole::ADMIN->badgeClass(), $admin->roleBadgeClass());
        $this->assertSame(UserRole::ORGANIZER->label(), $organizer->roleLabel());
        $this->assertSame(UserRole::CUSTOMER->badgeClass(), $customer->roleBadgeClass());

        $event = new Event(['status' => EventStatus::PUBLISHED->value]);
        $order = new Order(['status' => OrderStatus::PAID->value]);
        $ticket = new Ticket(['status' => TicketStatus::VALID->value]);

        $this->assertSame(EventStatus::PUBLISHED, $event->status);
        $this->assertTrue($event->isPublished());
        $this->assertSame(EventStatus::PUBLISHED->label(), $event->statusLabel());
        $this->assertSame(EventStatus::PUBLISHED->badgeClass(), $event->statusBadgeClass());

        $this->assertSame(OrderStatus::PAID, $order->status);
        $this->assertTrue($order->isPaid());
        $this->assertSame(OrderStatus::PAID->label(), $order->statusLabel());
        $this->assertSame(OrderStatus::PAID->badgeClass(), $order->statusBadgeClass());

        $this->assertSame(TicketStatus::VALID, $ticket->status);
        $this->assertTrue($ticket->isValid());
        $this->assertSame(TicketStatus::VALID->label(), $ticket->statusLabel());
        $this->assertSame(TicketStatus::VALID->badgeClass(), $ticket->statusBadgeClass());

        $emptyEvent = new Event;
        $emptyUser = new User;
        $this->assertSame('', $emptyEvent->statusLabel());
        $this->assertSame('bg-secondary', $emptyEvent->statusBadgeClass());
        $this->assertSame('', $emptyUser->roleLabel());
        $this->assertSame('badge-role-customer', $emptyUser->roleBadgeClass());
    }
}
