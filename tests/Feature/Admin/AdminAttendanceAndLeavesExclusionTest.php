<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Rbac\Rbac;
use Database\Seeders\LeaveTypeSeeder;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Feature\Rbac\RbacTestCase;

class AdminAttendanceAndLeavesExclusionTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
        $this->seed(LeaveTypeSeeder::class);
    }

    private function sidebar(string $html): string
    {
        $start = strpos($html, '<aside');
        $end = $html === '' ? false : strpos($html, '</aside>');

        return ($start !== false && $end !== false) ? substr($html, $start, $end - $start) : '';
    }

    public function test_gate_before_denies_self_service_attendance_and_leaves_for_admin(): void
    {
        $admin = $this->admin();

        $this->assertFalse(Gate::forUser($admin)->allows('my_attendance.view'));
        $this->assertFalse(Gate::forUser($admin)->allows('my_leaves.view'));
        $this->assertFalse(Gate::forUser($admin)->allows('my_leaves.create'));

        // Admin still retains administration permissions
        $this->assertTrue(Gate::forUser($admin)->allows('attendance.view'));
        $this->assertTrue(Gate::forUser($admin)->allows('attendance.create'));
        $this->assertTrue(Gate::forUser($admin)->allows('leaves.view'));
        $this->assertTrue(Gate::forUser($admin)->allows('leaves.create'));
        $this->assertTrue(Gate::forUser($admin)->allows('dashboard.view'));
    }

    public function test_admin_sidebar_does_not_contain_my_attendance_or_my_leaves(): void
    {
        $admin = $this->admin();
        $html = $this->actingAs($admin)->get('/dashboard')->assertSuccessful()->getContent();
        $nav = $this->sidebar($html);

        $this->assertNotSame('', $nav);
        $this->assertStringNotContainsString('My Attendance', $nav, 'Admin must not have My Attendance in sidebar');
        $this->assertStringNotContainsString('My Leaves', $nav, 'Admin must not have My Leaves in sidebar');

        // Admin administration section keeps only Roles & Permissions, Departments, Audit Log
        $this->assertStringNotContainsString('Attendance', $nav);
        $this->assertStringNotContainsString('Leave Management', $nav);
        $this->assertStringContainsString('Roles &amp; Permissions', $nav);
        $this->assertStringContainsString('Departments', $nav);
        $this->assertStringContainsString('Audit Log', $nav);
    }

    public function test_employee_sees_my_attendance_and_my_leaves_in_sidebar(): void
    {
        $role = Role::firstOrCreate(['name' => 'employee_role', 'guard_name' => Rbac::GUARD]);
        $role->syncPermissions(
            Permission::whereIn('name', ['dashboard.view', 'my_attendance.view', 'my_leaves.view'])->get()
        );
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $emp = User::factory()->create();
        $emp->assignRbacRole('employee_role');

        $html = $this->actingAs($emp)->get('/dashboard')->assertSuccessful()->getContent();
        $nav = $this->sidebar($html);

        $this->assertStringContainsString('My Attendance', $nav);
        $this->assertStringContainsString('My Leaves', $nav);
    }

    public function test_admin_visiting_my_attendance_is_redirected_to_admin_attendance(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/my-attendance');
        $response->assertRedirect(route('admin.attendance.index'));
        $response->assertSessionHas('info');
    }

    public function test_admin_visiting_my_leaves_is_redirected_to_admin_leaves(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/my-leaves');
        $response->assertRedirect(route('admin.leaves.index'));
        $response->assertSessionHas('info');
    }

    public function test_admin_submitting_my_leaves_is_redirected(): void
    {
        $admin = $this->admin();
        $cl = LeaveType::where('code', 'CL')->firstOrFail();

        $response = $this->actingAs($admin)->post('/my-leaves', [
            'leave_type_id' => $cl->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(1)->toDateString(),
            'reason' => 'Admin test leave',
        ]);

        $response->assertRedirect(route('admin.leaves.index'));
        $this->assertDatabaseMissing('leave_requests', [
            'user_id' => $admin->id,
        ]);
    }

    public function test_punch_in_and_punch_out_rejects_admin(): void
    {
        $admin = $this->admin();

        $responseIn = $this->actingAs($admin)->post('/attendance/punch-in');
        $responseIn->assertSessionHas('info', 'Admin accounts do not require attendance punching.');

        $this->assertDatabaseMissing('attendances', [
            'user_id' => $admin->id,
        ]);
    }
}
