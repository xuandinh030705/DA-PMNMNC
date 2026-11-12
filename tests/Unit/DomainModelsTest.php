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
        $organizer = new User(['role' => UserRole::ORGANIZER->value]);
        $customer = new User(['role' => UserRole::CUSTOMER->value]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isOrganizer());
        $this->assertTrue($organizer->isOrganizer());
        $this->assertTrue($customer->isCustomer());
        $this->assertSame(Event::STATUS_PUBLISHED, EventStatus::PUBLISHED->value);
        $this->assertSame(Order::STATUS_PAID, OrderStatus::PAID->value);
        $this->assertSame(Ticket::STATUS_VALID, TicketStatus::VALID->value);
    }
}
