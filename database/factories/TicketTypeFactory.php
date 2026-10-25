<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketType>
 */
class TicketTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->randomElement(['Vé Tiêu chuẩn', 'Vé VIP', 'Vé Early Bird', 'Vé SV']),
            'price' => fake()->randomElement([100000, 200000, 500000, 1000000]),
            'quantity' => 100,
            'sold' => 0,
            'sale_start' => now()->subDays(5),
            'sale_end' => now()->addDays(20),
            'max_per_order' => 10,
        ];
    }
}
