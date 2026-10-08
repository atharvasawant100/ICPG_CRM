<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('##########'),
            'company' => fake()->company(),
            'status' => 'new',
            'source' => fake()->randomElement(['website', 'referral', 'cold_call', 'social_media']),
            'created_by' => User::factory(),
        ];
    }
}