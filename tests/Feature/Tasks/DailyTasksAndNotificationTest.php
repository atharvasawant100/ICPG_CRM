<?php

namespace Tests\Feature\Tasks;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskCompletedNotification;
use App\Services\AttendancePunchService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Rbac\RbacTestCase;

class DailyTasksAndNotificationTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    private function manager(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'is_active' => true,
            'department' => 'development',
            'designation' => 'Engineering Manager',
        ], $attributes));
        $user->assignRbacRole('manager');
        $user->givePermissionTo(['dashboard.view', 'team.view', 'tasks.view']);

        return $user->fresh();
    }

    private function employee(User $manager, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'is_active' => true,
            'department' => 'development',
            'designation' => 'Software Engineer',
            'manager_id' => $manager->id,
        ], $attributes));
        $user->assignRbacRole('development');
        $user->givePermissionTo(['tasks.view', 'tasks.edit']);

        return $user->fresh();
    }

    public function test_manager_gets_notification_when_employee_ticks_task_completed(): void
    {
        Notification::fake();

        $manager = $this->manager();
        $employee = $this->employee($manager);

        // Also assign manager to the department
        Department::firstOrCreate(
            ['name' => 'development'],
            ['manager_id' => $manager->id]
        );

        $task = Task::create([
            'title' => 'Build Authentication Flow',
            'assigned_to' => $employee->id,
            'assigned_by' => $manager->id,
            'status' => 'pending',
            'priority' => 'high',
        ]);

        // Employee toggles task completed via toggle endpoint
        $response = $this->actingAs($employee)->postJson(route('tasks.toggle', $task));
        $response->assertSuccessful();

        $task->refresh();
        $this->assertTrue($task->isCompleted());
        $this->assertNotNull($task->completed_at);

        // Manager must receive TaskCompletedNotification
        Notification::assertSentTo(
            $manager,
            TaskCompletedNotification::class,
            function ($notification) use ($task) {
                return $notification->toArray($this->manager())['task_id'] === $task->id;
            }
        );
    }

    public function test_manager_gets_notification_when_employee_completes_task_during_punch_out(): void
    {
        Notification::fake();

        $manager = $this->manager(['email' => 'm2@example.com']);
        $employee = $this->employee($manager, ['email' => 'e2@example.com']);

        $task = Task::create([
            'title' => 'Optimize API Query Execution',
            'assigned_to' => $employee->id,
            'assigned_by' => $manager->id,
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        // Employee punches in first
        $punchService = app(AttendancePunchService::class);
        $punchService->punchIn($employee);

        // Employee punches out and ticks task as completed
        $punchService->punchOut(
            $employee,
            'Evening shift punch out',
            [$task->id],
            [$task->id],
            'Wrapped up index query optimization'
        );

        $task->refresh();
        $this->assertSame('completed', $task->status);
        $this->assertNotNull($task->completed_at);

        Notification::assertSentTo(
            $manager,
            TaskCompletedNotification::class,
            function ($notification) use ($task) {
                return $notification->toArray($this->manager())['task_id'] === $task->id;
            }
        );
    }

    public function test_manager_sees_daily_tasks_done_today_in_daily_tab(): void
    {
        $manager = $this->manager(['email' => 'm3@example.com']);
        $employee = $this->employee($manager, ['email' => 'e3@example.com']);

        // Task completed today
        $todayTask = Task::create([
            'title' => 'Today Completed Deliverable',
            'assigned_to' => $employee->id,
            'assigned_by' => $manager->id,
            'status' => 'completed',
            'completed_at' => Carbon::today()->addHours(14),
            'priority' => 'high',
        ]);

        // Manager views Daily Tasks Done tab
        $response = $this->actingAs($manager)->get(route('tasks.index', ['tab' => 'daily']));
        $response->assertSuccessful();
        $response->assertSee('Daily Tasks Completed by Your Team');
        $response->assertSee('Today Completed Deliverable');
        $response->assertSee($employee->name);
    }

    public function test_daily_task_done_vanishes_the_next_day(): void
    {
        $manager = $this->manager(['email' => 'm4@example.com']);
        $employee = $this->employee($manager, ['email' => 'e4@example.com']);

        // Task completed yesterday
        $yesterdayTask = Task::create([
            'title' => 'Yesterday Finished Task',
            'assigned_to' => $employee->id,
            'assigned_by' => $manager->id,
            'status' => 'completed',
            'completed_at' => Carbon::yesterday()->addHours(17),
            'priority' => 'high',
        ]);

        // Task completed today
        $todayTask = Task::create([
            'title' => 'Fresh Today Task',
            'assigned_to' => $employee->id,
            'assigned_by' => $manager->id,
            'status' => 'completed',
            'completed_at' => Carbon::today()->addHours(11),
            'priority' => 'medium',
        ]);

        // When viewing today's Daily Task section, yesterday's task must have vanished
        $response = $this->actingAs($manager)->get(route('tasks.index', ['tab' => 'daily']));
        $response->assertSuccessful();
        $response->assertSee('Fresh Today Task');
        $response->assertDontSee('Yesterday Finished Task');
    }

    public function test_dashboard_and_team_views_display_daily_tasks_done(): void
    {
        $manager = $this->manager(['email' => 'm5@example.com']);
        $employee = $this->employee($manager, ['email' => 'e5@example.com']);

        Task::create([
            'title' => 'Payment Webhook Integration',
            'assigned_to' => $employee->id,
            'assigned_by' => $manager->id,
            'status' => 'completed',
            'completed_at' => Carbon::today()->addHours(15),
            'priority' => 'urgent',
        ]);

        // Dashboard shows Daily Tasks Done (Today)
        $dashResponse = $this->actingAs($manager)->get(route('dashboard'));
        $dashResponse->assertSuccessful();
        $dashResponse->assertSee('Daily Tasks Done (Today)');
        $dashResponse->assertSee('Payment Webhook Integration');

        // Team index shows Daily Tasks Done button and member completed badge
        $teamResponse = $this->actingAs($manager)->get(route('team.index'));
        $teamResponse->assertSuccessful();
        $teamResponse->assertSee('Daily Tasks Done (1)');
        $teamResponse->assertSee('1 task done today');
    }
}
