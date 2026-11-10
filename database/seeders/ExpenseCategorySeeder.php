<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'AI tools', 'description' => 'AI tools and software subscriptions', 'is_custom' => false],
            ['name' => 'Trip Expense', 'description' => 'Business trips, lodging, and client visits', 'is_custom' => false],
            ['name' => 'Travel Expense', 'description' => 'Local travel, cab, transit, and conveyance', 'is_custom' => false],
            ['name' => 'Others', 'description' => 'Other miscellaneous business expenses', 'is_custom' => false],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
