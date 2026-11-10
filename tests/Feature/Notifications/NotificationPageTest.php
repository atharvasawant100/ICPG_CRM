<?php

namespace Tests\Feature\Notifications;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCompletedNotification;
use App\Rbac\Rbac;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Feature\Rbac\RbacTestCase;

class NotificationPageTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();

        foreach (['dashboard.view', 'tasks.view', 'tasks.create'] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => Rbac::GUARD]);
        }

        Role::firstOrCreate(['name' => 'test_role', 'guard_name' => Rbac::GUARD]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_guest_is_redirected_to_login_when_accessing_notifications_page(): void
    {
        $response = $this->get(route('notifications.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_clicking_or_visiting_notifications_page_marks_unread_notifications_as_read(): void
    {
        $user = User::factory()->create([
            'name' => 'John Assignee',
            'department' => 'development',
        ]);
        $user->assignRbacRole('test_role');
        $user->givePermissionTo(['dashboard.view', 'tasks.view']);

        $task = Task::create([
            'title' => 'Design new landing page',
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
            'status' => 'pending',
            'priority' => 'high',
        ]);

        // Send a notification to this user
        $user->notify(new TaskAssignedNotification($task));

        $this->assertSame(1, $user->unreadNotifications()->count());

        // Visiting notifications page
        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewIs('notifications.index');
        $response->assertSee('Design new landing page');
        $response->assertSee('Marked 1 unread notification as read');

        // Verify notification is marked as read in database
        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_navbar_notification_bell_links_to_notifications_page(): void
    {
        $user = User::factory()->create(['department' => 'development']);
        $user->assignRbacRole('test_role');
        $user->givePermissionTo(['dashboard.view', 'tasks.view']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('notifications.index'));
    }

    public function test_user_can_delete_notification(): void
    {
        $user = User::factory()->create(['department' => 'development']);
        $user->assignRbacRole('test_role');
        $user->givePermissionTo(['dashboard.view', 'tasks.view']);

        $task = Task::create([
            'title' => 'Sample Task',
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
            'status' => 'pending',
            'priority' => 'high',
        ]);
        $user->notify(new TaskAssignedNotification($task));

        $notification = $user->notifications()->firstOrFail();

        $response = $this->actingAs($user)->delete(route('notifications.destroy', $notification->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }
}
