<?php

namespace Tests\Feature\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskCollaborator;
use App\Models\TaskComment;
use App\Models\TaskHelpRequest;
use App\Models\TaskReviewAction;
use App\Models\User;
use App\Notifications\TaskCollaboratorNotification;
use App\Notifications\TaskCommentNotification;
use App\Notifications\TaskEscalatedNotification;
use App\Notifications\TaskHelpRequestedNotification;
use App\Notifications\TaskHelpRespondedNotification;
use App\Notifications\TaskReviewDecisionNotification;
use App\Notifications\TaskReviewSubmittedNotification;
use App\Notifications\TaskReworkNotification;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Rbac\RbacTestCase;

class GeneralizedTaskManagementTest extends RbacTestCase
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
            'designation' => 'Software Engineer',
        ], $attributes));

        $user->assignRole('development');
        return $user;
    }

    private function manager(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'is_active' => true,
            'department' => 'development',
            'designation' => 'Development Manager',
        ], $attributes));

        $user->assignRole('manager');
        return $user;
    }

    public function test_employee_can_create_personal_task_and_becomes_creator_and_default_assignee(): void
    {
        $emp = $this->employee(['name' => 'Alice Dev']);

        $response = $this->actingAs($emp)->post(route('tasks.store'), [
            'title' => 'Build login refactor',
            'description' => 'Clean up auth tokens and session handling',
            'expected_outcomes' => 'All tokens refreshed securely',
            'priority' => 'high',
            'start_date' => Carbon::today()->toDateString(),
            'due_date' => Carbon::tomorrow()->toDateString(),
            'review_required' => 1,
        ]);

        $task = Task::where('title', 'Build login refactor')->first();
        $this->assertNotNull($task);
        $response->assertRedirect(route('tasks.index'));

        $this->assertEquals($emp->id, $task->assigned_by);
        $this->assertEquals($emp->id, $task->assigned_to);
        $this->assertEquals('open', $task->status);
        $this->assertEquals('high', $task->priority);
        $this->assertTrue((bool) $task->review_required);

        // Check assignment history was recorded
        $this->assertDatabaseHas('task_assignments', [
            'task_id' => $task->id,
            'assigned_by' => $emp->id,
            'assigned_to' => $emp->id,
        ]);

        // Check activity log was recorded
        $this->assertDatabaseHas('task_activity_logs', [
            'task_id' => $task->id,
            'user_id' => $emp->id,
            'action' => 'created',
        ]);
    }

    public function test_employee_can_view_my_tasks_and_created_by_me(): void
    {
        $alice = $this->employee(['name' => 'Alice']);
        $bob = $this->employee(['name' => 'Bob']);

        // Task 1: Alice self task
        $project = \App\Models\Project::create([
            'title' => 'Core Backend Engine',
            'department' => 'development',
        ]);

        $task1 = Task::create([
            'title' => 'Alice Self Task',
            'assigned_by' => $alice->id,
            'assigned_to' => $alice->id,
            'priority' => 'medium',
            'status' => 'open',
            'project_id' => $project->id,
        ]);

        // Task 2: Alice created, assigned to Bob
        $task2 = Task::create([
            'title' => 'Delegated to Bob',
            'assigned_by' => $alice->id,
            'assigned_to' => $bob->id,
            'priority' => 'critical',
            'status' => 'in_progress',
        ]);

        // My Tasks for Alice should show task1, but not task2
        $myTasksRes = $this->actingAs($alice)->get(route('tasks.index', ['tab' => 'my']));
        $myTasksRes->assertOk();
        $myTasksRes->assertSee('Alice Self Task');
        $myTasksRes->assertSee('Core Backend Engine');
        $myTasksRes->assertDontSee('Delegated to Bob');

        // Show page for Alice task with project
        $showRes = $this->actingAs($alice)->get(route('tasks.show', $task1));
        $showRes->assertOk();
        $showRes->assertSee('Core Backend Engine');

        // Created By Me for Alice should show both task1 and task2
        $createdRes = $this->actingAs($alice)->get(route('tasks.index', ['tab' => 'created']));
        $createdRes->assertOk();
        $createdRes->assertSee('Alice Self Task');
        $createdRes->assertSee('Delegated to Bob');

        // Search by numeric Task ID (e.g. 1)
        $searchIdRes = $this->actingAs($alice)->get(route('tasks.index', ['tab' => 'created', 'search' => (string) $task1->id]));
        $searchIdRes->assertOk();
        $searchIdRes->assertSee('Alice Self Task');
        $searchIdRes->assertDontSee('Delegated to Bob');

        // Search by formatted Task ID (e.g. TSK-00001)
        $searchFormattedRes = $this->actingAs($alice)->get(route('tasks.index', ['tab' => 'created', 'search' => $task1->formatted_id]));
        $searchFormattedRes->assertOk();
        $searchFormattedRes->assertSee('Alice Self Task');
        $searchFormattedRes->assertDontSee('Delegated to Bob');
    }

    public function test_employee_can_delegate_task_to_eligible_colleague_preserving_history(): void
    {
        Notification::fake();

        $alice = $this->employee(['name' => 'Alice Delegator']);
        $bob = $this->employee(['name' => 'Bob Assignee']);

        $task = Task::create([
            'title' => 'Database Optimization',
            'assigned_by' => $alice->id,
            'assigned_to' => $alice->id,
            'priority' => 'high',
            'status' => 'open',
        ]);

        $response = $this->actingAs($alice)->post(route('tasks.delegate', $task), [
            'assigned_to' => $bob->id,
            'notes' => 'Please analyze index scans for queries.',
        ]);

        $response->assertRedirect(route('tasks.show', $task));

        $task->refresh();
        // Exact single accountable owner
        $this->assertEquals($bob->id, $task->assigned_to);
        $this->assertEquals($alice->id, $task->assigned_by);

        // Verify assignment history has 1 record for this delegation
        $this->assertDatabaseHas('task_assignments', [
            'task_id' => $task->id,
            'assigned_by' => $alice->id,
            'assigned_to' => $bob->id,
            'notes' => 'Please analyze index scans for queries.',
        ]);

        // Verify activity log
        $this->assertDatabaseHas('task_activity_logs', [
            'task_id' => $task->id,
            'user_id' => $alice->id,
            'action' => 'delegated',
        ]);
    }

    public function test_employee_cannot_assign_or_delegate_task_to_manager(): void
    {
        $manager = $this->manager(['name' => 'Boss Manager']);
        $employee = $this->employee([
            'name' => 'Junior Dev',
            'manager_id' => $manager->id,
        ]);

        // 1. In eligible delegates list, the manager must NOT appear for this employee
        $taskService = app(\App\Services\TaskService::class);
        $delegates = $taskService->eligibleDelegates($employee);
        $this->assertFalse($delegates->contains('id', $manager->id));

        // 2. Employee cannot create a task assigned directly to manager
        $createRes = $this->actingAs($employee)->post(route('tasks.store'), [
            'title' => 'Assigned to Manager Test',
            'assigned_to' => $manager->id,
            'priority' => 'high',
        ]);
        $createRes->assertSessionHasErrors('assigned_to');
        $this->assertDatabaseMissing('tasks', ['title' => 'Assigned to Manager Test']);

        // 3. Employee cannot delegate an existing task to manager
        $selfTask = Task::create([
            'title' => 'Employee Self Task',
            'assigned_by' => $employee->id,
            'assigned_to' => $employee->id,
            'priority' => 'medium',
            'status' => 'open',
        ]);

        $delegateRes = $this->actingAs($employee)->post(route('tasks.delegate', $selfTask), [
            'assigned_to' => $manager->id,
            'notes' => 'Handing over to boss',
        ]);
        $delegateRes->assertSessionHasErrors('assigned_to');
        $this->assertEquals($employee->id, $selfTask->fresh()->assigned_to);

        // 4. In contrast, Manager CAN assign task to anyone
        $managerCreateRes = $this->actingAs($manager)->post(route('tasks.store'), [
            'title' => 'Manager Assigned Task',
            'assigned_to' => $employee->id,
            'priority' => 'high',
        ]);
        $managerCreateRes->assertRedirect();
        $this->assertDatabaseHas('tasks', ['title' => 'Manager Assigned Task', 'assigned_to' => $employee->id]);
    }

    public function test_status_management_and_automatic_overdue_calculation(): void
    {
        $emp = $this->employee();

        $task = Task::create([
            'title' => 'Server Migration',
            'assigned_by' => $emp->id,
            'assigned_to' => $emp->id,
            'priority' => 'critical',
            'status' => 'open',
            'due_date' => Carbon::yesterday(),
        ]);

        // Automatic overdue: status is open, due_date is in the past
        $this->assertTrue($task->isOverdue());
        $this->assertEquals(1, $task->overdueDays());

        // Update status to in_progress
        $res = $this->actingAs($emp)->patch(route('tasks.status', $task), [
            'status' => 'in_progress',
            'progress' => 40,
        ]);
        $res->assertRedirect();

        $task->refresh();
        $this->assertEquals('in_progress', $task->status);
        $this->assertEquals(40, $task->progress);
        $this->assertTrue($task->isOverdue()); // still overdue

        // Update status to completed -> should no longer be overdue
        $this->actingAs($emp)->patch(route('tasks.status', $task), [
            'status' => 'completed',
        ]);

        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertEquals(100, $task->progress);
        $this->assertFalse($task->isOverdue());
    }

    public function test_unauthorized_user_cannot_access_or_edit_unrelated_tasks(): void
    {
        $alice = $this->employee(['department' => 'development']);
        $charlie = $this->employee(['department' => 'marketing']);

        $task = Task::create([
            'title' => 'Secret Marketing Plan',
            'department' => 'marketing',
            'assigned_by' => $charlie->id,
            'assigned_to' => $charlie->id,
            'status' => 'open',
        ]);

        // Alice (unrelated user) tries to view task
        $res = $this->actingAs($alice)->get(route('tasks.show', $task));
        $res->assertForbidden();

        // Alice tries to update status
        $resUpdate = $this->actingAs($alice)->patch(route('tasks.status', $task), [
            'status' => 'completed',
        ]);
        $resUpdate->assertForbidden();
    }

    public function test_task_help_requests_flow_and_owner_preservation(): void
    {
        Notification::fake();

        $owner = $this->employee(['name' => 'Owner Alice']);
        $helper = $this->employee(['name' => 'Helper Bob']);

        $task = Task::create([
            'title' => 'Complex Algorithm Fix',
            'assigned_by' => $owner->id,
            'assigned_to' => $owner->id,
            'status' => 'in_progress',
        ]);

        // 1. Owner requests help
        $response = $this->actingAs($owner)->post(route('tasks.help-requests.store', $task), [
            'helper_id' => $helper->id,
            'message' => 'Need help profiling memory leak in worker.',
        ]);
        $response->assertRedirect(route('tasks.show', $task));

        $helpRequest = TaskHelpRequest::where('task_id', $task->id)->where('helper_id', $helper->id)->first();
        $this->assertNotNull($helpRequest);
        $this->assertEquals('pending', $helpRequest->status);

        // Verification: Help request NEVER changes the accountable owner
        $task->refresh();
        $this->assertEquals($owner->id, $task->assigned_to);

        Notification::assertSentTo($helper, TaskHelpRequestedNotification::class);

        // 2. Helper accepts help request
        $respondRes = $this->actingAs($helper)->post(route('tasks.help-requests.respond', [$task, $helpRequest]), [
            'status' => 'accepted',
            'response_notes' => 'Happy to help! Let us pair program today.',
        ]);
        $respondRes->assertRedirect(route('tasks.show', $task));

        $helpRequest->refresh();
        $this->assertEquals('accepted', $helpRequest->status);
        $this->assertEquals('Happy to help! Let us pair program today.', $helpRequest->response_notes);

        // Owner still unchanged!
        $task->refresh();
        $this->assertEquals($owner->id, $task->assigned_to);

        Notification::assertSentTo($owner, TaskHelpRespondedNotification::class);
    }

    public function test_collaborators_can_be_added_and_removed_with_visibility_and_notifications(): void
    {
        Notification::fake();

        $owner = $this->employee(['name' => 'Owner']);
        $collaborator = $this->employee(['name' => 'Collaborator']);

        $task = Task::create([
            'title' => 'API Gateway Design',
            'assigned_by' => $owner->id,
            'assigned_to' => $owner->id,
            'status' => 'open',
        ]);

        // Add collaborator
        $addRes = $this->actingAs($owner)->post(route('tasks.collaborators.store', $task), [
            'user_id' => $collaborator->id,
            'role' => 'contributor',
        ]);
        $addRes->assertRedirect(route('tasks.show', $task));

        $this->assertTrue($task->hasCollaborator($collaborator->id));
        Notification::assertSentTo($collaborator, TaskCollaboratorNotification::class);

        // Collaborator can view task details
        $viewRes = $this->actingAs($collaborator)->get(route('tasks.show', $task));
        $viewRes->assertOk();

        // Remove collaborator
        $removeRes = $this->actingAs($owner)->delete(route('tasks.collaborators.destroy', [$task, $collaborator->id]));
        $removeRes->assertRedirect(route('tasks.show', $task));

        $this->assertFalse($task->hasCollaborator($collaborator->id));
    }

    public function test_comments_and_replies_can_be_posted_and_deleted(): void
    {
        Notification::fake();

        $owner = $this->employee(['name' => 'Owner']);
        $colleague = $this->employee(['name' => 'Colleague']);

        $task = Task::create([
            'title' => 'UI Revamp',
            'assigned_by' => $owner->id,
            'assigned_to' => $owner->id,
            'status' => 'open',
        ]);

        // Colleague adds collaborator first to view
        TaskCollaborator::create(['task_id' => $task->id, 'user_id' => $colleague->id, 'added_by' => $owner->id]);

        // Post top-level comment
        $commentRes = $this->actingAs($colleague)->post(route('tasks.comments.store', $task), [
            'comment' => 'Initial wireframes are uploaded.',
        ]);
        $commentRes->assertRedirect(route('tasks.show', $task));

        $comment = TaskComment::where('task_id', $task->id)->first();
        $this->assertNotNull($comment);
        $this->assertEquals('Initial wireframes are uploaded.', $comment->comment);

        // Owner posts reply
        $replyRes = $this->actingAs($owner)->post(route('tasks.comments.store', $task), [
            'parent_id' => $comment->id,
            'comment' => 'Looks great! Proceed with implementation.',
        ]);
        $replyRes->assertRedirect(route('tasks.show', $task));

        $this->assertEquals(1, $comment->replies()->count());

        // Colleague deletes their own comment
        $delRes = $this->actingAs($colleague)->delete(route('tasks.comments.destroy', [$task, $comment]));
        $delRes->assertRedirect(route('tasks.show', $task));
        $this->assertDatabaseMissing('task_comments', ['id' => $comment->id]);
    }

    public function test_attachments_upload_download_and_deletion(): void
    {
        Storage::fake('local');

        $owner = $this->employee();
        $task = Task::create([
            'title' => 'Doc Specs',
            'assigned_by' => $owner->id,
            'assigned_to' => $owner->id,
            'status' => 'open',
        ]);

        $file = UploadedFile::fake()->create('architecture_diagram.pdf', 250, 'application/pdf');

        $uploadRes = $this->actingAs($owner)->post(route('tasks.attachments.store', $task), [
            'file' => $file,
        ]);
        $uploadRes->assertRedirect(route('tasks.show', $task));

        $attachment = TaskAttachment::where('task_id', $task->id)->first();
        $this->assertNotNull($attachment);
        $this->assertEquals('architecture_diagram.pdf', $attachment->file_name);
        Storage::disk('local')->assertExists($attachment->file_path);

        // Download attachment
        $downloadRes = $this->actingAs($owner)->get(route('tasks.attachments.download', [$task, $attachment]));
        $downloadRes->assertOk();

        // Delete attachment
        $delRes = $this->actingAs($owner)->delete(route('tasks.attachments.destroy', [$task, $attachment]));
        $delRes->assertRedirect(route('tasks.show', $task));
        $this->assertDatabaseMissing('task_attachments', ['id' => $attachment->id]);
        Storage::disk('local')->assertMissing($attachment->file_path);
    }

    public function test_review_workflow_submit_changes_requested_and_approval(): void
    {
        Notification::fake();

        $manager = $this->manager(['name' => 'Manager Tanvi']);
        $employee = $this->employee(['name' => 'Employee Atharva', 'manager_id' => $manager->id]);

        $task = Task::create([
            'title' => 'Critical Payment Engine',
            'assigned_by' => $manager->id,
            'assigned_to' => $employee->id,
            'status' => 'in_progress',
            'review_required' => true,
        ]);

        // 1. Employee submits task for review
        $submitRes = $this->actingAs($employee)->post(route('tasks.review.submit', $task), [
            'notes' => 'Engine complete with full test coverage.',
        ]);
        $submitRes->assertRedirect(route('tasks.show', $task));

        $task->refresh();
        $this->assertEquals('in_review', $task->status);
        Notification::assertSentTo($manager, TaskReviewSubmittedNotification::class);

        // Employee cannot approve their own review
        $selfApproveRes = $this->actingAs($employee)->post(route('tasks.review.decide', $task), [
            'decision' => 'approved',
        ]);
        $selfApproveRes->assertForbidden();

        // 2. Manager requests changes
        $changesRes = $this->actingAs($manager)->post(route('tasks.review.decide', $task), [
            'decision' => 'changes_requested',
            'notes' => 'Please add idempotency key check.',
        ]);
        $changesRes->assertRedirect(route('tasks.show', $task));

        $task->refresh();
        $this->assertEquals('in_progress', $task->status);
        Notification::assertSentTo($employee, TaskReviewDecisionNotification::class);

        // 3. Employee resubmits
        $this->actingAs($employee)->post(route('tasks.review.submit', $task));
        $task->refresh();
        $this->assertEquals('in_review', $task->status);

        // 4. Manager approves review
        $approveRes = $this->actingAs($manager)->post(route('tasks.review.decide', $task), [
            'decision' => 'approved',
            'notes' => 'All checks verified. Approved!',
        ]);
        $approveRes->assertRedirect(route('tasks.show', $task));

        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertEquals(100, $task->progress);
        $this->assertNotNull($task->completed_at);
    }

    public function test_admin_assigning_or_delegating_task_to_employee_sets_urgent_priority(): void
    {
        $admin = $this->admin(['name' => 'Super Admin']);
        $employee = $this->employee(['name' => 'Bob Dev']);
        $manager = $this->manager(['name' => 'Alice Manager']);

        // 1. Admin creates task assigned to employee -> priority must be 'urgent'
        $createRes = $this->actingAs($admin)->post(route('tasks.store'), [
            'title' => 'Critical Server Hotfix',
            'assigned_to' => $employee->id,
            'priority' => 'medium', // Even if submitted with medium, admin assignment forces urgent
        ]);
        $createRes->assertRedirect(route('tasks.index'));

        $task = Task::where('title', 'Critical Server Hotfix')->first();
        $this->assertNotNull($task);
        $this->assertEquals('urgent', $task->priority);
        $this->assertEquals($employee->id, $task->assigned_to);
        $this->assertEquals($admin->id, $task->assigned_by);

        // 2. Admin self-task does not get forced to urgent if specified medium
        $selfRes = $this->actingAs($admin)->post(route('tasks.store'), [
            'title' => 'Admin internal review',
            'assigned_to' => $admin->id,
            'priority' => 'low',
        ]);
        $selfRes->assertRedirect(route('tasks.index'));
        $selfTask = Task::where('title', 'Admin internal review')->first();
        $this->assertNotNull($selfTask);
        $this->assertEquals('low', $selfTask->priority);

        // 3. Manager creates task assigned to employee -> keeps specified priority (not forced to urgent)
        $mgrCreateRes = $this->actingAs($manager)->post(route('tasks.store'), [
            'title' => 'Manager regular assignment',
            'assigned_to' => $employee->id,
            'priority' => 'medium',
        ]);
        $mgrCreateRes->assertRedirect(route('tasks.index'));
        $mgrTask = Task::where('title', 'Manager regular assignment')->first();
        $this->assertNotNull($mgrTask);
        $this->assertEquals('medium', $mgrTask->priority);

        // 4. Admin delegates an existing medium-priority task to an employee -> priority elevated to 'urgent'
        $employee2 = $this->employee(['name' => 'Charlie Dev']);
        $delegateRes = $this->actingAs($admin)->post(route('tasks.delegate', $mgrTask), [
            'assigned_to' => $employee2->id,
            'notes' => 'Admin delegated to Charlie Dev',
        ]);
        $delegateRes->assertRedirect(route('tasks.show', $mgrTask));

        $mgrTask->refresh();
        $this->assertEquals('urgent', $mgrTask->priority);
        $this->assertEquals($employee2->id, $mgrTask->assigned_to);
    }

    public function test_admin_can_classify_and_filter_tasks_by_department(): void
    {
        $admin = $this->admin(['name' => 'Department Overview Admin']);
        $devEmp = $this->employee(['name' => 'Dave Dev', 'department' => 'development']);
        $mktEmp = $this->employee(['name' => 'Molly Mkt', 'department' => 'marketing']);
        $mktEmp->syncRoles([]);
        $mktEmp->assignRole('marketing');

        $devTask = Task::create([
            'title' => 'Backend API optimization',
            'assigned_to' => $devEmp->id,
            'assigned_by' => $admin->id,
            'department' => 'development',
            'status' => 'open',
            'priority' => 'urgent',
        ]);

        $mktTask = Task::create([
            'title' => 'Q4 Social Campaign Launch',
            'assigned_to' => $mktEmp->id,
            'assigned_by' => $admin->id,
            'department' => 'marketing',
            'status' => 'open',
            'priority' => 'urgent',
        ]);

        // 1. Department helper methods on Task model
        $this->assertEquals('Development', $devTask->departmentLabel());
        $this->assertEquals('Marketing', $mktTask->departmentLabel());
        $this->assertStringContainsString('indigo', $devTask->departmentBadgeClasses());
        $this->assertStringContainsString('pink', $mktTask->departmentBadgeClasses());

        // 2. Admin viewing All Company Tasks with department filter = development
        $devFilterRes = $this->actingAs($admin)->get(route('tasks.index', [
            'tab' => 'team',
            'department' => 'development',
        ]));
        $devFilterRes->assertSuccessful();
        $devFilterRes->assertSee('Backend API optimization');
        $devFilterRes->assertDontSee('Q4 Social Campaign Launch');

        // 3. Admin viewing All Company Tasks with department filter = marketing
        $mktFilterRes = $this->actingAs($admin)->get(route('tasks.index', [
            'tab' => 'team',
            'department' => 'marketing',
        ]));
        $mktFilterRes->assertSuccessful();
        $mktFilterRes->assertSee('Q4 Social Campaign Launch');
        $mktFilterRes->assertDontSee('Backend API optimization');

        // 4. Admin viewing All Company Tasks without department filter sees both and sees Department column
        $allRes = $this->actingAs($admin)->get(route('tasks.index', [
            'tab' => 'team',
        ]));
        $allRes->assertSuccessful();
        $allRes->assertSee('Backend API optimization');
        $allRes->assertSee('Q4 Social Campaign Launch');
        $allRes->assertSee('Department');
        $allRes->assertSee('All Departments');

        // 5. Employee and Manager do NOT see the Department column or Department quick-filter bar
        $empRes = $this->actingAs($devEmp)->get(route('tasks.index'));
        $empRes->assertSuccessful();
        $empRes->assertDontSee('All Departments');

        $mgrUser = $this->manager(['department' => 'development']);
        $mgrRes = $this->actingAs($mgrUser)->get(route('tasks.index', ['tab' => 'team']));
        $mgrRes->assertSuccessful();
        $mgrRes->assertDontSee('All Departments');

        // 6. Creating task with explicit department selection
        $postRes = $this->actingAs($admin)->post(route('tasks.store'), [
            'title' => 'HR Compliance Audit',
            'department' => 'hr',
            'assigned_to' => $admin->id,
            'priority' => 'high',
        ]);
        $postRes->assertRedirect(route('tasks.index'));
        $hrTask = Task::where('title', 'HR Compliance Audit')->first();
        $this->assertNotNull($hrTask);
        $this->assertEquals('hr', $hrTask->department);
        $this->assertEquals('Hr', $hrTask->departmentLabel());
    }

    /**
     * Test admin escalating an unheeded task to Urgent with a special message and notification.
     */
    public function test_admin_can_escalate_task_to_urgent_with_special_message_and_notification(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $employee = $this->employee();

        // 1. Employee has an open task of low or medium priority
        $task = Task::create([
            'title' => 'Prepare quarterly report deck',
            'assigned_by' => $employee->id,
            'assigned_to' => $employee->id,
            'priority' => 'medium',
            'status' => 'open',
            'department' => 'development',
        ]);

        $this->assertFalse($task->isEscalated());
        $this->assertEquals('medium', $task->priority);

        // 2. Regular employee cannot escalate a task
        $unauthRes = $this->actingAs($employee)->post(route('tasks.escalate', $task), [
            'reason' => 'Trying to escalate self',
        ]);
        $unauthRes->assertForbidden();

        // 3. Admin escalates the task with a directive message
        $escalationDirective = 'Employee is not responding. Client demo is in 3 hours. Deliver report deck immediately!';
        $escalateRes = $this->actingAs($admin)->post(route('tasks.escalate', $task), [
            'reason' => $escalationDirective,
        ]);
        $escalateRes->assertRedirect();
        $escalateRes->assertSessionHas('success');

        // 4. Verify task state changed to Urgent and marked as escalated
        $task->refresh();
        $this->assertTrue($task->isEscalated());
        $this->assertEquals('urgent', $task->priority);
        $this->assertEquals($escalationDirective, $task->escalation_reason);
        $this->assertEquals($admin->id, $task->escalated_by);
        $this->assertNotNull($task->escalated_at);

        // 5. Verify escalation activity log and discussion comment notice
        $this->assertDatabaseHas('task_activity_logs', [
            'task_id' => $task->id,
            'user_id' => $admin->id,
            'action' => 'escalated',
            'field_name' => 'priority',
            'old_value' => 'medium',
            'new_value' => 'urgent',
        ]);

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->id,
            'user_id' => $admin->id,
        ]);

        $comment = $task->comments()->latest()->first();
        $this->assertStringContainsString('ADMIN ESCALATION NOTICE', $comment->comment);
        $this->assertStringContainsString($escalationDirective, $comment->comment);

        // 6. Verify dedicated TaskEscalatedNotification sent to employee
        Notification::assertSentTo(
            $employee,
            TaskEscalatedNotification::class,
            function (TaskEscalatedNotification $notification) use ($task, $escalationDirective) {
                $data = $notification->toArray($task->assignee);
                return $data['type'] === 'task_escalated'
                    && $data['priority'] === 'urgent'
                    && $data['task_id'] === $task->id
                    && str_contains($data['message'], $escalationDirective);
            }
        );

        // 7. Employee visits the task show page and sees the Escalation Alert Banner & badge
        $showRes = $this->actingAs($employee)->get(route('tasks.show', $task));
        $showRes->assertSuccessful();
        $showRes->assertSee('ESCALATED TO URGENT');
        $showRes->assertSee('Admin Escalation Directive:');
        $showRes->assertSee($escalationDirective);
        $showRes->assertSee('Escalation Notice');

        // 8. Completed tasks cannot be escalated
        $task->update(['status' => 'completed']);
        $completedRes = $this->actingAs($admin)->post(route('tasks.escalate', $task), [
            'reason' => 'Cannot escalate completed',
        ]);
        $completedRes->assertSessionHasErrors('reason');
    }

    public function test_creator_manager_and_admin_can_request_rework_even_after_task_completed(): void
    {
        Notification::fake();

        $admin = $this->admin(['name' => 'Super Admin']);
        $manager = $this->manager(['name' => 'Dan Manager', 'department' => 'development']);
        $creator = $this->employee(['name' => 'Alice Creator', 'department' => 'development']);
        $assignee = $this->employee(['name' => 'Bob Worker', 'department' => 'development']);
        $otherEmployee = $this->employee(['name' => 'Charlie Outsider', 'department' => 'sales']);

        // 1. Create a completed task
        $task = Task::create([
            'title' => 'Deliver quarterly revenue analysis report',
            'assigned_by' => $creator->id,
            'assigned_to' => $assignee->id,
            'department' => 'development',
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
            'priority' => 'medium',
        ]);

        // 2. Unrelated/unauthorized employee cannot request rework
        $forbiddenRes = $this->actingAs($otherEmployee)->post(route('tasks.rework', $task), [
            'rework_notes' => 'Please redo this',
        ]);
        $forbiddenRes->assertForbidden();

        // 3. Task Creator requests rework on completed task
        $creatorNotes = 'The charts in appendix B have mismatched margins and Q3 EBITDA is missing.';
        $creatorRes = $this->actingAs($creator)->post(route('tasks.rework', $task), [
            'rework_notes' => $creatorNotes,
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);
        $creatorRes->assertRedirect(route('tasks.show', $task));
        $creatorRes->assertSessionHas('success');

        $task->refresh();
        $this->assertEquals('rework', $task->status);
        $this->assertTrue($task->isRework());
        $this->assertNull($task->completed_at);
        $this->assertEquals(50, $task->progress);
        $this->assertEquals($creatorNotes, $task->rework_notes);
        $this->assertEquals(1, $task->rework_count);
        $this->assertEquals($creator->id, $task->rework_by);
        $this->assertNotNull($task->rework_at);
        $this->assertEquals(Carbon::tomorrow()->toDateString(), $task->due_date->toDateString());

        // Verify notification sent to assignee
        Notification::assertSentTo(
            $assignee,
            TaskReworkNotification::class,
            function (TaskReworkNotification $notification) use ($task, $creatorNotes) {
                $data = $notification->toArray($task->assignee);
                return $data['type'] === 'task_rework'
                    && $data['task_id'] === $task->id
                    && str_contains($data['rework_notes'], $creatorNotes);
            }
        );

        // Verify activity log & discussion comment created
        $this->assertDatabaseHas('task_activity_logs', [
            'task_id' => $task->id,
            'user_id' => $creator->id,
            'action' => 'rework_requested',
        ]);

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->id,
            'user_id' => $creator->id,
        ]);
        $latestComment = $task->comments()->latest()->first();
        $this->assertStringContainsString('TASK SENT BACK FOR RE-WORK', $latestComment->comment);
        $this->assertStringContainsString($creatorNotes, $latestComment->comment);

        // 4. Mark completed again
        $task->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
        ]);

        // 5. Department Manager requests rework on completed task
        $managerNotes = 'Legal team found clause 4.2 missing from page 12. Fix required before final signoff.';
        $managerRes = $this->actingAs($manager)->post(route('tasks.rework', $task), [
            'rework_notes' => $managerNotes,
        ]);
        $managerRes->assertRedirect(route('tasks.show', $task));

        $task->refresh();
        $this->assertEquals('rework', $task->status);
        $this->assertNull($task->completed_at);
        $this->assertEquals(2, $task->rework_count);
        $this->assertEquals($manager->id, $task->rework_by);
        $this->assertEquals($managerNotes, $task->rework_notes);

        // 6. Mark completed again
        $task->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
        ]);

        // 7. Company Admin requests rework on completed task
        $adminNotes = 'Executive audit requested breakdown by region. Please update tables 1-4.';
        $adminRes = $this->actingAs($admin)->post(route('tasks.rework', $task), [
            'rework_notes' => $adminNotes,
        ]);
        $adminRes->assertRedirect(route('tasks.show', $task));

        $task->refresh();
        $this->assertEquals('rework', $task->status);
        $this->assertNull($task->completed_at);
        $this->assertEquals(3, $task->rework_count);
        $this->assertEquals($admin->id, $task->rework_by);
        $this->assertEquals($adminNotes, $task->rework_notes);

        // 8. Assignee views task show page and sees Rework Banner and details
        $showRes = $this->actingAs($assignee)->get(route('tasks.show', $task));
        $showRes->assertSuccessful();
        $showRes->assertSee('TASK UNDER RE-WORK');
        $showRes->assertSee($adminNotes);
        $showRes->assertSee('Re-work Directive');
        $showRes->assertSee('Revision #3');
    }

    public function test_employee_in_development_department_only_sees_development_projects_not_other_departments(): void
    {
        $devEmp = $this->employee(['name' => 'Atharva Dev', 'department' => 'development']);

        // 1. Create projects across different departments
        $devProject = Project::create([
            'title' => 'Iconicpages CRM Core API',
            'department' => 'development',
            'status' => 'active',
            'created_by' => $devEmp->id,
        ]);

        $mktProject = Project::create([
            'title' => 'Linkdn Setup ( CMO Onkar Naik )',
            'department' => 'marketing',
            'status' => 'active',
        ]);

        $salesProject = Project::create([
            'title' => 'Enterprise Sales Outbound v2',
            'department' => 'sales',
            'status' => 'active',
        ]);

        // 2. Dev Employee accesses tasks index
        $response = $this->actingAs($devEmp)->get(route('tasks.index'));
        $response->assertSuccessful();

        // 3. Dev project should be visible, but other department projects must NOT be visible
        $response->assertSee('Iconicpages CRM Core API');
        $response->assertDontSee('Linkdn Setup ( CMO Onkar Naik )');
        $response->assertDontSee('Enterprise Sales Outbound v2');

        // 4. Scoped query directly
        $scopedProjects = Project::forUser($devEmp)->pluck('title');
        $this->assertTrue($scopedProjects->contains('Iconicpages CRM Core API'));
        $this->assertFalse($scopedProjects->contains('Linkdn Setup ( CMO Onkar Naik )'));
        $this->assertFalse($scopedProjects->contains('Enterprise Sales Outbound v2'));

        // 5. Attempting to view another department's project details is forbidden
        $forbiddenRes = $this->actingAs($devEmp)->get(route('projects.show', $mktProject));
        $forbiddenRes->assertForbidden();
    }
}
