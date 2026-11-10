<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\User;
use App\Services\AttendanceAutoAbsentService;
use Carbon\Carbon;
use Tests\Feature\Rbac\RbacTestCase;

class HolidayModuleTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/holidays')->assertRedirect(route('login'));
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $user = $this->rolelessUser();

        $this->actingAs($user)->get('/admin/holidays')->assertForbidden();
        $this->actingAs($user)->post('/admin/holidays', [
            'name' => 'Diwali',
            'date' => '2026-11-01',
        ])->assertForbidden();
    }

    public function test_admin_and_hr_can_manage_holidays(): void
    {
        $admin = $this->admin();

        // 1. Admin creates holiday
        $response = $this->actingAs($admin)->post('/admin/holidays', [
            'name' => 'Diwali Festival',
            'date' => '2026-11-01',
            'description' => 'Festival of Lights',
        ]);

        $response->assertRedirect('/admin/holidays?year=2026');
        $this->assertDatabaseHas('holidays', [
            'name' => 'Diwali Festival',
            'date' => '2026-11-01 00:00:00',
        ]);

        $holiday = Holiday::where('name', 'Diwali Festival')->first();

        // 2. Index page lists the holiday
        $this->actingAs($admin)->get('/admin/holidays')->assertSee('Diwali Festival');

        // 3. Update holiday
        $this->actingAs($admin)->put("/admin/holidays/{$holiday->id}", [
            'name' => 'Diwali Laxmi Pujan',
            'date' => '2026-11-01',
            'description' => 'Updated celebration',
        ])->assertRedirect('/admin/holidays?year=2026');

        $this->assertDatabaseHas('holidays', [
            'id' => $holiday->id,
            'name' => 'Diwali Laxmi Pujan',
        ]);

        // 4. Delete holiday
        $this->actingAs($admin)->delete("/admin/holidays/{$holiday->id}")
            ->assertRedirect('/admin/holidays?year=2026');

        $this->assertDatabaseMissing('holidays', [
            'id' => $holiday->id,
        ]);
    }

    public function test_auto_absent_service_marks_unpunched_as_absent_and_exempts_holidays(): void
    {
        $employee = User::factory()->create([
            'name' => 'John Test Worker',
            'emp_id' => 'ICPG_99',
            'is_active' => true,
            'created_at' => '2026-10-01 09:00:00',
        ]);

        $service = app(AttendanceAutoAbsentService::class);

        // Regular weekday without punch-in -> should be marked ABSENT
        $pastWeekday = Carbon::parse('2026-10-21'); // Wednesday
        $result = $service->markAbsentsForDate($pastWeekday);

        $this->assertGreaterThanOrEqual(1, $result['marked_absent']);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $employee->id,
            'date' => '2026-10-21 00:00:00',
            'status' => 'absent',
        ]);

        // Official holiday date -> should be marked HOLIDAY, not absent!
        Holiday::create([
            'name' => 'Gandhi Jayanti',
            'date' => '2026-10-02',
            'description' => 'National Holiday',
        ]);

        $holidayDate = Carbon::parse('2026-10-02'); // Friday
        $service->markAbsentsForDate($holidayDate);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $employee->id,
            'date' => '2026-10-02 00:00:00',
            'status' => 'holiday',
        ]);
    }
}
