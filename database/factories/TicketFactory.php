<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_item_id' => OrderItem::factory(),
            'ticket_code' => Ticket::generateUniqueTicketCode(),
            'status' => Ticket::STATUS_VALID,
            'checked_in_at' => null,
        ];
    }

    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_VALID,
            'checked_in_at' => null,
        ]);
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_USED,
            'checked_in_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_CANCELLED,
        ]);
    }
}
