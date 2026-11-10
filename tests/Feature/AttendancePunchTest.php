<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskWorkLog;
use App\Models\User;
use Carbon\Carbon;
use Tests\Feature\Rbac\RbacTestCase;

class AttendancePunchTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    public function test_user_can_punch_in_and_out(): void
    {
        $user = User::factory()->create([
            'email' => 'testworker@example.com',
            'department' => 'development',
            'designation' => 'software developer',
            'role' => 'development',
        ]);

        $responseIn = $this->actingAs($user)->post('/attendance/punch-in');
        $responseIn->assertRedirect();
        $responseIn->assertSessionHas('success');

        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', Carbon::today())->first();
        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->check_in);

        $responseOut = $this->actingAs($user)->post('/attendance/punch-out');
        $responseOut->assertRedirect();
        $responseOut->assertSessionHas('success');

        $attendance->refresh();
        $this->assertNotNull($attendance->check_out);
    }

    public function test_no_auto_punch_on_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'devworker@example.com',
            'password' => bcrypt('password123'),
            'department' => 'development',
            'designation' => 'software developer',
            'role' => 'development',
            'is_active' => true,
        ]);

        // 1. Login via POST /login should NOT punch in automatically
        $responseLogin = $this->post('/login', [
            'email' => 'devworker@example.com',
            'password' => 'password123',
        ]);
        $responseLogin->assertRedirect(route('dashboard'));

        $att = Attendance::where('user_id', $user->id)->whereDate('date', Carbon::today())->first();
        $this->assertNull($att, 'Login must NOT auto-punch in');

        // 2. User explicitly clicks "Punch In" button
        $this->actingAs($user)->post('/attendance/punch-in');
        $att = Attendance::where('user_id', $user->id)->whereDate('date', Carbon::today())->first();
        $this->assertNotNull($att, 'Attendance created only after manual punch in');
        $this->assertNotNull($att->check_in);
        $this->assertNull($att->check_out);

        // 3. User logs out or closes browser - attendance should REMAIN punched in
        $responseLogout = $this->actingAs($user)->post('/logout');
        $responseLogout->assertRedirect(route('login'));

        $att->refresh();
        $this->assertNull($att->check_out, 'Attendance must stay punched in even after user logs out');
    }

    public function test_tab_close_does_not_punch_out_user(): void
    {
        $user = User::factory()->create([
            'email' => 'tabcloser@example.com',
            'password' => bcrypt('password123'),
            'department' => 'development',
            'designation' => 'software developer',
            'role' => 'development',
            'is_active' => true,
        ]);

        // User manually punches in
        $this->actingAs($user)->post('/attendance/punch-in');
        $att = Attendance::where('user_id', $user->id)->whereDate('date', Carbon::today())->first();
        $this->assertNotNull($att);
        $this->assertNull($att->check_out);

        // Tab close beacon received
        $response = $this->actingAs($user)->postJson('/attendance/tab-close-logout', [
            'tab_id' => 'tab_test_123',
        ]);

        $response->assertSuccessful();
        $response->assertJson(['status' => 'session_kept_active']);

        // Assert attendance remains punched in
        $att->refresh();
        $this->assertNull($att->check_out, 'Tab close must NOT punch out the user');
    }

    public function test_punch_out_with_completed_tasks_marks_tasks_and_logs_attendance(): void
    {
        $user = User::factory()->create([
            'email' => 'taskworker@example.com',
            'department' => 'development',
            'designation' => 'developer',
            'role' => 'development',
            'is_active' => true,
        ]);

        $project = Project::create([
            'title' => 'Client Portal Redesign',
            'status' => 'active',
        ]);

        $task1 = Task::create([
            'title' => 'Implement API Authentication',
            'project_id' => $project->id,
            'assigned_to' => $user->id,
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $task2 = Task::create([
            'title' => 'Write Unit Tests for Auth',
            'project_id' => $project->id,
            'assigned_to' => $user->id,
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        // Punch in first
        $this->actingAs($user)->post('/attendance/punch-in');

        // Evening punch out with ticked tasks
        $response = $this->actingAs($user)->post('/attendance/punch-out', [
            'completed_task_ids' => [$task1->id, $task2->id],
            'notes' => 'Finished both authentication and testing today.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check attendance record
        $att = Attendance::where('user_id', $user->id)->whereDate('date', Carbon::today())->first();
        $this->assertNotNull($att->check_out);
        $this->assertStringContainsString('Finished both authentication and testing today.', $att->notes);

        // Check tasks are marked completed
        $task1->refresh();
        $task2->refresh();
        $this->assertEquals('completed', $task1->status);
        $this->assertNotNull($task1->completed_at);
        $this->assertEquals('completed', $task2->status);
        $this->assertNotNull($task2->completed_at);

        // Check task work logs created
        $logs = TaskWorkLog::where('attendance_id', $att->id)->get();
        $this->assertCount(2, $logs);
    }

    public function test_heartbeat_keeps_session_active(): void
    {
        $user = User::factory()->create([
            'email' => 'heartbeat@example.com',
            'department' => 'marketing',
            'role' => 'marketing',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson('/attendance/heartbeat', [
            'tab_id' => 'tab_active_456',
        ]);

        $response->assertSuccessful();
        $response->assertJson([
            'status' => 'active',
            'user_id' => $user->id,
        ]);
    }

    public function test_employee_cannot_punch_in_after_shift_end_time(): void
    {
        $user = User::factory()->create([
            'email' => 'latepuncher@example.com',
            'department' => 'development',
            'role' => 'development',
            'is_active' => true,
        ]);

        // Attempting to punch in at 21:57 (after 18:30 shift end)
        Carbon::setTestNow(Carbon::today()->setTime(21, 57));

        $response = $this->actingAs($user)->post('/attendance/punch-in');
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Office shift ended at 06:30 PM. Punch-in is closed for today.');

        $att = Attendance::where('user_id', $user->id)->whereDate('date', Carbon::today())->first();
        $this->assertNull($att, 'No attendance record should be created after shift end time');
    }

    public function test_employee_can_punch_out_after_shift_end_time_if_clocked_in_earlier(): void
    {
        $user = User::factory()->create([
            'email' => 'overtimeworker@example.com',
            'department' => 'development',
            'role' => 'development',
            'is_active' => true,
        ]);

        // Employee punched in during shift hours at 10:00 AM
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));
        $this->actingAs($user)->post('/attendance/punch-in');

        // Works overtime and punches out at 21:57
        Carbon::setTestNow(Carbon::today()->setTime(21, 57));
        $response = $this->actingAs($user)->post('/attendance/punch-out', [
            'notes' => 'Overtime shift complete.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $att = Attendance::where('user_id', $user->id)->whereDate('date', Carbon::today())->first();
        $this->assertNotNull($att);
        $this->assertEquals('10:00:00', $att->check_in);
        $this->assertEquals('21:57:00', $att->check_out);
    }
}
