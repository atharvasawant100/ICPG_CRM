<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\LeaveTypeSeeder;
use Tests\Feature\Rbac\RbacTestCase;

class LeaveModuleTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
        $this->seed(LeaveTypeSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/leaves')->assertRedirect(route('login'));
        $this->get('/admin/leaves/create')->assertRedirect(route('login'));
        $this->get('/admin/leaves/balances')->assertRedirect(route('login'));
        $this->get('/admin/leaves/types')->assertRedirect(route('login'));
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $user = $this->rolelessUser();

        $this->actingAs($user)->get('/admin/leaves')->assertForbidden();
        $this->actingAs($user)->get('/admin/leaves/create')->assertForbidden();
        $this->actingAs($user)->get('/admin/leaves/balances')->assertForbidden();
        $this->actingAs($user)->get('/admin/leaves/types')->assertForbidden();
    }

    public function test_admin_can_open_leave_pages(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/leaves')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/leaves/create')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/leaves/balances')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/leaves/types')->assertSuccessful();
    }

    public function test_admin_can_record_approved_leave_and_it_syncs_to_attendance(): void
    {
        $admin = $this->admin();
        $emp = User::factory()->create(['name' => 'Charlie Test', 'emp_id' => 'ICPG_05']);
        $clType = LeaveType::where('code', 'CL')->firstOrFail();

        $response = $this->actingAs($admin)->post('/admin/leaves', [
            'user_id' => $emp->id,
            'leave_type_id' => $clType->id,
            'start_date' => '2026-10-12',
            'end_date' => '2026-10-13',
            'is_half_day' => 0,
            'reason' => 'Family travel',
            'status' => 'approved',
            'admin_remarks' => 'Approved in advance',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.leaves.index'));

        // Verify Leave Request created
        $leave = LeaveRequest::where('user_id', $emp->id)->firstOrFail();
        $this->assertSame('approved', $leave->status);
        $this->assertEquals(2.0, (float) $leave->total_days);

        // Verify Balance deducted
        $balance = EmployeeLeaveBalance::where('user_id', $emp->id)
            ->where('leave_type_id', $clType->id)
            ->firstOrFail();
        $this->assertEquals(2.0, (float) $balance->used_days);
        $this->assertEquals(10.0, (float) $balance->remaining_days); // 12 - 2

        // Verify Attendance synced for both dates
        $att1 = Attendance::where('user_id', $emp->id)->whereDate('date', '2026-10-12')->firstOrFail();
        $this->assertSame('on_leave', $att1->status);

        $att2 = Attendance::where('user_id', $emp->id)->whereDate('date', '2026-10-13')->firstOrFail();
        $this->assertSame('on_leave', $att2->status);
    }

    public function test_admin_can_approve_pending_leave_request(): void
    {
        $admin = $this->admin();
        $emp = User::factory()->create(['name' => 'Dana Test', 'emp_id' => 'ICPG_06']);
        $slType = LeaveType::where('code', 'SL')->firstOrFail();

        // Create pending request
        $leave = LeaveRequest::create([
            'user_id' => $emp->id,
            'leave_type_id' => $slType->id,
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-15',
            'total_days' => 1.0,
            'reason' => 'Fever and cold',
            'status' => 'pending',
        ]);

        $balance = EmployeeLeaveBalance::getOrCreateForUser($emp->id, $slType->id, 2026);
        $balance->pending_days = 1.0;
        $balance->save();

        // Approve it
        $response = $this->actingAs($admin)->post("/admin/leaves/{$leave->id}/approve", [
            'remarks' => 'Get well soon',
        ]);

        $response->assertRedirect();

        $leave->refresh();
        $this->assertSame('approved', $leave->status);
        $this->assertSame('Get well soon', $leave->admin_remarks);

        // Check balance
        $balance->refresh();
        $this->assertEquals(0.0, (float) $balance->pending_days);
        $this->assertEquals(1.0, (float) $balance->used_days);
        $this->assertEquals(9.0, (float) $balance->remaining_days); // 10 - 1

        // Check attendance record created
        $att = Attendance::where('user_id', $emp->id)->whereDate('date', '2026-10-15')->firstOrFail();
        $this->assertSame('on_leave', $att->status);
    }

    public function test_admin_can_reject_leave_and_revert_attendance(): void
    {
        $admin = $this->admin();
        $emp = User::factory()->create(['name' => 'Evan Test', 'emp_id' => 'ICPG_07']);
        $clType = LeaveType::where('code', 'CL')->firstOrFail();

        // Create an approved leave request
        $leave = LeaveRequest::create([
            'user_id' => $emp->id,
            'leave_type_id' => $clType->id,
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-20',
            'total_days' => 1.0,
            'status' => 'approved',
            'action_by' => $admin->id,
        ]);
        $leave->syncToAttendance();

        $balance = EmployeeLeaveBalance::getOrCreateForUser($emp->id, $clType->id, 2026);
        $balance->used_days = 1.0;
        $balance->recompute()->save();

        // Reject it
        $response = $this->actingAs($admin)->post("/admin/leaves/{$leave->id}/reject", [
            'remarks' => 'Cancelled due to urgent deployment',
        ]);

        $response->assertRedirect();

        $leave->refresh();
        $this->assertSame('rejected', $leave->status);

        // Balance restored
        $balance->refresh();
        $this->assertEquals(0.0, (float) $balance->used_days);
        $this->assertEquals(12.0, (float) $balance->remaining_days);

        // Synced attendance removed
        $att = Attendance::where('user_id', $emp->id)->whereDate('date', '2026-10-20')->first();
        $this->assertNull($att);
    }

    public function test_admin_can_create_new_leave_type(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post('/admin/leaves/types', [
            'name' => 'Maternity Leave',
            'code' => 'ML',
            'days_allowed_per_year' => 90,
            'is_paid' => 1,
        ]);

        $response->assertRedirect(route('admin.leaves.types'));

        $this->assertDatabaseHas('leave_types', [
            'code' => 'ML',
            'days_allowed_per_year' => 90,
            'is_paid' => 1,
        ]);
    }

    public function test_admin_can_update_leave_type_and_sync_balances(): void
    {
        $admin = $this->admin();
        $emp = User::factory()->create(['emp_id' => 'ICPG_08']);
        $cl = LeaveType::where('code', 'CL')->firstOrFail();

        // Create balance for current year
        $balance = EmployeeLeaveBalance::getOrCreateForUser($emp->id, $cl->id, now()->year);
        $this->assertEquals(12.0, (float) $balance->allocated_days);

        // Admin updates CL quota to 14 days and checks sync checkbox
        $response = $this->actingAs($admin)->put("/admin/leaves/types/{$cl->id}", [
            'name' => 'Casual Leave (CL)',
            'days_allowed_per_year' => 14,
            'is_paid' => 1,
            'update_existing_balances' => 1,
        ]);

        $response->assertRedirect(route('admin.leaves.types'));

        $cl->refresh();
        $this->assertEquals(14.0, (float) $cl->days_allowed_per_year);

        $balance->refresh();
        $this->assertEquals(14.0, (float) $balance->allocated_days);
        $this->assertEquals(14.0, (float) $balance->remaining_days);
    }

    public function test_admin_can_adjust_individual_employee_leave_balance(): void
    {
        $admin = $this->admin();
        $emp = User::factory()->create(['emp_id' => 'ICPG_09']);
        $sl = LeaveType::where('code', 'SL')->firstOrFail();

        $balance = EmployeeLeaveBalance::getOrCreateForUser($emp->id, $sl->id, now()->year);
        $this->assertEquals(10.0, (float) $balance->allocated_days);

        // Adjust allocation to 15 days specifically for this employee
        $response = $this->actingAs($admin)->post("/admin/leaves/balances/{$balance->id}", [
            'allocated_days' => 15,
        ]);

        $response->assertRedirect();

        $balance->refresh();
        $this->assertEquals(15.0, (float) $balance->allocated_days);
        $this->assertEquals(15.0, (float) $balance->remaining_days);
    }

    public function test_hr_can_view_and_approve_leave_for_any_department_employee_or_manager(): void
    {
        $hrRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'hr', 'guard_name' => \App\Rbac\Rbac::GUARD]);
        $hrRole->givePermissionTo(\Spatie\Permission\Models\Permission::whereIn('name', ['leaves.view', 'leaves.create', 'leaves.edit', 'leaves.approve'])->get());

        $hrUser = User::factory()->create([
            'name' => 'Katrina HR',
            'department' => 'hr',
            'designation' => 'hr executive',
        ]);
        $hrUser->assignRbacRole('hr');

        $salesManager = User::factory()->create([
            'name' => 'Sara SalesMgr',
            'department' => 'sales',
            'designation' => 'sales manager',
        ]);
        $devEngineer = User::factory()->create([
            'name' => 'Dave Dev',
            'department' => 'development',
            'designation' => 'software developer',
        ]);

        $cl = LeaveType::where('code', 'CL')->firstOrFail();

        // 1. Sales Manager applies for leave
        $leaveManager = LeaveRequest::create([
            'user_id' => $salesManager->id,
            'leave_type_id' => $cl->id,
            'start_date' => '2026-10-25',
            'end_date' => '2026-10-25',
            'total_days' => 1.0,
            'reason' => 'Manager personal work',
            'status' => 'pending',
        ]);
        $balManager = EmployeeLeaveBalance::getOrCreateForUser($salesManager->id, $cl->id, 2026);
        $balManager->pending_days = 1.0;
        $balManager->save();

        // 2. Dev Engineer applies for leave
        $leaveDev = LeaveRequest::create([
            'user_id' => $devEngineer->id,
            'leave_type_id' => $cl->id,
            'start_date' => '2026-10-26',
            'end_date' => '2026-10-26',
            'total_days' => 1.0,
            'reason' => 'Dev engineer sick leave',
            'status' => 'pending',
        ]);
        $balDev = EmployeeLeaveBalance::getOrCreateForUser($devEngineer->id, $cl->id, 2026);
        $balDev->pending_days = 1.0;
        $balDev->save();

        // HR views the leaves index: should see both from different departments and roles
        $response = $this->actingAs($hrUser)->get('/admin/leaves');
        $response->assertSuccessful();
        $response->assertSee('Sara SalesMgr');
        $response->assertSee('Dave Dev');

        // HR approves Sales Manager leave
        $approveMgr = $this->actingAs($hrUser)->post("/admin/leaves/{$leaveManager->id}/approve", [
            'remarks' => 'Approved by HR for Manager',
        ]);
        $approveMgr->assertRedirect();
        $leaveManager->refresh();
        $this->assertSame('approved', $leaveManager->status);
        $this->assertEquals($hrUser->id, $leaveManager->action_by);

        // HR approves Dev Engineer leave
        $approveDev = $this->actingAs($hrUser)->post("/admin/leaves/{$leaveDev->id}/approve", [
            'remarks' => 'Approved by HR for Developer',
        ]);
        $approveDev->assertRedirect();
        $leaveDev->refresh();
        $this->assertSame('approved', $leaveDev->status);
        $this->assertEquals($hrUser->id, $leaveDev->action_by);
    }

    public function test_manager_can_see_leave_balances_of_his_department_only_not_whole_office(): void
    {
        $managerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'manager', 'guard_name' => \App\Rbac\Rbac::GUARD]);
        $managerRole->givePermissionTo(\Spatie\Permission\Models\Permission::whereIn('name', ['leaves.view'])->get());

        $devManager = User::factory()->create([
            'name' => 'Atharva Sawant',
            'department' => 'development',
            'designation' => 'manager',
            'is_active' => true,
        ]);
        $devManager->assignRbacRole('manager');

        $devEmp = User::factory()->create([
            'name' => 'Omkar Dev',
            'department' => 'development',
            'is_active' => true,
        ]);

        $marketingEmp = User::factory()->create([
            'name' => 'Aditya Marketing',
            'department' => 'marketing',
            'is_active' => true,
        ]);

        $salesEmp = User::factory()->create([
            'name' => 'Shreya Sales',
            'department' => 'sales',
            'is_active' => true,
        ]);

        // 1. When Development Manager views leave balances
        $response = $this->actingAs($devManager)->get('/admin/leaves/balances');
        $response->assertSuccessful();

        // Must see development department members
        $response->assertSee('Atharva Sawant');
        $response->assertSee('Omkar Dev');

        // Must NOT see members from other departments in the office
        $response->assertDontSee('Aditya Marketing');
        $response->assertDontSee('Shreya Sales');

        // 2. Querying ?department=marketing should not leak marketing employees to the dev manager
        $responseQueried = $this->actingAs($devManager)->get('/admin/leaves/balances?department=marketing');
        $responseQueried->assertSuccessful();
        $responseQueried->assertDontSee('Aditya Marketing');
        $responseQueried->assertSee('Omkar Dev');
    }

    public function test_leave_approval_dispatches_email_from_hr_to_employee(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $hrRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'hr', 'guard_name' => \App\Rbac\Rbac::GUARD]);
        $hrRole->givePermissionTo(\Spatie\Permission\Models\Permission::whereIn('name', ['leaves.view', 'leaves.approve'])->get());

        $hrUser = User::factory()->create([
            'name' => 'HR Manager',
            'email' => 'hr-manager@iconicpages.com',
            'department' => 'hr',
            'designation' => 'hr head',
        ]);
        $hrUser->assignRbacRole('hr');

        $employee = User::factory()->create([
            'name' => 'John Developer',
            'email' => 'john.dev@iconicpages.com',
            'department' => 'development',
        ]);

        $cl = LeaveType::where('code', 'CL')->firstOrFail();
        $balance = EmployeeLeaveBalance::getOrCreateForUser($employee->id, $cl->id, now()->year);
        $balance->pending_days = 2.0;
        $balance->save();

        $leave = LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $cl->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'total_days' => 2.0,
            'reason' => 'Family vacation',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($hrUser)->post("/admin/leaves/{$leave->id}/approve", [
            'remarks' => 'Approved, have a good time!',
        ]);

        $response->assertRedirect();
        $leave->refresh();
        $this->assertSame('approved', $leave->status);

        // Verify EmailLog entry was recorded
        $log = \App\Models\EmailLog::where('type', \App\Models\EmailLog::TYPE_LEAVE_APPROVED)
            ->where('recipient_email', 'john.dev@iconicpages.com')
            ->first();

        $this->assertNotNull($log, 'EmailLog must record leave approval email.');
        $this->assertEquals('John Developer', $log->recipient_name);
        $this->assertStringContainsString('Leave Request Approved', $log->subject);

        // Verify Job was dispatched to emails queue
        \Illuminate\Support\Facades\Queue::assertPushedOn(
            \App\Models\EmailLog::QUEUE,
            \App\Jobs\SendLeaveApprovedEmail::class,
            function ($job) use ($log) {
                return $job->emailLogId === $log->id;
            }
        );

        // Verify Mailable sender and recipient details
        $mailable = new \App\Mail\LeaveApprovedMail($leave);
        $mailable->build();

        $this->assertTrue($mailable->hasFrom('hr@iconicpages.com'));
        $this->assertTrue($mailable->hasTo('john.dev@iconicpages.com'));
        $this->assertStringContainsString('Leave Request Approved', $mailable->subject);
    }
}

