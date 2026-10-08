<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            // Column is `rate`, not `price`.
            'rate' => fake()->randomFloat(2, 500, 10000),
            'frequency' => 'one-time',
            'custom_interval_days' => null,
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}