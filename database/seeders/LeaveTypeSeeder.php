<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Casual Leave (CL)',
                'code' => 'CL',
                'days_allowed_per_year' => 12.0,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Sick Leave (SL)',
                'code' => 'SL',
                'days_allowed_per_year' => 10.0,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Privilege Leave (PL)',
                'code' => 'PL',
                'days_allowed_per_year' => 15.0,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Loss of Pay (LOP)',
                'code' => 'LOP',
                'days_allowed_per_year' => 0.0,
                'is_paid' => false,
                'requires_approval' => true,
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}
