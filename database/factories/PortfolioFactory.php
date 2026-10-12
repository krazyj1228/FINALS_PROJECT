<?php

namespace Database\Factories;

use App\Models\Portfolio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Portfolio>
 */
class PortfolioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'contact_number' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'about_me' => fake()->paragraph(),
            'education' => [],
            'skills' => [],
            'skill_levels' => [],
            'projects' => [],
            'experience' => [],
            'social_links' => [],
            'template' => 'simple',
        ];
    }
}
