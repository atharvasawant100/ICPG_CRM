<?php

namespace Tests\Feature\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Tests\Feature\Rbac\RbacTestCase;

class ProjectCardAndChecklistTest extends RbacTestCase
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

    public function test_manager_can_create_project_card(): void
    {
        $admin = $this->admin();

        $postData = [
            'title' => 'Alpha Redesign Project',
            'client_name' => 'Acme Corporation',
            'department' => 'development',
            'color' => 'indigo',
            'due_date' => Carbon::now()->addDays(14)->toDateString(),
            'description' => 'Comprehensive redesign of customer portal.',
        ];

        $response = $this->actingAs($admin)->post(route('projects.store'), $postData);
        $response->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('projects', [
            'title' => 'Alpha Redesign Project',
            'client_name' => 'Acme Corporation',
            'department' => 'development',
            'color' => 'indigo',
        ]);
    }

    public function test_multiple_tasks_can_be_assigned_inside_one_project(): void
    {
        $admin = $this->admin();
        $dev1 = $this->employee(['email' => 'dev1@example.com']);
        $dev2 = $this->employee(['email' => 'dev2@example.com']);

        $project = Project::create([
            'title' => 'Website Overhaul',
            'client_name' => 'Tech Corp',
            'created_by' => $admin->id,
            'status' => 'active',
        ]);

        // Assign task 1
        $this->actingAs($admin)->post(route('tasks.store'), [
            'project_id' => $project->id,
            'title' => 'Create Figma Mockups',
            'assigned_to' => $dev1->id,
            'priority' => 'high',
        ]);

        // Assign task 2
        $this->actingAs($admin)->post(route('tasks.store'), [
            'project_id' => $project->id,
            'title' => 'Implement Auth System',
            'assigned_to' => $dev2->id,
            'priority' => 'urgent',
        ]);

        // Both tasks belong to the project
        $this->assertSame(2, $project->fresh()->tasks->count());
        $this->assertSame(0, $project->fresh()->completionPercentage());

        // View tasks index displays the Project Card and both tasks
        $response = $this->actingAs($admin)->get(route('tasks.index'));
        $response->assertSuccessful();
        $response->assertSee('Website Overhaul');
        $response->assertSee('Create Figma Mockups');
        $response->assertSee('Implement Auth System');
    }

    public function test_tick_mark_toggle_endpoint_updates_task_and_project_progress(): void
    {
        $admin = $this->admin();
        $employee = $this->employee();

        $project = Project::create([
            'title' => 'Mobile App v2',
            'created_by' => $admin->id,
        ]);

        $task1 = Task::create([
            'project_id' => $project->id,
            'title' => 'Build Splash Screen',
            'assigned_to' => $employee->id,
            'assigned_by' => $admin->id,
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $task2 = Task::create([
            'project_id' => $project->id,
            'title' => 'Setup Push Notifications',
            'assigned_to' => $employee->id,
            'assigned_by' => $admin->id,
            'priority' => 'high',
            'status' => 'pending',
        ]);

        // Initially 0% completed
        $this->assertSame(0, $project->fresh()->completionPercentage());

        // Employee clicks tick mark on task1 (AJAX toggle)
        $toggleResponse = $this->actingAs($employee)->postJson(route('tasks.toggle', $task1));
        $toggleResponse->assertSuccessful();
        $toggleResponse->assertJson([
            'success' => true,
            'task_id' => $task1->id,
            'is_completed' => true,
            'status' => 'completed',
            'project_id' => $project->id,
            'project_stats' => [
                'total' => 2,
                'completed' => 1,
                'percentage' => 50,
            ],
        ]);

        $this->assertSame('completed', $task1->fresh()->status);
        $this->assertNotNull($task1->fresh()->completed_at);
        $this->assertSame(50, $project->fresh()->completionPercentage());

        // Click tick mark again on task1 to untick / reopen
        $untoggleResponse = $this->actingAs($employee)->postJson(route('tasks.toggle', $task1));
        $untoggleResponse->assertSuccessful();
        $untoggleResponse->assertJson([
            'success' => true,
            'task_id' => $task1->id,
            'is_completed' => false,
            'status' => 'pending',
            'project_stats' => [
                'total' => 2,
                'completed' => 0,
                'percentage' => 0,
            ],
        ]);

        $this->assertSame('pending', $task1->fresh()->status);
        $this->assertNull($task1->fresh()->completed_at);
    }

    public function test_manager_can_assign_a_self_task(): void
    {
        $manager = User::factory()->create([
            'name' => 'Sara Manager',
            'email' => 'sara.manager@example.com',
            'is_active' => true,
            'department' => 'development',
            'designation' => 'Lead Manager',
        ]);
        $manager->assignRbacRole('manager');

        $subordinate = $this->employee([
            'name' => 'Bob Dev',
            'manager_id' => $manager->id,
        ]);

        $project = Project::create([
            'title' => 'Core Architecture',
            'created_by' => $manager->id,
            'department' => 'development',
        ]);

        // Manager sees "Assign to Myself" option on the tasks page
        $response = $this->actingAs($manager)->get(route('tasks.index'));
        $response->assertSuccessful();
        $response->assertSee('Assign to Myself');
        $response->assertSee('Sara Manager');
        $response->assertSee('Bob Dev');

        // Manager assigns a task to themselves
        $postResponse = $this->actingAs($manager)->post(route('tasks.store'), [
            'project_id' => $project->id,
            'title' => 'Review System Architecture',
            'description' => 'Personal evaluation of microservices migration plan.',
            'assigned_to' => $manager->id,
            'priority' => 'urgent',
            'due_date' => Carbon::now()->addDays(3)->toDateString(),
        ]);

        $postResponse->assertRedirect(route('tasks.index'));
        $postResponse->assertSessionHas('success');

        // Task created in database with assigned_to = manager and assigned_by = manager
        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Review System Architecture',
            'assigned_to' => $manager->id,
            'assigned_by' => $manager->id,
            'priority' => 'urgent',
            'status' => 'pending',
        ]);

        $task = Task::where('title', 'Review System Architecture')->first();
        $this->assertNotNull($task);

        // Self-assigned task shows up in manager's "My Tasks" view
        $myTasksResponse = $this->actingAs($manager)->get(route('tasks.index', ['tab' => 'my']));
        $myTasksResponse->assertSuccessful();
        $myTasksResponse->assertSee('Review System Architecture');

        // Manager can toggle the tick mark on their self-assigned task
        $toggleResponse = $this->actingAs($manager)->postJson(route('tasks.toggle', $task));
        $toggleResponse->assertSuccessful();
        $toggleResponse->assertJson([
            'success' => true,
            'task_id' => $task->id,
            'is_completed' => true,
            'status' => 'completed',
        ]);

        $this->assertSame('completed', $task->fresh()->status);
    }

    public function test_project_show_page_displays_project_details_and_tasks_checklist(): void
    {
        $admin = $this->admin();
        $dev = $this->employee(['email' => 'dev_test@example.com']);

        $project = Project::create([
            'title' => 'Client Portal Redesign',
            'client_name' => 'Alice Johnson (POC)',
            'department' => 'development',
            'created_by' => $admin->id,
            'status' => 'active',
            'color' => 'indigo',
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Build Dashboard Widgets',
            'assigned_to' => $dev->id,
            'assigned_by' => $admin->id,
            'priority' => 'high',
            'status' => 'pending',
        ]);

        // When viewing tasks index, View Tasks links directly to projects.show
        $indexResponse = $this->actingAs($admin)->get(route('tasks.index'));
        $indexResponse->assertSuccessful();
        $indexResponse->assertSee(route('projects.show', $project));
        $indexResponse->assertSee('POC:');
        $indexResponse->assertSee('Alice Johnson (POC)');

        $overdueTask = Task::create([
            'project_id' => $project->id,
            'title' => 'Overdue Task Fix',
            'assigned_to' => $dev->id,
            'assigned_by' => $admin->id,
            'priority' => 'urgent',
            'status' => 'pending',
            'due_date' => Carbon::now()->subDays(2)->toDateString(),
        ]);

        $this->assertTrue($overdueTask->isOverdue());

        // Visiting the dedicated project page loads project details and tasks checklist
        $showResponse = $this->actingAs($admin)->get(route('projects.show', $project));
        $showResponse->assertSuccessful();
        $showResponse->assertSee('Client Portal Redesign');
        $showResponse->assertSee('POC:');
        $showResponse->assertSee('Alice Johnson (POC)');
        $showResponse->assertSee('Build Dashboard Widgets');
        $showResponse->assertSee('Overdue Task Fix');
        $showResponse->assertSee('Work Items');
    }

    public function test_poc_label_is_used_in_create_project_modal(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('tasks.index'));
        $response->assertSuccessful();
        $response->assertSee('Create New Project Card');
        $response->assertSee('<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">POC</label>', false);
    }
}

