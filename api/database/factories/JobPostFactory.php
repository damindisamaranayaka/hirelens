<?php

namespace Database\Factories;

use App\Models\JobPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobPost>
 */
class JobPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->employer(),
            'title' => fake()->jobTitle(),
            'company_name' => fake()->company(),
            'description' => fake()->paragraphs(3, true),
            'location' => fake()->city(),
            'type' => fake()->randomElement([
                'full-time',
                'part-time',
                'internship',
                'contract',
            ]),
            'salary_min' => fake()->numberBetween(30000, 80000),
            'salary_max' => fake()->numberBetween(85000, 200000),
            'status' => 'open',
        ];
    }
}
