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
            ['name' => 'Travel', 'description' => 'Travel and transportation expenses', 'is_custom' => false],
            ['name' => 'Food', 'description' => 'Food and dining expenses', 'is_custom' => false],
            ['name' => 'Office Supplies', 'description' => 'Office supplies and stationery', 'is_custom' => false],
            ['name' => 'Utilities', 'description' => 'Utility bills and services', 'is_custom' => false],
            ['name' => 'Marketing', 'description' => 'Marketing and advertising expenses', 'is_custom' => false],
            ['name' => 'Equipment', 'description' => 'Equipment and hardware purchases', 'is_custom' => false],
            ['name' => 'Other', 'description' => 'Other miscellaneous expenses', 'is_custom' => false],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
