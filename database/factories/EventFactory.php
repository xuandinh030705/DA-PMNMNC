<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);
        $startTime = fake()->dateTimeBetween('+1 days', '+1 months');
        $endTime = (clone $startTime)->modify('+4 hours');

        return [
            'organizer_id' => User::factory()->organizer(),
            'category_id' => Category::factory(),
            'venue_id' => Venue::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 9999),
            'description' => fake()->paragraphs(3, true),
            'banner_path' => null,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => Event::STATUS_PUBLISHED,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Event::STATUS_DRAFT,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Event::STATUS_PENDING,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Event::STATUS_PUBLISHED,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Event::STATUS_CANCELLED,
        ]);
    }
}
