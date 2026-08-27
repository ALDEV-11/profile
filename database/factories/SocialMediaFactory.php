<?php

namespace Database\Factories;

use App\Models\SocialMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialMedia>
 */
class SocialMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'platform' => fake()->randomElement(['GitHub', 'Instagram', 'LinkedIn', 'Twitter', 'Portfolio']),
            'url' => fake()->url(),
            'icon' => null,
            'order' => 0,
        ];
    }
}
