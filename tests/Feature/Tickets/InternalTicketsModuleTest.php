<?php

namespace Tests\Feature\Tickets;

use App\Models\InternalTicket;
use App\Models\InternalTicketMessage;
use App\Models\Task;
use App\Models\User;
use App\Notifications\InternalTicketResolvedNotification;
use App\Notifications\NewInternalTicketAdminNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Rbac\RbacTestCase;

class InternalTicketsModuleTest extends RbacTestCase
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

        $user->assignRole('development_manager');
        return $user;
    }

    /**
     * Test employee can raise a confidential ticket directly to admin bypassing their manager.
     */
    public function test_employee_can_raise_ticket_directly_to_admin_bypassing_manager(): void
    {
        Notification::fake();
        Storage::fake('local');

        $admin = $this->admin();
        $manager = $this->manager();
        $employee = $this->employee(['manager_id' => $manager->id]);

        // Employee has a task where manager didn't give requirements
        $task = Task::create([
            'title' => 'Client Payment Gateway Integration',
            'assigned_by' => $manager->id,
            'assigned_to' => $employee->id,
            'priority' => 'urgent',
            'status' => 'open',
            'department' => 'development',
        ]);

        // 1. Employee visits ticket creation page
        $createRes = $this->actingAs($employee)->get(route('tickets.create', ['task_id' => $task->id]));
        $createRes->assertSuccessful();
        $createRes->assertSee('Direct Channel to Company Administration');
        $createRes->assertSee('Manager Bypassed');

        // 2. Employee submits ticket
        $ticketData = [
            'subject' => 'Leadership questioning delay but manager has not provided API keys or specs',
            'category' => 'manager_blocker',
            'priority' => 'high',
            'task_id' => $task->id,
            'description' => 'CEO asked why payment gateway is not live. I requested API credentials from manager 4 times over Slack and email since Monday, but received no response or specs.',
            'attachments' => [
                UploadedFile::fake()->create('slack_proof.png', 150, 'image/png'),
            ],
        ];

        $postRes = $this->actingAs($employee)->post(route('tickets.store'), $ticketData);
        $postRes->assertRedirect();

        $ticket = InternalTicket::where('subject', $ticketData['subject'])->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($employee->id, $ticket->user_id);
        $this->assertEquals($task->id, $ticket->task_id);
        $this->assertEquals('manager_blocker', $ticket->category);
        $this->assertEquals('open', $ticket->status);
        $this->assertStringStartsWith('TKT-', $ticket->ticket_number);

        // Attachment stored
        $this->assertCount(1, $ticket->attachments);
        $this->assertEquals('slack_proof.png', $ticket->attachments->first()->file_name);

        // 3. Admin receives notification
        Notification::assertSentTo(
            $admin,
            NewInternalTicketAdminNotification::class,
            function (NewInternalTicketAdminNotification $n) use ($ticket) {
                $data = $n->toArray($ticket->user);
                return $data['type'] === 'internal_ticket_created' && $data['ticket_id'] === $ticket->id;
            }
        );

        // 4. Manager does NOT receive notification
        Notification::assertNotSentTo($manager, NewInternalTicketAdminNotification::class);
    }

    /**
     * Test confidentiality: Manager cannot view employee's confidential ticket.
     */
    public function test_manager_cannot_view_or_access_employee_confidential_ticket(): void
    {
        $admin = $this->admin();
        $manager = $this->manager();
        $employee = $this->employee(['manager_id' => $manager->id]);
        $otherEmployee = $this->employee();

        $ticket = InternalTicket::create([
            'user_id' => $employee->id,
            'category' => 'unfair_blame',
            'priority' => 'high',
            'subject' => 'Manager blaming team for missed sprint deadline',
            'description' => 'Confidential details of internal politics and unfair blame.',
            'status' => 'open',
        ]);

        // Employee can view own ticket
        $empRes = $this->actingAs($employee)->get(route('tickets.show', $ticket));
        $empRes->assertSuccessful();
        $empRes->assertSee('Manager blaming team for missed sprint deadline');

        // Admin can view ticket
        $adminRes = $this->actingAs($admin)->get(route('tickets.show', $ticket));
        $adminRes->assertSuccessful();

        // Manager is strictly forbidden
        $mgrRes = $this->actingAs($manager)->get(route('tickets.show', $ticket));
        $mgrRes->assertForbidden();

        // Other employee is strictly forbidden
        $otherRes = $this->actingAs($otherEmployee)->get(route('tickets.show', $ticket));
        $otherRes->assertForbidden();

        // In ticket index: Manager does not see employee's ticket
        $mgrIndex = $this->actingAs($manager)->get(route('tickets.index'));
        $mgrIndex->assertSuccessful();
        $mgrIndex->assertDontSee($ticket->ticket_number);
        $mgrIndex->assertDontSee('Manager blaming team for missed sprint deadline');
    }

    /**
     * Test two-way communication between Employee and Admin, and Admin resolving the ticket.
     */
    public function test_two_way_communication_and_admin_resolution_flow(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $employee = $this->employee();

        $ticket = InternalTicket::create([
            'user_id' => $employee->id,
            'category' => 'manager_blocker',
            'priority' => 'high',
            'subject' => 'Missing database access blocked by department head',
            'description' => 'I cannot test the migration without staging DB credentials.',
            'status' => 'open',
        ]);

        // 1. Admin replies with question
        $adminReply = $this->actingAs($admin)->post(route('tickets.reply', $ticket), [
            'message' => 'We received your report. Did the department head give a reason for withholding access?',
        ]);
        $adminReply->assertRedirect(route('tickets.show', $ticket));

        $this->assertDatabaseHas('internal_ticket_messages', [
            'internal_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'message' => 'We received your report. Did the department head give a reason for withholding access?',
        ]);

        // Employee notified
        Notification::assertSentTo($employee, InternalTicketResolvedNotification::class);

        // 2. Employee replies back to Admin
        $empReply = $this->actingAs($employee)->post(route('tickets.reply', $ticket), [
            'message' => 'No reason given, just said "will check next week" while CEO was told it is ready today.',
        ]);
        $empReply->assertRedirect(route('tickets.show', $ticket));

        $this->assertDatabaseHas('internal_ticket_messages', [
            'internal_ticket_id' => $ticket->id,
            'user_id' => $employee->id,
            'message' => 'No reason given, just said "will check next week" while CEO was told it is ready today.',
        ]);

        // 3. Admin can add a private internal note (invisible to employee)
        $adminNote = $this->actingAs($admin)->post(route('tickets.reply', $ticket), [
            'message' => 'CONFIDENTIAL: Investigation confirmed manager has not ordered DB instance yet.',
            'is_admin_note' => 1,
        ]);
        $adminNote->assertRedirect(route('tickets.show', $ticket));

        $showEmp = $this->actingAs($employee)->get(route('tickets.show', $ticket));
        $showEmp->assertSuccessful();
        $showEmp->assertDontSee('CONFIDENTIAL: Investigation confirmed manager has not ordered DB instance yet.');

        // 4. Admin resolves ticket with official directive
        $resolutionDirective = 'Administration has spoken directly to executive leadership and verified credentials delay is on management. You are fully exonerated from the delay. Direct staging credentials have been provisioned to your profile.';
        $resolveRes = $this->actingAs($admin)->post(route('tickets.resolve', $ticket), [
            'status' => 'resolved',
            'admin_response' => $resolutionDirective,
            'admin_notes' => 'Resolved directly with CEO and employee.',
        ]);
        $resolveRes->assertRedirect(route('tickets.show', $ticket));

        $ticket->refresh();
        $this->assertEquals('resolved', $ticket->status);
        $this->assertEquals($admin->id, $ticket->resolved_by);
        $this->assertNotNull($ticket->resolved_at);
        $this->assertEquals($resolutionDirective, $ticket->admin_response);

        // Employee visits ticket and sees resolution
        $showResolved = $this->actingAs($employee)->get(route('tickets.show', $ticket));
        $showResolved->assertSuccessful();
        $showResolved->assertSee('Official Administration Resolution and Decision');
        $showResolved->assertSee($resolutionDirective);

        // 5. Non-admin cannot resolve tickets
        $unauthResolve = $this->actingAs($employee)->post(route('tickets.resolve', $ticket), [
            'status' => 'closed',
        ]);
        $unauthResolve->assertForbidden();
    }
}
