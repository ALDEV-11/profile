<?php

namespace Database\Factories;

use App\Enums\DeviceType;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visitor>
 */
class VisitorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'page_visited' => '/',
            'referer' => fake()->optional(0.5)->url(),
            'device_type' => fake()->randomElement(DeviceType::cases()),
            'visited_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }
}
