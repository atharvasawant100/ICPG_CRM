<?php

namespace Tests\Feature\Employee;

use App\Models\Customer;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\RecurringInvoiceTemplate;
use App\Models\Task;
use App\Models\User;
use App\Notifications\FollowupReminderNotification;
use App\Notifications\LeaveApprovedNotification;
use App\Notifications\LeaveRequestNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCompletedNotification;
use App\Rbac\Rbac;
use App\Services\NotificationFeed;
use Database\Seeders\LeaveTypeSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Feature\Rbac\RbacTestCase;

/**
 * The notification bell is deliberately narrow, and the rule differs per role:
 *
 *   employee : a task was assigned to me, my leave was approved
 *   manager  : a task I assigned was completed, a leave request from my team
 *   hr       : a leave request from anywhere in the company
 *   admin    : everything
 */
class EmployeeNotificationsTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
        $this->seed(LeaveTypeSeeder::class);

        foreach (['tasks.view', 'tasks.create', 'leaves.approve', 'leaves.view'] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => Rbac::GUARD]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * A plain employee: no management rights at all.
     *
     * The role name is deliberately NOT one of the seeded roles (manager,
     * sales, marketing, development, hr). Spatie caches roles and
     * permissions in memory for the whole test process, so borrowing a seeded
     * role here would leak permissions into the RBAC tests that run later.
     */
    private function employee(
        string $roleName = 'test_employee_role',
        ?User $manager = null,
        string $department = 'marketing'
    ): User {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => Rbac::GUARD]);
        $role->givePermissionTo(['dashboard.view', 'tasks.view', 'my_leaves.view', 'my_leaves.create']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create([
            'name' => 'Employee One',
            'manager_id' => $manager?->id,
            'department' => $department,
        ]);
        $user->assignRbacRole($roleName);

        return $user->fresh();
    }

    /**
     * A department manager.
     *
     * isManager() is satisfied by managing a department, so the seeded
     * 'manager' role is left untouched.
     */
    private function manager(string $department = 'marketing'): User
    {
        $role = Role::firstOrCreate(['name' => 'test_manager_role', 'guard_name' => Rbac::GUARD]);
        $role->givePermissionTo([
            'dashboard.view', 'tasks.view', 'tasks.create',
            'leaves.view', 'leaves.approve', 'team.view',
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create([
            'name' => 'Manager One',
            'department' => $department,
        ]);
        $user->assignRbacRole('test_manager_role');

        $dept = Department::firstOrCreate(['name' => $department]);
        $dept->manager_id = $user->id;
        $dept->save();

        return $user->fresh();
    }

    /**
     * An HR user, recognised by the HR department rather than the seeded role.
     */
    private function hr(): User
    {
        $role = Role::firstOrCreate(['name' => 'test_hr_role', 'guard_name' => Rbac::GUARD]);
        $role->givePermissionTo([
            'dashboard.view', 'tasks.view',
            'leaves.view', 'leaves.approve',
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create([
            'name' => 'HR One',
            'department' => 'hr',
        ]);
        $user->assignRbacRole('test_hr_role');

        return $user->fresh();
    }

    /**
     * Push a management-only notification into a user's inbox.
     */
    private function notifyFollowupReminder(User $user): void
    {
        $followup = (new \App\Models\LeadFollowup)->forceFill([
            'id' => 1,
            'lead_id' => 1,
            'followup_type' => 'call',
            'followup_date' => now(),
        ]);

        $user->notify(new FollowupReminderNotification($followup, 15));
    }

    // ---------------------------------------------------------------------
    // Employee feed
    // ---------------------------------------------------------------------

    public function test_assigning_a_task_notifies_the_assignee(): void
    {
        $manager = $this->manager();
        $employee = $this->employee('test_employee_role', $manager);

        $response = $this->actingAs($manager)->post(route('tasks.store'), [
            'title' => 'Fix the login redirect',
            'description' => 'Employees land on the wrong page after signing in.',
            'assigned_to' => $employee->id,
            'priority' => 'high',
            'due_date' => '2026-11-01',
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');

        $task = Task::where('assigned_to', $employee->id)->firstOrFail();

        $notification = $employee->notifications()->firstOrFail();

        $this->assertSame(TaskAssignedNotification::class, $notification->type);
        $this->assertSame('task_assigned', $notification->data['type']);
        $this->assertSame($task->id, $notification->data['task_id']);
        $this->assertStringContainsString('Fix the login redirect', $notification->data['message']);
        $this->assertStringContainsString('Manager One', $notification->data['message']);
    }

    public function test_reassigning_a_task_notifies_the_new_owner(): void
    {
        $manager = $this->manager();
        $employee = $this->employee();
        $other = $this->employee('test_employee_role_two');

        $task = Task::create([
            'title' => 'Existing task',
            'assigned_to' => $employee->id,
            'assigned_by' => $manager->id,
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)->put(route('tasks.update', $task), [
            'title' => 'Existing task',
            'assigned_to' => $other->id,
            'priority' => 'medium',
            'status' => 'pending',
        ])->assertRedirect();

        $this->assertSame(0, $employee->notifications()->count());
        $this->assertSame(1, $other->notifications()->count());
        $this->assertSame(
            TaskAssignedNotification::class,
            $other->notifications()->firstOrFail()->type
        );
    }

    public function test_assigning_a_task_to_yourself_does_not_notify_you(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)->post(route('tasks.store'), [
            'title' => 'Own task',
            'assigned_to' => $manager->id,
            'priority' => 'low',
        ])->assertRedirect();

        $this->assertSame(0, $manager->notifications()->count());
    }

    public function test_approving_a_leave_notifies_the_applicant(): void
    {
        $admin = $this->admin();
        $employee = $this->employee();

        $leave = LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => LeaveType::where('code', 'CL')->firstOrFail()->id,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-03',
            'total_days' => 2,
            'reason' => 'Family travel',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.leaves.approve', $leave))
            ->assertRedirect();

        $notification = $employee->notifications()->firstOrFail();

        $this->assertSame(LeaveApprovedNotification::class, $notification->type);
        $this->assertSame('leave_approved', $notification->data['type']);
        $this->assertSame($leave->id, $notification->data['leave_id']);
        $this->assertStringContainsString('approved', $notification->data['message']);
    }

    public function test_hr_approving_a_leave_notifies_the_applicant(): void
    {
        $hr = $this->hr();
        $employee = $this->employee();

        $leave = LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => LeaveType::where('code', 'CL')->firstOrFail()->id,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-03',
            'total_days' => 2,
            'reason' => 'Family travel',
            'status' => 'pending',
        ]);

        $this->actingAs($hr)->post(route('admin.leaves.approve', $leave))
            ->assertRedirect();

        $notification = $employee->notifications()->firstOrFail();

        $this->assertSame(LeaveApprovedNotification::class, $notification->type);
        $this->assertStringContainsString('HR One', $notification->data['message']);
    }

    public function test_employee_feed_hides_management_notifications(): void
    {
        $employee = $this->employee();
        $feed = app(NotificationFeed::class);

        // A management-only notification lands in the employee's inbox.
        $this->notifyFollowupReminder($employee);

        $this->assertSame(1, $employee->notifications()->count());
        $this->assertSame(0, $feed->unreadCount($employee));

        // A task assignment is allowed through.
        $task = Task::create([
            'title' => 'Allowed task',
            'assigned_to' => $employee->id,
            'assigned_by' => $employee->id,
            'priority' => 'low',
            'status' => 'pending',
        ]);
        $employee->notify(new TaskAssignedNotification($task, 'Manager One'));

        $this->assertSame(1, $feed->unreadCount($employee));
        $this->assertSame(
            TaskAssignedNotification::class,
            $feed->unread($employee)->firstOrFail()->type
        );
    }

    public function test_employee_does_not_see_task_completed_or_leave_requested(): void
    {
        $employee = $this->employee();
        $feed = app(NotificationFeed::class);

        $task = Task::create([
            'title' => 'Some task',
            'assigned_to' => $employee->id,
            'assigned_by' => $employee->id,
            'priority' => 'low',
            'status' => 'completed',
        ]);
        $employee->notify(new TaskCompletedNotification($task, 'Employee One'));

        $leave = LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => LeaveType::where('code', 'CL')->firstOrFail()->id,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-02',
            'total_days' => 1,
            'reason' => 'Family travel',
            'status' => 'pending',
        ]);
        $employee->notify(new LeaveRequestNotification($leave, 'Employee One'));

        $this->assertSame(2, $employee->notifications()->count());
        $this->assertSame(0, $feed->unreadCount($employee));
    }

    public function test_employee_bell_hides_upcoming_recurring_invoices(): void
    {
        $employee = $this->employee();
        $admin = $this->admin();

        $customer = Customer::factory()->create(['name' => 'Joshwa Mathew']);

        RecurringInvoiceTemplate::create([
            'name' => 'Recurring Invoice - Joshwa Mathew - Monthly',
            'customer_id' => $customer->id,
            'frequency' => 'monthly',
            'start_date' => now()->subMonth(),
            'next_generation_date' => now()->toDateString(),
            'subtotal' => 30000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $feed = app(NotificationFeed::class);

        $this->assertSame(0, $feed->upcomingCount($employee));
        $this->assertCount(0, $feed->upcomingTemplates($employee));

        $this->assertSame(1, $feed->upcomingCount($admin));
        $this->assertCount(1, $feed->upcomingTemplates($admin));

        // The dropdown itself must not render the invoice for an employee.
        $employeeHtml = $this->actingAs($employee)->get(route('dashboard'))->getContent();
        $this->assertStringNotContainsString('Recurring Invoice - Joshwa Mathew', $employeeHtml);

        $adminHtml = $this->actingAs($admin)->get(route('dashboard'))->getContent();
        $this->assertStringContainsString('Recurring Invoice - Joshwa Mathew', $adminHtml);
    }

    public function test_employee_poll_endpoint_only_returns_allowed_types(): void
    {
        $employee = $this->employee();
        $feed = app(NotificationFeed::class);

        $this->notifyFollowupReminder($employee);

        $task = Task::create([
            'title' => 'Allowed task',
            'assigned_to' => $employee->id,
            'assigned_by' => $employee->id,
            'priority' => 'low',
            'status' => 'pending',
        ]);
        $employee->notify(new TaskAssignedNotification($task, 'Manager One'));

        $response = $this->actingAs($employee)->getJson(route('notifications.poll'));

        $response->assertSuccessful();
        $response->assertJsonPath('unread_count', 1);
        $response->assertJsonPath('upcoming_count', 0);
        $response->assertJsonPath('total_count', 1);
        $response->assertJsonCount(1, 'notifications');
        $response->assertJsonPath('notifications.0.type', 'task_assigned');
    }

    // ---------------------------------------------------------------------
    // Manager feed
    // ---------------------------------------------------------------------

    public function test_task_completion_notifies_the_manager_who_assigned_it(): void
    {
        $manager = $this->manager('development');
        $employee = $this->employee('test_employee_role', $manager, 'development');

        $task = Task::create([
            'title' => 'Ship the pricing page',
            'assigned_to' => $employee->id,
            'assigned_by' => $manager->id,
            'priority' => 'high',
            'status' => 'in_progress',
        ]);

        $this->actingAs($employee)->patch(route('tasks.status', $task), [
            'status' => 'completed',
        ])->assertRedirect();

        $notification = $manager->notifications()->firstOrFail();

        $this->assertSame(TaskCompletedNotification::class, $notification->type);
        $this->assertSame('task_completed', $notification->data['type']);
        $this->assertSame($task->id, $notification->data['task_id']);
        $this->assertStringContainsString('Ship the pricing page', $notification->data['message']);
        $this->assertStringContainsString('Employee One', $notification->data['message']);
    }

    public function test_leave_request_notifies_the_team_manager(): void
    {
        $manager = $this->manager('development');
        $employee = $this->employee('test_employee_role', $manager, 'development');

        $this->actingAs($employee)->post(route('my-leaves.store'), [
            'leave_type_id' => LeaveType::where('code', 'CL')->firstOrFail()->id,
            'start_date' => '2026-11-09',
            'end_date' => '2026-11-10',
            'reason' => 'Family function',
        ])->assertRedirect(route('my-leaves.index'));

        $notification = $manager->notifications()->firstOrFail();

        $this->assertSame(LeaveRequestNotification::class, $notification->type);
        $this->assertSame('leave_requested', $notification->data['type']);
        $this->assertStringContainsString('Employee One', $notification->data['message']);
    }

    public function test_manager_feed_only_shows_manager_notifications(): void
    {
        $manager = $this->manager('marketing');
        $employee = $this->employee('test_employee_role', $manager, 'marketing');
        $feed = app(NotificationFeed::class);

        // Employee-type notifications must not reach a manager.
        $task = Task::create([
            'title' => 'Some task',
            'assigned_to' => $employee->id,
            'assigned_by' => $manager->id,
            'priority' => 'low',
            'status' => 'pending',
        ]);
        $manager->notify(new TaskAssignedNotification($task, 'Manager One'));

        $this->assertSame(1, $manager->notifications()->count());
        $this->assertSame(0, $feed->unreadCount($manager));

        // Manager-type notifications must.
        $manager->notify(new TaskCompletedNotification($task, 'Employee One'));

        $this->assertSame(1, $feed->unreadCount($manager));
        $this->assertSame(
            TaskCompletedNotification::class,
            $feed->unread($manager)->firstOrFail()->type
        );
    }

    public function test_manager_feed_hides_upcoming_recurring_invoices(): void
    {
        $manager = $this->manager('marketing');
        $admin = $this->admin();

        $customer = Customer::factory()->create(['name' => 'Joshwa Mathew']);

        RecurringInvoiceTemplate::create([
            'name' => 'Recurring Invoice - Joshwa Mathew - Monthly',
            'customer_id' => $customer->id,
            'frequency' => 'monthly',
            'start_date' => now()->subMonth(),
            'next_generation_date' => now()->toDateString(),
            'subtotal' => 30000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $feed = app(NotificationFeed::class);

        $this->assertSame(0, $feed->upcomingCount($manager));
        $this->assertCount(0, $feed->upcomingTemplates($manager));
        $this->assertSame(1, $feed->upcomingCount($admin));
    }

    public function test_a_manager_in_a_department_without_tasks_keeps_the_full_feed(): void
    {
        // Sales does not receive task assignments, so its manager has no
        // task-completion slot in their feed.
        $salesManager = $this->manager('sales');
        $feed = app(NotificationFeed::class);

        $this->assertSame('manager', $feed->tier($salesManager));
        $this->assertSame(
            [LeaveRequestNotification::class, LeaveApprovedNotification::class],
            $feed->allowedClasses($salesManager)
        );
        $this->assertFalse($feed->showsRecurringInvoices($salesManager));
    }

    // ---------------------------------------------------------------------
    // Managers apply for leave too
    // ---------------------------------------------------------------------

    public function test_a_manager_can_apply_for_leave_and_is_told_when_it_is_approved(): void
    {
        $manager = $this->manager('development');
        $hr = $this->hr();
        $feed = app(NotificationFeed::class);

        // A manager applies for leave like anybody else.
        $this->actingAs($manager)->post(route('my-leaves.store'), [
            'leave_type_id' => LeaveType::where('code', 'CL')->firstOrFail()->id,
            'start_date' => '2026-11-09',
            'end_date' => '2026-11-10',
            'reason' => 'Family function',
        ])->assertRedirect(route('my-leaves.index'));

        $leave = LeaveRequest::where('user_id', $manager->id)->firstOrFail();

        // Only HR is asked to approve it.
        $this->assertSame(1, $hr->notifications()->count());
        $this->assertSame(
            LeaveRequestNotification::class,
            $hr->notifications()->firstOrFail()->type
        );

        // HR approves it.
        $this->actingAs($hr)->post(route('admin.leaves.approve', $leave))
            ->assertRedirect();

        // The manager is told.
        $notification = $manager->notifications()
            ->where('type', LeaveApprovedNotification::class)
            ->firstOrFail();

        $this->assertSame('leave_approved', $notification->data['type']);
        $this->assertStringContainsString('HR One', $notification->data['message']);

        // And the manager feed shows it.
        $this->assertSame(1, $feed->unreadCount($manager));
    }

    public function test_a_manager_cannot_approve_another_managers_leave(): void
    {
        $manager = $this->manager('development');
        $peer = $this->manager('development');

        $leave = LeaveRequest::create([
            'user_id' => $peer->id,
            'leave_type_id' => LeaveType::where('code', 'CL')->firstOrFail()->id,
            'start_date' => '2026-11-09',
            'end_date' => '2026-11-10',
            'total_days' => 2,
            'reason' => 'Family function',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)->post(route('admin.leaves.approve', $leave))
            ->assertForbidden();

        $this->assertSame('pending', $leave->fresh()->status);
    }

    public function test_hr_can_approve_a_managers_leave(): void
    {
        $hr = $this->hr();
        $manager = $this->manager('development');

        $leave = LeaveRequest::create([
            'user_id' => $manager->id,
            'leave_type_id' => LeaveType::where('code', 'CL')->firstOrFail()->id,
            'start_date' => '2026-11-09',
            'end_date' => '2026-11-10',
            'total_days' => 2,
            'reason' => 'Family function',
            'status' => 'pending',
        ]);

        $this->actingAs($hr)->post(route('admin.leaves.approve', $leave))
            ->assertRedirect();

        $this->assertSame('approved', $leave->fresh()->status);
    }

    public function test_a_manager_can_still_approve_their_employees_leave(): void
    {
        $manager = $this->manager('development');
        $employee = $this->employee('test_employee_role', $manager, 'development');

        $leave = LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => LeaveType::where('code', 'CL')->firstOrFail()->id,
            'start_date' => '2026-11-09',
            'end_date' => '2026-11-10',
            'total_days' => 2,
            'reason' => 'Family function',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)->post(route('admin.leaves.approve', $leave))
            ->assertRedirect();

        $this->assertSame('approved', $leave->fresh()->status);
    }

    // ---------------------------------------------------------------------
    // HR feed
    // ---------------------------------------------------------------------

    public function test_hr_is_told_about_leave_requests_from_any_department(): void
    {
        $hr = $this->hr();
        $salesEmployee = $this->employee('test_employee_role_sales', null, 'sales');

        $this->actingAs($salesEmployee)->post(route('my-leaves.store'), [
            'leave_type_id' => LeaveType::where('code', 'CL')->firstOrFail()->id,
            'start_date' => '2026-11-09',
            'end_date' => '2026-11-10',
            'reason' => 'Family function',
        ])->assertRedirect(route('my-leaves.index'));

        $notification = $hr->notifications()->firstOrFail();

        $this->assertSame(LeaveRequestNotification::class, $notification->type);
        $this->assertStringContainsString('Employee One', $notification->data['message']);
    }

    public function test_hr_only_receives_leave_request_notifications(): void
    {
        $hr = $this->hr();
        $feed = app(NotificationFeed::class);

        $this->assertSame('hr', $feed->tier($hr));
        $this->assertSame(
            [LeaveRequestNotification::class],
            $feed->allowedClasses($hr)
        );
        $this->assertFalse($feed->showsRecurringInvoices($hr));

        // Anything else in HR's inbox is hidden.
        $this->notifyFollowupReminder($hr);

        $task = Task::create([
            'title' => 'Some task',
            'assigned_to' => $hr->id,
            'assigned_by' => $hr->id,
            'priority' => 'low',
            'status' => 'pending',
        ]);
        $hr->notify(new TaskAssignedNotification($task, 'Manager One'));

        $this->assertSame(2, $hr->notifications()->count());
        $this->assertSame(0, $feed->unreadCount($hr));

        // A leave request does come through.
        $leave = LeaveRequest::create([
            'user_id' => $hr->id,
            'leave_type_id' => LeaveType::where('code', 'CL')->firstOrFail()->id,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-02',
            'total_days' => 1,
            'reason' => 'Family travel',
            'status' => 'pending',
        ]);
        $hr->notify(new LeaveRequestNotification($leave, 'Employee One'));

        $this->assertSame(1, $feed->unreadCount($hr));
        $this->assertSame(
            LeaveRequestNotification::class,
            $feed->unread($hr)->firstOrFail()->type
        );
    }

    public function test_admin_only_receives_follow_up_reminders_and_recurring_invoices(): void    {
        $admin = $this->admin();
        $feed = app(NotificationFeed::class);

        $this->assertSame('admin', $feed->tier($admin));
        $this->assertSame(
            [FollowupReminderNotification::class],
            $feed->allowedClasses($admin)
        );
        $this->assertTrue($feed->showsRecurringInvoices($admin));

        // Follow-up reminders are what the admin does see.
        $this->notifyFollowupReminder($admin);
        $this->assertSame(1, $feed->unreadCount($admin));

        // Everything else is hidden.
        $task = Task::create([
            'title' => 'Some task',
            'assigned_to' => $admin->id,
            'assigned_by' => $admin->id,
            'priority' => 'low',
            'status' => 'pending',
        ]);
        $admin->notify(new TaskAssignedNotification($task, 'Manager One'));
        $admin->notify(new TaskCompletedNotification($task, 'Employee One'));

        $leave = LeaveRequest::create([
            'user_id' => $admin->id,
            'leave_type_id' => LeaveType::where('code', 'CL')->firstOrFail()->id,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-02',
            'total_days' => 1,
            'reason' => 'Family travel',
            'status' => 'pending',
        ]);
        $admin->notify(new LeaveRequestNotification($leave, 'Employee One'));
        $admin->notify(new LeaveApprovedNotification($leave, 'HR One'));

        $this->assertSame(5, $admin->notifications()->count());
        $this->assertSame(1, $feed->unreadCount($admin));
    }
}
