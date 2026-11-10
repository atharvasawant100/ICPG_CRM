<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use App\Rbac\Rbac;
use Tests\Feature\Rbac\RbacTestCase;

class AttendanceModuleTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/attendance')->assertRedirect(route('login'));
        $this->get('/admin/attendance/create')->assertRedirect(route('login'));
        $this->get('/admin/attendance/monthly')->assertRedirect(route('login'));
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $user = $this->rolelessUser();

        $this->actingAs($user)->get('/admin/attendance')->assertForbidden();
        $this->actingAs($user)->get('/admin/attendance/create')->assertForbidden();
        $this->actingAs($user)->get('/admin/attendance/monthly')->assertForbidden();
    }

    public function test_admin_can_open_attendance_index_and_create(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/attendance')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/attendance/create')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/attendance/monthly')->assertSuccessful();
    }

    public function test_admin_can_store_attendance_records(): void
    {
        $admin = $this->admin();
        $emp1 = User::factory()->create(['name' => 'Alice Worker', 'emp_id' => 'ICPG_01']);
        $emp2 = User::factory()->create(['name' => 'Bob Worker', 'emp_id' => 'ICPG_02']);

        $response = $this->actingAs($admin)->post('/admin/attendance', [
            'date' => '2026-10-09',
            'records' => [
                $emp1->id => [
                    'status' => 'present',
                    'check_in' => '09:30',
                    'check_out' => '18:30',
                    'notes' => 'On time',
                ],
                $emp2->id => [
                    'status' => 'late',
                    'check_in' => '10:45',
                    'check_out' => '18:30',
                    'notes' => 'Traffic delay',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.attendance.index', ['date' => '2026-10-09']));

        $att1 = Attendance::where('user_id', $emp1->id)->whereDate('date', '2026-10-09')->firstOrFail();
        $this->assertSame('present', $att1->status);
        $this->assertEquals(540, $att1->total_minutes); // 9h = 540m
        $this->assertSame('9h', $att1->working_hours_formatted);

        $att2 = Attendance::where('user_id', $emp2->id)->whereDate('date', '2026-10-09')->firstOrFail();
        $this->assertSame('late', $att2->status);
        $this->assertEquals(465, $att2->total_minutes); // 7h 45m
        $this->assertSame('7h 45m', $att2->working_hours_formatted);
    }

    public function test_admin_can_update_attendance_record(): void
    {
        $admin = $this->admin();
        $emp = User::factory()->create(['emp_id' => 'ICPG_03']);

        $att = Attendance::create([
            'user_id' => $emp->id,
            'date' => '2026-10-09',
            'status' => 'present',
            'check_in' => '09:30',
            'check_out' => '18:30',
            'total_minutes' => 540,
        ]);

        $response = $this->actingAs($admin)->put("/admin/attendance/{$att->id}", [
            'status' => 'half_day',
            'check_in' => '09:30',
            'check_out' => '13:30',
            'notes' => 'Approved personal leave afternoon',
        ]);

        $response->assertRedirect(route('admin.attendance.index', ['date' => '2026-10-09']));

        $att->refresh();
        $this->assertSame('half_day', $att->status);
        $this->assertEquals(240, $att->total_minutes);
        $this->assertSame('4h', $att->working_hours_formatted);
    }

    public function test_admin_does_not_appear_in_employee_attendance_sheet(): void
    {
        $admin = $this->admin();
        $emp = User::factory()->create(['name' => 'Real Employee', 'emp_id' => 'ICPG_04']);

        $response = $this->actingAs($admin)->get('/admin/attendance');
        $response->assertSuccessful();
        $response->assertSee('Real Employee');
        // Admin should not have a row in employee attendance sheet
        $response->assertDontSee($admin->email);
    }

    public function test_hr_can_watch_attendance_for_all_departments_and_all_employees(): void
    {
        $hrRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'hr', 'guard_name' => Rbac::GUARD]);
        $hrRole->givePermissionTo(\Spatie\Permission\Models\Permission::whereIn('name', ['attendance.view', 'attendance.create', 'attendance.edit'])->get());

        $hrUser = User::factory()->create([
            'name' => 'Katrina HR',
            'department' => 'hr',
            'designation' => 'hr executive',
        ]);
        $hrUser->assignRbacRole('hr');

        $devEmp = User::factory()->create(['name' => 'Dev Engineer', 'department' => 'development']);
        $salesEmp = User::factory()->create(['name' => 'Sales Lead', 'department' => 'sales']);
        $marketingEmp = User::factory()->create(['name' => 'Marketing Guru', 'department' => 'marketing']);

        $response = $this->actingAs($hrUser)->get('/admin/attendance');
        $response->assertSuccessful();
        $response->assertSee('Dev Engineer');
        $response->assertSee('Sales Lead');
        $response->assertSee('Marketing Guru');

        // And in monthly summary view
        $monthlyResponse = $this->actingAs($hrUser)->get('/admin/attendance/monthly');
        $monthlyResponse->assertSuccessful();
        $monthlyResponse->assertSee('Dev Engineer');
        $monthlyResponse->assertSee('Sales Lead');
        $monthlyResponse->assertSee('Marketing Guru');

        // Department filter should also work properly for HR
        $filteredResponse = $this->actingAs($hrUser)->get('/admin/attendance?department=sales');
        $filteredResponse->assertSuccessful();
        $filteredResponse->assertSee('Sales Lead');
        $filteredResponse->assertDontSee('Dev Engineer');
    }
}

