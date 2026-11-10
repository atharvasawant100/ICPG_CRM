<?php

namespace Tests\Feature\Tasks;

use App\Mail\MissedTasksAlertMail;
use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskOverdueNotification;
use App\Services\TaskOverdueService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Rbac\RbacTestCase;

class TaskOverdueNotificationTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    private function createManager(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'is_active' => true,
            'department' => 'development',
            'designation' => 'Development Manager',
            'email' => 'devmanager@example.com',
        ], $attributes));
        $user->assignRbacRole('manager');

        return $user->fresh();
    }

    private function createEmployee(User $manager, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'is_active' => true,
            'department' => 'development',
            'designation' => 'Developer',
            'manager_id' => $manager->id,
            'email' => 'developer@example.com',
        ], $attributes));
        $user->assignRbacRole('development');

        return $user->fresh();
    }

    public function test_automatic_portal_notification_is_generated_when_task_crosses_deadline(): void
    {
        Notification::fake();
        Mail::fake();

        $manager = $this->createManager();
        $employee = $this->createEmployee($manager);

        // Task with past due date and incomplete
        $task = Task::create([
            'title' => 'Fix Checkout Gateway',
            'assigned_to' => $employee->id,
            'assigned_by' => $manager->id,
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => Carbon::yesterday(),
        ]);

        $service = app(TaskOverdueService::class);
        $result = $service->checkAndDispatchOverdueNotifications();

        $this->assertSame(1, $result['overdue_tasks']);
        $this->assertSame(1, $result['portal_notifications']);
        $this->assertSame(0, $result['emails_sent']);

        Notification::assertSentTo($employee, TaskOverdueNotification::class, function ($notification) use ($task) {
            $data = $notification->toArray($notification);
            return $data['task_id'] === $task->id
                && $data['type'] === 'task_overdue'
                && str_contains($data['message'], 'Fix Checkout Gateway');
        });
    }

    public function test_threshold_email_sent_to_employee_with_manager_cc_after_5_missed_tasks(): void
    {
        Mail::fake();

        $manager = $this->createManager(['email' => 'tanvi@iconicpages.com']);
        $employee = $this->createEmployee($manager, ['email' => 'atharva@iconicpages.com']);

        Department::firstOrCreate(
            ['name' => 'development'],
            ['manager_id' => $manager->id, 'is_active' => true]
        );

        // Create 5 overdue tasks for the employee
        for ($i = 1; $i <= 5; $i++) {
            Task::create([
                'title' => "Overdue Task {$i}",
                'assigned_to' => $employee->id,
                'assigned_by' => $manager->id,
                'status' => 'pending',
                'priority' => 'medium',
                'due_date' => Carbon::today()->subDays($i),
            ]);
        }

        $service = app(TaskOverdueService::class);
        $result = $service->checkAndDispatchOverdueNotifications();

        $this->assertSame(5, $result['overdue_tasks']);
        $this->assertSame(1, $result['emails_sent']);

        Mail::assertSent(MissedTasksAlertMail::class, function (MissedTasksAlertMail $mail) use ($employee, $manager) {
            return $mail->hasTo('atharva@iconicpages.com')
                && $mail->hasCc('tanvi@iconicpages.com')
                && $mail->overdueTasks->count() === 5;
        });
    }
}

