<?php

namespace Tests\Feature\Tasks;

use App\Models\Attendance;
use App\Models\Task;
use App\Models\TaskWorkLog;
use App\Models\User;
use Carbon\Carbon;
use Tests\Feature\Rbac\RbacTestCase;

class TaskPoolModuleTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    private function employee(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'is_active' => true,
            'department' => 'development',
            'designation' => 'Developer',
        ], $attributes));
        $user->assignRbacRole('development');

        return $user->fresh();
    }

    public function test_admin_sees_task_pool_and_can_create_task(): void
    {
        $admin = $this->admin();
        $employee = $this->employee();

        $response = $this->actingAs($admin)->get(route('tasks.index'));
        $response->assertSuccessful();
        $response->assertSee('Task Pool');
        $response->assertSee('Assign New Task');

        // Create Task
        $postData = [
            'title' => 'Implement Payment Gateway Webhook',
            'description' => 'Handle Stripe webhook responses and status callbacks.',
            'assigned_to' => $employee->id,
            'priority' => 'high',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ];

        $createResponse = $this->actingAs($admin)->post(route('tasks.store'), $postData);
        $createResponse->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'title' => 'Implement Payment Gateway Webhook',
            'assigned_to' => $employee->id,
            'assigned_by' => $admin->id,
            'priority' => 'high',
            'status' => 'pending',
            'department' => 'development',
        ]);
    }

    public function test_employee_sees_assigned_tasks_in_my_tasks(): void
    {
        $employee = $this->employee();
        $otherEmployee = $this->employee(['email' => 'other@example.com']);

        // Task for this employee
        $myTask = Task::create([
            'title' => 'My Personal Task',
            'assigned_to' => $employee->id,
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        // Task for other employee
        $otherTask = Task::create([
            'title' => 'Other Colleague Task',
            'assigned_to' => $otherEmployee->id,
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($employee)->get(route('tasks.index'));
        $response->assertSuccessful();
        $response->assertSee('My Personal Task');
        $response->assertDontSee('Other Colleague Task');
    }

    public function test_employee_can_update_task_status(): void
    {
        $employee = $this->employee();
        $task = Task::create([
            'title' => 'Documentation Review',
            'assigned_to' => $employee->id,
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($employee)->patch(route('tasks.status', $task), [
            'status' => 'in_progress',
        ]);

        $response->assertRedirect();
        $this->assertSame('in_progress', $task->fresh()->status);
    }

    public function test_my_active_tasks_endpoint_returns_json_for_punch_out_modal(): void
    {
        $employee = $this->employee();

        $task = Task::create([
            'title' => 'Active Task 1',
            'assigned_to' => $employee->id,
            'priority' => 'urgent',
            'status' => 'in_progress',
            'due_date' => Carbon::today(),
        ]);

        $task = Task::create([
            'title' => 'Completed Task',
            'assigned_to' => $employee->id,
            'priority' => 'low',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($employee)->getJson(route('tasks.my-active'));
        $response->assertSuccessful();
        $response->assertJsonCount(1, 'tasks');
        $response->assertJsonFragment([
            'title' => 'Active Task 1',
            'priority' => 'Urgent',
        ]);
    }

    public function test_punch_out_records_task_work_log_and_updates_status(): void
    {
        $employee = $this->employee();

        // Punch in first
        $attendance = Attendance::create([
            'user_id' => $employee->id,
            'date' => Carbon::today(),
            'check_in' => '09:00:00',
            'status' => 'present',
        ]);

        // Create 2 tasks
        $task1 = Task::create([
            'title' => 'Fix Navbar alignment',
            'assigned_to' => $employee->id,
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $task2 = Task::create([
            'title' => 'Write unit tests',
            'assigned_to' => $employee->id,
            'priority' => 'high',
            'status' => 'in_progress',
        ]);

        // Clock out and mark task1 worked, task2 worked and completed
        $response = $this->actingAs($employee)->post(route('attendance.punch-out'), [
            'worked_task_ids' => [$task1->id, $task2->id],
            'completed_task_ids' => [$task2->id],
            'work_notes' => 'Aligned navigation and wrote 15 unit tests.',
        ]);

        $response->assertRedirect();

        // Verify attendance checked out
        $freshAttendance = $attendance->fresh();
        $this->assertNotNull($freshAttendance->check_out);

        // Verify task1 transitioned from pending -> in_progress
        $this->assertSame('in_progress', $task1->fresh()->status);

        // Verify task2 transitioned to completed
        $this->assertSame('completed', $task2->fresh()->status);
        $this->assertNotNull($task2->fresh()->completed_at);

        // Verify TaskWorkLogs created
        $this->assertDatabaseHas('task_work_logs', [
            'task_id' => $task1->id,
            'user_id' => $employee->id,
            'attendance_id' => $attendance->id,
            'status_after' => 'in_progress',
        ]);

        $this->assertDatabaseHas('task_work_logs', [
            'task_id' => $task2->id,
            'user_id' => $employee->id,
            'attendance_id' => $attendance->id,
            'status_after' => 'completed',
        ]);
    }

    public function test_task_show_view_displays_timeline_of_work_logs(): void
    {
        $employee = $this->employee();
        $task = Task::create([
            'title' => 'Bug investigation in leaves',
            'assigned_to' => $employee->id,
            'priority' => 'urgent',
            'status' => 'in_progress',
        ]);

        TaskWorkLog::create([
            'task_id' => $task->id,
            'user_id' => $employee->id,
            'worked_date' => Carbon::today(),
            'status_after' => 'in_progress',
            'notes' => 'Investigated SQL query issue during shift.',
        ]);

        $response = $this->actingAs($employee)->get(route('tasks.show', $task));
        $response->assertSuccessful();
        $response->assertSee('Bug investigation in leaves');
        $response->assertSee('Shift Punch-Out Work Logs');
        $response->assertSee('Investigated SQL query issue during shift.');
    }

    public function test_task_can_be_created_with_project_name(): void
    {
        $admin = $this->admin();
        $employee = $this->employee();

        $postData = [
            'title' => 'Redesign Landing Page Hero',
            'project_name' => 'IconicPages Website Redesign',
            'description' => 'Create new responsive layout.',
            'assigned_to' => $employee->id,
            'priority' => 'high',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ];

        $response = $this->actingAs($admin)->post(route('tasks.store'), $postData);
        $response->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'title' => 'Redesign Landing Page Hero',
            'project_name' => 'IconicPages Website Redesign',
            'assigned_to' => $employee->id,
        ]);

        $task = Task::where('title', 'Redesign Landing Page Hero')->first();

        // Check show view displays project name
        $showResponse = $this->actingAs($admin)->get(route('tasks.show', $task));
        $showResponse->assertSuccessful();
        $showResponse->assertSee('Project: IconicPages Website Redesign');

        // Check my-active tasks API returns project name
        $apiResponse = $this->actingAs($employee)->getJson(route('tasks.my-active'));
        $apiResponse->assertSuccessful();
        $apiResponse->assertJsonFragment([
            'project_name' => 'IconicPages Website Redesign',
        ]);
    }

    public function test_manager_only_sees_their_team_in_assignable_employees(): void
    {
        // Manager in development
        $manager = User::factory()->create([
            'name' => 'Dev Manager',
            'is_active' => true,
            'department' => 'development',
        ]);
        $manager->assignRbacRole('manager');

        // Employee in manager's department (development)
        $devEmployee = User::factory()->create([
            'name' => 'Alice Dev',
            'is_active' => true,
            'department' => 'development',
            'manager_id' => $manager->id,
        ]);
        $devEmployee->assignRbacRole('development');

        // Employee in other department (marketing)
        $marketingEmployee = User::factory()->create([
            'name' => 'Bob Marketing',
            'is_active' => true,
            'department' => 'marketing',
        ]);
        $marketingEmployee->assignRbacRole('marketing');

        $response = $this->actingAs($manager)->get(route('tasks.index', ['tab' => 'all']));
        $response->assertSuccessful();
        $response->assertSee('Alice Dev');
        $response->assertDontSee('Bob Marketing');

        // Manager cannot assign task to non-team member Bob Marketing
        $invalidPost = [
            'title' => 'Marketing Campaign Task',
            'project_name' => 'SEO Push',
            'assigned_to' => $marketingEmployee->id,
            'priority' => 'medium',
        ];

        $postResponse = $this->actingAs($manager)->post(route('tasks.store'), $invalidPost);
        $postResponse->assertSessionHasErrors('assigned_to');

        // Manager CAN assign task to their team member Alice Dev
        $validPost = [
            'title' => 'Backend API Development',
            'project_name' => 'Core Platform',
            'assigned_to' => $devEmployee->id,
            'priority' => 'high',
        ];

        $validResponse = $this->actingAs($manager)->post(route('tasks.store'), $validPost);
        $validResponse->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'title' => 'Backend API Development',
            'project_name' => 'Core Platform',
            'assigned_to' => $devEmployee->id,
            'assigned_by' => $manager->id,
        ]);
    }

    public function test_assigned_to_column_visible_for_manager_and_hidden_for_employee(): void
    {
        $admin = $this->admin();
        $manager = User::factory()->create([
            'is_active' => true,
            'department' => 'development',
            'designation' => 'Development Manager',
        ]);
        $manager->assignRbacRole('manager');

        $employee = $this->employee();

        $task = Task::create([
            'title' => 'Assigned Task Sample',
            'assigned_to' => $employee->id,
            'assigned_by' => $manager->id,
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        // Admin sees "Assigned To"
        $adminResponse = $this->actingAs($admin)->get(route('tasks.index'));
        $adminResponse->assertSuccessful();
        $adminResponse->assertSee('<th class="py-3.5 px-4">Assigned To</th>', false);

        // Manager sees "Assigned To"
        $managerResponse = $this->actingAs($manager)->get(route('tasks.index'));
        $managerResponse->assertSuccessful();
        $managerResponse->assertSee('<th class="py-3.5 px-4">Assigned To</th>', false);

        // EMP (regular employee) does NOT see "Assigned To" column
        $empResponse = $this->actingAs($employee)->get(route('tasks.index'));
        $empResponse->assertSuccessful();
        $empResponse->assertDontSee('<th class="py-3.5 px-4">Assigned To</th>', false);

        // Project Cards view: Manager sees "Assigned Team:", but regular EMP does NOT
        $project = \App\Models\Project::create([
            'title' => 'Assigned Team Test Project',
            'created_by' => $manager->id,
            'status' => 'active',
        ]);
        $task->update(['project_id' => $project->id]);

        $managerCards = $this->actingAs($manager)->get(route('tasks.index', ['view' => 'cards']));
        $managerCards->assertSuccessful();
        $managerCards->assertSee('Assigned Team:');

        $empCards = $this->actingAs($employee)->get(route('tasks.index', ['view' => 'cards']));
        $empCards->assertSuccessful();
        $empCards->assertDontSee('Assigned Team:');
    }

    public function test_sales_department_is_completely_excluded_from_task_module(): void
    {
        $salesManager = User::factory()->create([
            'department' => 'sales',
            'designation' => 'manager',
        ]);
        $salesManager->assignRbacRole('manager');

        $salesExecutive = User::factory()->create([
            'department' => 'sales',
            'designation' => 'business executive',
        ]);
        $salesExecutive->assignRbacRole('sales');

        // Neither can view tasks index (forbidden)
        $this->actingAs($salesManager)->get(route('tasks.index'))->assertForbidden();
        $this->actingAs($salesExecutive)->get(route('tasks.index'))->assertForbidden();

        // Neither sees Task Pool in navigation
        $this->assertFalse(\App\Support\Navigation::can($salesManager, 'tasks.view'));
        $this->assertFalse(\App\Support\Navigation::can($salesExecutive, 'tasks.view'));

        // my-active endpoint returns empty tasks for Sales
        $response = $this->actingAs($salesExecutive)->get(route('tasks.my-active'));
        $response->assertSuccessful();
        $this->assertSame([], $response->json('tasks'));
    }

    public function test_changing_status_to_in_progress_tracks_time_until_completed(): void
    {
        $employee = $this->employee();

        Carbon::setTestNow(Carbon::parse('2026-10-29 10:00:00'));

        $task = Task::create([
            'title' => 'Build Time Tracker Feature',
            'assigned_to' => $employee->id,
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $this->assertNull($task->started_at);
        $this->assertSame(0, $task->total_seconds_spent);

        // Employee moves status to in_progress at 10:00:00
        $this->actingAs($employee)->patch(route('tasks.status', $task), [
            'status' => 'in_progress',
        ])->assertRedirect();

        $task->refresh();
        $this->assertSame('in_progress', $task->status);
        $this->assertNotNull($task->started_at);
        $this->assertTrue($task->isTimerRunning());

        // Advance clock by 2 hours 15 minutes (8100 seconds)
        Carbon::setTestNow(Carbon::parse('2026-10-29 12:15:00'));
        $this->assertSame(8100, $task->currentSecondsSpent());
        $this->assertSame('2h 15m', $task->formattedTimeSpent());

        // Employee marks task completed at 12:15:00
        $this->actingAs($employee)->patch(route('tasks.status', $task), [
            'status' => 'completed',
        ])->assertRedirect();

        $task->refresh();
        $this->assertSame('completed', $task->status);
        $this->assertNull($task->started_at);
        $this->assertFalse($task->isTimerRunning());
        $this->assertSame(8100, $task->total_seconds_spent);
        $this->assertSame('2h 15m', $task->formattedTimeSpent());

        Carbon::setTestNow();
    }

    public function test_kanban_board_view_groups_tasks_by_status(): void
    {
        $employee = $this->employee();

        $task = Task::create([
            'title' => 'Kanban Pending Item',
            'assigned_to' => $employee->id,
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $task = Task::create([
            'title' => 'Kanban In Progress Item',
            'assigned_to' => $employee->id,
            'priority' => 'high',
            'status' => 'in_progress',
        ]);

        $task = Task::create([
            'title' => 'Kanban Completed Item',
            'assigned_to' => $employee->id,
            'priority' => 'medium',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($employee)->get(route('tasks.index', ['view' => 'kanban']));
        $response->assertSuccessful();
        $response->assertSee('Kanban Board');
        $response->assertSee('Awaiting start');
        $response->assertSee('Active timer running');
        $response->assertSee('Delivered & verified');
        $response->assertSee('Kanban Pending Item');
        $response->assertSee('Kanban In Progress Item');
        $response->assertSee('Kanban Completed Item');
    }

    public function test_overdue_badges_and_overdue_filter_tab(): void
    {
        $employee = $this->employee();

        $overdueTask = Task::create([
            'title' => 'Past Deadline Deliverable',
            'assigned_to' => $employee->id,
            'priority' => 'urgent',
            'status' => 'in_progress',
            'due_date' => Carbon::today()->subDays(3),
        ]);

        $futureTask = Task::create([
            'title' => 'Upcoming Deliverable',
            'assigned_to' => $employee->id,
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => Carbon::today()->addDays(5),
        ]);

        $this->assertTrue($overdueTask->isOverdue());
        $this->assertSame(3, $overdueTask->overdueDays());
        $this->assertSame('Overdue by 3 days', $overdueTask->overdueBadgeLabel());
        $this->assertFalse($futureTask->isOverdue());

        // Overdue tab shows only overdue tasks
        $response = $this->actingAs($employee)->get(route('tasks.index', ['tab' => 'overdue']));
        $response->assertSuccessful();
        $response->assertSee('Past Deadline Deliverable');
        $response->assertSee('Overdue by 3 days');
        $response->assertDontSee('Upcoming Deliverable');
    }

    public function test_task_show_page_includes_edit_task_modal_and_time_tracked(): void
    {
        $admin = $this->admin();
        $employee = $this->employee();

        $task = Task::create([
            'title' => 'Editable Show Page Task',
            'assigned_to' => $employee->id,
            'assigned_by' => $admin->id,
            'priority' => 'high',
            'status' => 'in_progress',
            'total_seconds_spent' => 3600,
        ]);

        $response = $this->actingAs($admin)->get(route('tasks.show', $task));
        $response->assertSuccessful();
        $response->assertSee('Edit Task');
        $response->assertSee('id="edit-task-modal"', false);
        $response->assertSee('Time Tracked');
    }

    public function test_today_assigned_tab_displays_fresh_tasks_created_today_and_orders_newest_first(): void
    {
        $admin = $this->admin();
        $employee = $this->employee();

        // Old task from 3 days ago (forceCreate to bypass $fillable for timestamps)
        $oldTask = Task::forceCreate([
            'title' => 'Old Legacy Task From Past',
            'assigned_to' => $employee->id,
            'assigned_by' => $admin->id,
            'priority' => 'low',
            'status' => 'pending',
            'created_at' => Carbon::today()->subDays(3),
            'updated_at' => Carbon::today()->subDays(3),
        ]);

        // Fresh Task 1 created today
        $freshTask1 = Task::forceCreate([
            'title' => 'Fresh Morning Assignment',
            'assigned_to' => $employee->id,
            'assigned_by' => $admin->id,
            'priority' => 'medium',
            'status' => 'pending',
            'created_at' => Carbon::today()->addHours(2),
            'updated_at' => Carbon::today()->addHours(2),
        ]);

        // Fresh Task 2 created today (later)
        $freshTask2 = Task::forceCreate([
            'title' => 'Fresh Latest Assignment',
            'assigned_to' => $employee->id,
            'assigned_by' => $admin->id,
            'priority' => 'high',
            'status' => 'pending',
            'created_at' => Carbon::today()->addHours(5),
            'updated_at' => Carbon::today()->addHours(5),
        ]);

        $response = $this->actingAs($admin)->get(route('tasks.index', ['tab' => 'today']));
        $response->assertSuccessful();
        $response->assertSee('Today Assigned');
        $response->assertSee('Fresh Latest Assignment');
        $response->assertSee('Fresh Morning Assignment');
        $response->assertDontSee('Old Legacy Task From Past');

        // Order check: freshTask2 appears before freshTask1 in HTML
        $content = $response->getContent();
        $posTask2 = strpos($content, 'Fresh Latest Assignment');
        $posTask1 = strpos($content, 'Fresh Morning Assignment');
        $this->assertNotFalse($posTask2);
        $this->assertNotFalse($posTask1);
        $this->assertTrue($posTask2 < $posTask1, 'Latest fresh task must appear first (#1 in sequence)');
    }

    public function test_user_can_switch_between_cards_and_list_views(): void
    {
        $employee = $this->employee();

        $task = Task::create([
            'title' => 'Sample View Task Item',
            'assigned_to' => $employee->id,
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        // Default / list view shows table register and not cards checklist header
        $listResponse = $this->actingAs($employee)->get(route('tasks.index', ['view' => 'list']));
        $listResponse->assertSuccessful();
        $listResponse->assertSee('All Tasks Register');
        $listResponse->assertSee('Sample View Task Item');
        $listResponse->assertDontSee('Projects & Work Checklists');

        // Cards view shows project/cards checklists and not the list table register
        $cardsResponse = $this->actingAs($employee)->get(route('tasks.index', ['view' => 'cards']));
        $cardsResponse->assertSuccessful();
        $cardsResponse->assertSee('Projects & Work Checklists');
        $cardsResponse->assertSee('Sample View Task Item');
        $cardsResponse->assertDontSee('All Tasks Register');
    }

    public function test_task_pool_pagination_and_custom_per_page(): void
    {
        $employee = $this->employee();

        for ($i = 1; $i <= 25; $i++) {
            Task::create([
                'title' => "Batch Task Number {$i}",
                'assigned_to' => $employee->id,
                'priority' => 'medium',
                'status' => 'pending',
            ]);
        }

        // Page 1 with default 10 per page
        $resPage1 = $this->actingAs($employee)->get(route('tasks.index', ['view' => 'list']));
        $resPage1->assertSuccessful();
        $resPage1->assertSee('Showing');
        $resPage1->assertSee('25');
        $resPage1->assertSee('Per page:');
        $resPage1->assertSee('Next');

        // Page 2
        $resPage2 = $this->actingAs($employee)->get(route('tasks.index', ['view' => 'list', 'page' => 2]));
        $resPage2->assertSuccessful();
        $resPage2->assertSee('Prev');

        // Custom per_page = 25
        $resAll = $this->actingAs($employee)->get(route('tasks.index', ['view' => 'list', 'per_page' => 25]));
        $resAll->assertSuccessful();
        $resAll->assertSee('25');
    }

    public function test_project_tasks_pagination(): void
    {
        $admin = $this->admin();
        $project = \App\Models\Project::create([
            'title' => 'Large Multi-task Project',
            'created_by' => $admin->id,
            'department' => 'development',
        ]);

        for ($i = 1; $i <= 15; $i++) {
            Task::create([
                'title' => "Project Subtask {$i}",
                'project_id' => $project->id,
                'assigned_to' => $admin->id,
                'priority' => 'low',
                'status' => 'pending',
            ]);
        }

        $res = $this->actingAs($admin)->get(route('projects.show', $project));
        $res->assertSuccessful();
        $res->assertSee('Showing');
        $res->assertSee('15');
        $res->assertSee('Next');
    }
}
