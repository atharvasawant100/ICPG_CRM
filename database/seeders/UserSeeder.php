<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Default Admin User
        User::updateOrCreate(
            ['email' => 'admin@iconicpages.com'],
            [
                'name' => 'Admin',
                'email' => 'admin@iconicpages.com',
                'password' => Hash::make('Iconicpages1100@'),
                'role' => 'admin',
            ]
        );
    }
}
