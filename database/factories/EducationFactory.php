<?php

namespace Database\Factories;

use App\Models\Education;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Education>
 */
class EducationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-10 years', '-1 year');

        return [
            'user_id' => User::factory(),
            'institution' => fake()->company().' University',
            'field_of_study' => fake()->randomElement(['Informatika', 'Sistem Informasi', 'Teknik Komputer', 'Manajemen']),
            'degree' => fake()->randomElement(['D3', 'D4', 'S1', 'S2']),
            'start_date' => $start,
            'end_date' => null,
            'is_currently_studying' => true,
            'order' => 0,
        ];
    }
}
