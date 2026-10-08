<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            // category_id is NOT NULL in the schema.
            'category_id' => \App\Models\ExpenseCategory::factory(),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'description' => fake()->sentence(4),
            'expense_date' => now()->toDateString(),
            'status' => 'pending',
            'receipt' => null,
            'user_id' => User::factory(),
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ];
    }
}