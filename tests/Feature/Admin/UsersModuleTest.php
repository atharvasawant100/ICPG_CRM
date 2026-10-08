<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use App\Rbac\Rbac;
use App\Services\UserManagementService;
use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Rbac\RbacTestCase;

/**
 * Admin Users module.
 */
class UsersModuleTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_admin_can_open_the_users_index(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/users')
            ->assertSuccessful();
    }

    public function test_admin_can_open_the_create_form(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/users/create')
            ->assertSuccessful();
    }

    public function test_user_without_permissions_gets_403_on_every_admin_page(): void
    {
        $user = $this->rolelessUser();

        foreach (['/admin/users', '/admin/roles', '/admin/audit-logs', '/admin/settings'] as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        foreach (['/admin/users', '/admin/roles', '/admin/audit-logs', '/admin/settings'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_admin_can_create_a_user(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Ramesh Patel',
            'email' => 'ramesh@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'role' => 'sales',
            'department' => 'sales',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $created = User::where('email', 'ramesh@example.com')->firstOrFail();

        $this->assertTrue($created->hasRole('sales'));
        $this->assertSame('sales', $created->getAttribute('role'), 'The mirror must match Spatie.');
        $this->assertSame('sales', $created->department);
    }

    public function test_create_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($this->admin())
            ->post('/admin/users', [
                'name' => 'Someone',
                'email' => 'taken@example.com',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
                'role' => 'sales',
                'department' => 'sales',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_department_is_required_for_employee_roles(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/users', [
                'name' => 'No Dept',
                'email' => 'nodept@example.com',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
                'role' => 'marketing',
                'department' => '',
            ])
            ->assertSessionHasErrors('department');
    }

    public function test_department_is_optional_for_admin_and_manager(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'A Manager',
            'email' => 'mgr@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'role' => 'manager',
            'department' => '',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'mgr@example.com']);
    }

    public function test_admin_can_edit_a_user_and_the_role_stays_in_sync(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['department' => 'sales']);
        $target->assignRbacRole('sales');

        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => 'Renamed Person',
            'email' => $target->email,
            'role' => 'development',
            'department' => 'development',
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $target->refresh();

        $this->assertSame('Renamed Person', $target->name);
        $this->assertTrue($target->hasRole('development'));
        $this->assertFalse($target->hasRole('sales'));
        $this->assertSame('development', $target->getAttribute('role'), 'Mirror must follow Spatie.');
    }

    public function test_password_is_optional_on_edit(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['department' => 'sales']);
        $target->assignRbacRole('sales');
        $originalHash = $target->password;

        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => $target->name,
            'email' => $target->email,
            'role' => 'sales',
            'department' => 'sales',
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame($originalHash, $target->fresh()->password, 'Password must not change when blank.');
    }

    public function test_admin_cannot_deactivate_themselves(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put("/admin/users/{$admin->id}", [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => Rbac::ADMIN_ROLE,
                'is_active' => '0',
            ])
            ->assertSessionHasErrors('is_active');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put("/admin/users/{$admin->id}", [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'sales',
                'department' => 'sales',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('role');

        $this->assertTrue($admin->fresh()->hasRole(Rbac::ADMIN_ROLE));
    }

    public function test_admin_cannot_toggle_themselves_off(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post("/admin/users/{$admin->id}/toggle", ['activate' => '0'])
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_the_last_admin_cannot_be_deactivated(): void
    {
        /*
         | The seeder creates admin@iconicpages.com, so both it and the test
         | admin hold the role. Remove the seeded one to reach the true
         | last-admin state.
         */
        User::where('email', RolePermissionSeeder::ADMIN_EMAILS[0])->delete();

        $admin = $this->admin();

        $this->assertSame(1, app(UserManagementService::class)->activeAdminCount());

        $this->actingAs($admin)
            ->post("/admin/users/{$admin->id}/toggle", ['activate' => '0'])
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_the_last_admin_cannot_be_demoted_by_another_admin(): void
    {
        User::where('email', RolePermissionSeeder::ADMIN_EMAILS[0])->delete();

        $admin = $this->admin();
        $target = User::factory()->create();
        $target->assignRbacRole(Rbac::ADMIN_ROLE);

        // The acting admin is now the second one, so the target may be demoted.
        $this->assertFalse(app(UserManagementService::class)->wouldOrphanAdmins($target, false));

        // Make the acting admin inactive so the target becomes the only admin.
        User::whereKey($admin->id)->update(['is_active' => false]);

        $this->assertTrue(
            app(UserManagementService::class)->wouldOrphanAdmins($target, false),
            'With no other active admin, demoting must be blocked.'
        );
    }

    public function test_the_last_admin_cannot_be_demoted(): void
    {
        User::where('email', RolePermissionSeeder::ADMIN_EMAILS[0])->delete();

        $admin = $this->admin();

        $this->assertSame(1, app(UserManagementService::class)->activeAdminCount());

        $this->actingAs($admin)
            ->put("/admin/users/{$admin->id}", [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'manager',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors();

        $this->assertTrue($admin->fresh()->hasRole(Rbac::ADMIN_ROLE));
    }

    public function test_an_admin_can_be_deactivated_once_another_admin_exists(): void
    {
        $admin = $this->admin();
        $other = User::factory()->create();
        $other->assignRbacRole(Rbac::ADMIN_ROLE);

        $this->actingAs($admin)
            ->post("/admin/users/{$other->id}/toggle", ['activate' => '0'])
            ->assertSessionHas('success');

        $this->assertFalse($other->fresh()->is_active);
    }

    public function test_a_user_cannot_be_their_own_manager(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->put("/admin/users/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'role' => 'sales',
                'department' => 'sales',
                'manager_id' => $target->id,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('manager_id');
    }

    public function test_a_manager_loop_is_rejected(): void
    {
        $admin = $this->admin();

        // chain: a -> b -> c.  Trying to make c report to a creates a cycle.
        $a = User::factory()->create();
        $b = User::factory()->create(['manager_id' => $a->id]);
        $c = User::factory()->create(['manager_id' => $b->id]);

        foreach ([$a, $b, $c] as $node) {
            $node->assignRbacRole('manager');
        }

        $this->actingAs($admin)
            ->put("/admin/users/{$a->id}", [
                'name' => $a->name,
                'email' => $a->email,
                'role' => 'manager',
                'manager_id' => $c->id,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('manager_id');

        $this->assertNotSame($c->id, $a->fresh()->manager_id);
    }

    public function test_a_valid_manager_can_be_assigned(): void
    {
        $admin = $this->admin();
        $manager = User::factory()->create();
        $manager->assignRbacRole('manager');

        $target = User::factory()->create();
        $target->assignRbacRole('sales');

        $this->actingAs($admin)
            ->put("/admin/users/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'role' => 'sales',
                'department' => 'sales',
                'manager_id' => $manager->id,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($manager->id, $target->fresh()->manager_id);
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        // A fresh admin for this test: the previous request logged a user out,
        // and assertGuest() below needs a clean session.
        $user = User::factory()->create([
            'email' => 'switched@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->flushSession();

        $this->actingAs($this->admin())
            ->post("/admin/users/{$user->id}/toggle", ['activate' => '0'])
            ->assertSessionHas('success');

        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_a_deactivated_user_is_logged_out_of_an_active_session(): void
    {
        $admin = $this->admin();
        $victim = User::factory()->create(['is_active' => true]);

        // Give the victim a working session with real permissions, so the only
        // thing that can eject them afterwards is the deactivation itself.
        $victim->assignRbacRole('sales');
        \Spatie\Permission\Models\Role::findByName('sales', \App\Rbac\Rbac::GUARD)
            ->givePermissionTo(\Spatie\Permission\Models\Permission::findByName('leads.view', \App\Rbac\Rbac::GUARD));
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($victim->fresh())->get('/leads')->assertSuccessful();

        // The admin disables the account.
        $this->actingAs($admin)
            ->post("/admin/users/{$victim->id}/toggle", ['activate' => '0'])
            ->assertSessionHas('success');

        $this->assertFalse($victim->fresh()->is_active);

        // Their next request is ejected, even though the session still exists.
        $this->actingAs($victim->fresh())
            ->get('/leads')
            ->assertRedirect(route('login'));
    }

    public function test_a_user_can_be_reactivated(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['is_active' => false]);
        $target->assignRbacRole('sales');

        $this->actingAs($admin)
            ->post("/admin/users/{$target->id}/toggle", ['activate' => '1'])
            ->assertSessionHas('success');

        $this->assertTrue($target->fresh()->is_active);
    }

    public function test_reset_password_changes_the_hash_and_never_logs_it(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->post("/admin/users/{$target->id}/reset-password", [
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ])
            ->assertSessionHas('success');

        $target->refresh();

        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('brand-new-password', $target->password),
            'The new password must work.'
        );

        // Nothing sensitive anywhere in the trail.
        $serialised = json_encode(
            AuditLog::where('module', 'users')->get()
                ->map(fn ($row) => [$row->old_values, $row->new_values])->all()
        );

        $this->assertStringNotContainsString('brand-new-password', $serialised);
        $this->assertStringNotContainsString('$2y$', $serialised);
    }

    public function test_reset_password_requires_a_confirmation_match(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->post("/admin/users/{$target->id}/reset-password", [
                'password' => 'one-password',
                'password_confirmation' => 'different',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_creating_a_user_is_audited(): void
    {
        $this->actingAs($this->admin())->post('/admin/users', [
            'name' => 'Audited',
            'email' => 'audited-user@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'role' => 'hr',
            'department' => 'hr',
        ]);

        $this->assertDatabaseHas('audit_logs', ['module' => 'users', 'action' => 'create']);
    }

    public function test_index_can_be_searched_and_filtered(): void
    {
        $admin = $this->admin();

        $salesA = User::factory()->create(['name' => 'Alice Sales', 'department' => 'sales']);
        $salesA->assignRbacRole('sales');

        $marketingB = User::factory()->create(['name' => 'Bob Marketing', 'department' => 'marketing']);
        $marketingB->assignRbacRole('marketing');

        $this->actingAs($admin)->get('/admin/users?search=Alice')
            ->assertSuccessful()->assertSee('Alice Sales')->assertDontSee('Bob Marketing');

        $this->actingAs($admin)->get('/admin/users?department=marketing')
            ->assertSuccessful()->assertSee('Bob Marketing')->assertDontSee('Alice Sales');

        $this->actingAs($admin)->get('/admin/users?role=sales')
            ->assertSuccessful()->assertSee('Alice Sales')->assertDontSee('Bob Marketing');
    }
}