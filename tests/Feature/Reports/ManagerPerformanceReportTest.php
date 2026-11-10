<?php

namespace Tests\Feature\Reports;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Tests\Feature\Rbac\RbacTestCase;

class ManagerPerformanceReportTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    private function manager(string $department = 'marketing'): User
    {
        $roleName = $department . '_manager';
        $user = User::factory()->create([
            'is_active' => true,
            'department' => $department,
            'designation' => ucfirst($department) . ' Manager',
        ]);
        $user->assignRbacRole($roleName);

        return $user->fresh();
    }

    private function employee(): User
    {
        $user = User::factory()->create([
            'is_active' => true,
            'department' => 'development',
            'designation' => 'Junior Developer',
        ]);
        $user->assignRbacRole('development');

        return $user->fresh();
    }

    public function test_admin_can_access_all_managers_performance_report(): void
    {
        $admin = $this->admin();
        $marketingManager = $this->manager('marketing');
        $devManager = $this->manager('development');

        $response = $this->actingAs($admin)->get(route('reports.performance', ['manager_id' => 'all']));
        $response->assertSuccessful();
        $response->assertSee('Manager Performance Report');
        $response->assertSee('Executive Scorecard');
        $response->assertSee($marketingManager->name);
        $response->assertSee($devManager->name);
    }

    public function test_admin_can_view_specific_manager_report(): void
    {
        $admin = $this->admin();
        $marketingManager = $this->manager('marketing');

        $task = Task::create([
            'title' => 'Q4 Marketing Strategy Deliverable',
            'assigned_to' => $marketingManager->id,
            'assigned_by' => $admin->id,
            'priority' => 'high',
            'status' => 'completed',
            'due_date' => Carbon::today()->addDays(2),
            'completed_at' => Carbon::today(),
        ]);

        $response = $this->actingAs($admin)->get(route('reports.performance', [
            'manager_id' => $marketingManager->id,
            'month' => Carbon::now()->month,
            'year' => Carbon::now()->year,
        ]));

        $response->assertSuccessful();
        $response->assertSee($marketingManager->name);
        $response->assertSee('Q4 Marketing Strategy Deliverable');
        $response->assertSee('100%'); // 1 completed of 1
        $response->assertSee('On Time');
    }

    public function test_marketing_manager_can_view_own_performance_report(): void
    {
        $marketingManager = $this->manager('marketing');
        $otherManager = $this->manager('development');

        $response = $this->actingAs($marketingManager)->get(route('reports.performance'));
        $response->assertSuccessful();
        $response->assertSee($marketingManager->name);
        $response->assertSee('Performance Report');
    }

    public function test_manager_cannot_view_another_managers_report(): void
    {
        $marketingManager = $this->manager('marketing');
        $devManager = $this->manager('development');

        // Trying to pass devManager id should still only show marketingManager report
        $response = $this->actingAs($marketingManager)->get(route('reports.performance', [
            'manager_id' => $devManager->id,
        ]));

        $response->assertSuccessful();
        $response->assertSee($marketingManager->name);
        $response->assertDontSee($devManager->email);
    }

    public function test_regular_employee_cannot_access_performance_report(): void
    {
        $employee = $this->employee();

        $response = $this->actingAs($employee)->get(route('reports.performance'));
        $response->assertForbidden();
    }

    public function test_performance_metrics_calculation(): void
    {
        $admin = $this->admin();
        $manager = $this->manager('marketing');
        $now = Carbon::now();

        // Task 1: Completed On-Time
        Task::create([
            'title' => 'On-Time Task',
            'assigned_to' => $manager->id,
            'assigned_by' => $admin->id,
            'priority' => 'urgent',
            'status' => 'completed',
            'due_date' => $now->copy()->addDays(1),
            'completed_at' => $now->copy(),
            'total_seconds_spent' => 3600,
        ]);

        // Task 2: Completed Delayed (after due date)
        Task::create([
            'title' => 'Delayed Task',
            'assigned_to' => $manager->id,
            'assigned_by' => $admin->id,
            'priority' => 'high',
            'status' => 'completed',
            'due_date' => $now->copy()->subDays(3),
            'completed_at' => $now->copy(),
            'total_seconds_spent' => 1800,
        ]);

        // Task 3: In Progress
        Task::create([
            'title' => 'Active In Progress Task',
            'assigned_to' => $manager->id,
            'assigned_by' => $admin->id,
            'priority' => 'medium',
            'status' => 'in_progress',
            'due_date' => $now->copy()->addDays(5),
            'started_at' => $now->copy()->subHours(2),
        ]);

        // Task 4: Pending
        Task::create([
            'title' => 'Pending Task Item',
            'assigned_to' => $manager->id,
            'assigned_by' => $admin->id,
            'priority' => 'low',
            'status' => 'pending',
            'due_date' => $now->copy()->addDays(7),
        ]);

        $response = $this->actingAs($manager)->get(route('reports.performance', [
            'month' => $now->month,
            'year' => $now->year,
        ]));

        $response->assertSuccessful();
        $response->assertSee('On-Time Task');
        $response->assertSee('Delayed Task');
        $response->assertSee('Active In Progress Task');
        $response->assertSee('Pending Task Item');

        // Check metrics: 4 total tasks, 2 completed (50%)
        $response->assertSee('50%'); // Completion Rate
        $response->assertSee('On-Time Delivery');
        $response->assertSee('Delayed');
    }
}

