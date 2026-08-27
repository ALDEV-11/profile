<?php

namespace Database\Factories;

use App\Enums\GalleryCategory;
use App\Models\Gallery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
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
            'image_path' => 'galleries/'.fake()->image('storage/app/public/galleries', 640, 480, null, false),
            'caption' => fake()->optional()->sentence(),
            'category' => fake()->randomElement(GalleryCategory::cases()),
            'order' => 0,
        ];
    }
}
