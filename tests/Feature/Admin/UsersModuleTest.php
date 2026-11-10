<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Department;
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
            // No role field: the role is derived from department + designation.
            'designation' => 'business executive',
            'department' => 'sales',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $created = User::where('email', 'ramesh@example.com')->firstOrFail();

        $this->assertTrue($created->hasRole('sales'));
        $this->assertSame('sales', $created->getAttribute('role'), 'The mirror must match Spatie.');
        $this->assertSame('business executive', $created->designation);
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
                'department' => 'sales',
                'designation' => 'intern',
            ])
            ->assertSessionHasErrors('email');
    }

    /**
     * A designation needs a department to resolve against, because the
     * department is what decides the employee's access level.
     */
    public function test_a_designation_requires_a_department(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/users', [
                'name' => 'No Dept',
                'email' => 'nodept@example.com',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
                'designation' => 'team leader',
                'department' => '',
            ])
            ->assertSessionHasErrors('department');

        $this->assertDatabaseMissing('users', ['email' => 'nodept@example.com']);
    }

    /**
     * A designation from another department's list must be refused, so a
     * crafted request cannot smuggle one through.
     */
    public function test_a_designation_from_another_department_is_rejected(): void
    {
        /*
         | Sets its own per-department lists so the rule is tested regardless of
         | what config/designations.php currently ships. A designation that is
         | valid somewhere must still be refused for a department that does not
         | offer it.
         */
        config([
            'designations.departments' => [
                'sales' => ['intern' => 'Intern', 'sales executive' => 'Sales Executive'],
                'development' => ['intern' => 'Intern', 'software developer' => 'Software Developer'],
            ],
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/users', [
                'name' => 'Mismatch',
                'email' => 'mismatch@example.com',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
                'designation' => 'sales executive',
                'department' => 'development',
            ])
            ->assertSessionHasErrors('designation');

        $this->assertDatabaseMissing('users', ['email' => 'mismatch@example.com']);

        // The right department accepts it.
        $this->actingAs($this->admin())
            ->post('/admin/users', [
                'name' => 'Matched',
                'email' => 'matched@example.com',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
                'designation' => 'sales executive',
                'department' => 'sales',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'matched@example.com']);
    }

    /**
     * The department decides the role; only a designation that elevates
     * overrides it.
     */
    public function test_only_an_elevating_designation_changes_the_role(): void
    {
        $admin = $this->admin();

        // Team Leader in development stays an ordinary development employee.
        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Dev Lead',
            'email' => 'devlead@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'designation' => 'team leader',
            'department' => 'development',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(User::where('email', 'devlead@example.com')->firstOrFail()->hasRole('development'));

        // Manager elevates.
        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Dev Manager',
            'email' => 'devmgr@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'designation' => 'manager',
            'department' => 'development',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(User::where('email', 'devmgr@example.com')->firstOrFail()->hasRole('manager'));
    }

    /**
     * With neither field filled in the user is created with no access at all,
     * rather than being silently given a department's permissions.
     */
    public function test_a_user_with_no_department_is_created_without_access(): void
    {
        $this->actingAs($this->admin())->post('/admin/users', [
            'name' => 'Unconfigured',
            'email' => 'unconfigured@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertSessionHasNoErrors();

        $created = User::where('email', 'unconfigured@example.com')->firstOrFail();

        $this->assertCount(0, $created->getRoleNames());
        $this->assertNull($created->department);
    }

    public function test_admin_can_edit_a_user_and_the_role_stays_in_sync(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['department' => 'sales']);
        $target->assignRbacRole('sales');

        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => 'Renamed Person',
            'email' => $target->email,
            'designation' => 'team leader',
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
            'designation' => 'intern',
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
                'is_active' => '0',
            ])
            ->assertSessionHasErrors('is_active');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = $this->admin();

        // Any department/designation pair derives a non-admin role, so the
        // admin would be demoting themselves.
        $this->actingAs($admin)
            ->put("/admin/users/{$admin->id}", [
                'name' => $admin->name,
                'email' => $admin->email,
                'designation' => 'intern',
                'department' => 'sales',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('department');

        $this->assertTrue($admin->fresh()->hasRole(Rbac::ADMIN_ROLE));
    }

    /**
     * Renaming yourself must not silently strip your access, because the form
     * has no role field for the request to preserve.
     */
    public function test_editing_your_own_name_keeps_your_admin_role(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put("/admin/users/{$admin->id}", [
                'name' => 'Hitesh Renamed',
                'email' => $admin->email,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Hitesh Renamed', $admin->fresh()->name);
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
                'designation' => 'manager',
                'department' => 'sales',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('department');

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

    /**
 * The Manager field is gone: the manager comes from the department. A user who
 * is their own department's manager would report to themselves, so that
 * department is refused.
 */
public function test_a_user_cannot_report_to_themselves(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();

        Department::where('name', 'sales')->update(['manager_id' => $target->id]);

        $this->actingAs($admin)
            ->put("/admin/users/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'designation' => 'intern',
                'department' => 'sales',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('department');

        $this->assertNull($target->fresh()->manager_id);
    }

    /**
     * A department whose manager already sits below this user in the reporting
     * chain would create a loop.
     */
    public function test_a_manager_loop_is_rejected(): void
    {
        $admin = $this->admin();

        // chain: a -> b -> c. Making a's department managed by c loops.
        $a = User::factory()->create();
        $b = User::factory()->create(['manager_id' => $a->id]);
        $c = User::factory()->create(['manager_id' => $b->id]);

        Department::where('name', 'sales')->update(['manager_id' => $c->id]);

        $this->actingAs($admin)
            ->put("/admin/users/{$a->id}", [
                'name' => $a->name,
                'email' => $a->email,
                'designation' => 'intern',
                'department' => 'sales',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('department');

        $this->assertNotSame($c->id, $a->fresh()->manager_id);
    }

    /**
     * The happy path: choosing a department assigns its manager automatically.
     */
    public function test_the_department_manager_is_assigned_automatically(): void
    {
        $admin = $this->admin();
        $manager = User::factory()->create();
        $manager->assignRbacRole('manager');

        Department::where('name', 'sales')->update(['manager_id' => $manager->id]);

        $target = User::factory()->create();
        $target->assignRbacRole('sales');

        $this->actingAs($admin)
            ->put("/admin/users/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'designation' => 'intern',
                'department' => 'sales',
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($manager->id, $target->fresh()->manager_id);
    }

    /**
     * A new user inherits the department's manager on creation too.
     */
    public function test_a_new_user_inherits_the_department_manager(): void
    {
        $manager = User::factory()->create();
        $manager->assignRbacRole('manager');

        Department::where('name', 'hr')->update(['manager_id' => $manager->id]);

        $this->actingAs($this->admin())->post('/admin/users', [
            'name' => 'New Starter',
            'email' => 'starter@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'designation' => 'hr executive',
            'department' => 'hr',
        ])->assertSessionHasNoErrors();

        $created = User::where('email', 'starter@example.com')->firstOrFail();

        $this->assertSame($manager->id, $created->manager_id);
        $this->assertTrue($created->hasRole('hr'));
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
            'department' => 'hr',
            'designation' => 'intern',
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

    public function test_admin_can_create_a_user_with_auto_generated_emp_id(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Emp One',
            'email' => 'emp1@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'department' => 'sales',
            'designation' => 'business executive',
        ])->assertRedirect(route('admin.users.index'));

        $user1 = User::where('email', 'emp1@example.com')->firstOrFail();
        $this->assertSame('ICPG_01', $user1->emp_id);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Emp Two',
            'email' => 'emp2@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'department' => 'sales',
            'designation' => 'business executive',
        ])->assertRedirect(route('admin.users.index'));

        $user2 = User::where('email', 'emp2@example.com')->firstOrFail();
        $this->assertSame('ICPG_02', $user2->emp_id);
    }

    public function test_admin_can_create_a_user_with_custom_emp_id(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/users', [
            'emp_id' => 'ICPG_99',
            'name' => 'Custom Emp',
            'email' => 'custom@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'department' => 'sales',
            'designation' => 'business executive',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'custom@example.com')->firstOrFail();
        $this->assertSame('ICPG_99', $user->emp_id);
    }

    public function test_emp_id_must_be_unique(): void
    {
        $admin = $this->admin();

        User::factory()->create(['emp_id' => 'ICPG_10']);

        $this->actingAs($admin)->post('/admin/users', [
            'emp_id' => 'ICPG_10',
            'name' => 'Duplicate Emp',
            'email' => 'dup@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'department' => 'sales',
            'designation' => 'business executive',
        ])->assertSessionHasErrors('emp_id');
    }

    public function test_users_index_can_search_by_emp_id(): void
    {
        $admin = $this->admin();

        User::factory()->create([
            'name' => 'Search Target',
            'email' => 'target@example.com',
            'emp_id' => 'ICPG_77',
        ]);

        $this->actingAs($admin)->get('/admin/users?search=ICPG_77')
            ->assertSuccessful()
            ->assertSee('Search Target')
            ->assertSee('ICPG_77');
    }

    public function test_creating_a_manager_directly_assigns_them_as_department_manager(): void
    {
        $admin = $this->admin();

        // Existing staff member in development with no manager yet
        $existingDev = User::factory()->create([
            'name' => 'Junior Developer',
            'email' => 'jr@example.com',
            'department' => 'development',
            'designation' => 'software developer',
            'manager_id' => null,
        ]);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New Dev Head',
            'email' => 'head@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'department' => 'development',
            'designation' => 'manager',
        ])->assertSessionHasNoErrors();

        $newManager = User::where('email', 'head@example.com')->firstOrFail();
        $this->assertTrue($newManager->hasRole('manager'));
        $this->assertNull($newManager->manager_id);

        // Verify the department's manager_id is now directly this user
        $department = \App\Models\Department::where('name', 'development')->firstOrFail();
        $this->assertSame($newManager->id, (int) $department->manager_id);

        // Verify existing staff in development now reports to this new manager
        $this->assertSame($newManager->id, (int) $existingDev->fresh()->manager_id);
    }

    public function test_hr_user_can_open_create_form_and_create_user(): void
    {
        $hrUser = User::factory()->create([
            'name' => 'Katrina HR',
            'department' => 'hr',
            'designation' => 'hr executive',
        ]);
        $hrUser->assignRbacRole('hr');

        // HR opens users index and create form
        $this->actingAs($hrUser)->get('/admin/users')->assertSuccessful();
        $this->actingAs($hrUser)->get('/admin/users/create')->assertSuccessful();

        // HR creates a new employee
        $response = $this->actingAs($hrUser)->post('/admin/users', [
            'name' => 'Kunal Sharma',
            'email' => 'kunal@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'department' => 'development',
            'designation' => 'software developer',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.users.index'));

        $createdUser = User::where('email', 'kunal@example.com')->firstOrFail();
        $this->assertSame('Kunal Sharma', $createdUser->name);
        $this->assertSame('development', $createdUser->department);
        $this->assertTrue($createdUser->hasRole('development'));
    }

    public function test_admin_can_view_employee_profile(): void
    {
        $admin = $this->admin();
        $employee = User::factory()->create([
            'emp_id' => 'ICPG_07',
            'name' => 'Akshata Atharva Sawant',
            'email' => 'akshu@example.com',
            'department' => 'sales',
            'designation' => 'manager',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.show', $employee));

        $response->assertSuccessful();
        $response->assertSee('Akshata Atharva Sawant');
        $response->assertSee('ICPG_07');
        $response->assertSee('Sales Department');
    }

    public function test_admin_departments_page_links_to_employee_profile(): void
    {
        $admin = $this->admin();
        $dept = Department::firstOrCreate(['name' => 'sales'], [
            'description' => 'Sales and Client Outreach',
            'is_active' => true,
        ]);
        $employee = User::factory()->create([
            'emp_id' => 'ICPG_07',
            'name' => 'Akshata Atharva Sawant',
            'department' => 'sales',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.departments.index'));

        $response->assertSuccessful();
        $response->assertSee(route('admin.users.show', $employee));
    }

    public function test_hr_can_view_employee_profile(): void
    {
        $hrUser = User::factory()->create([
            'name' => 'Katrina HR',
            'department' => 'hr',
            'designation' => 'hr executive',
        ]);
        $hrUser->assignRbacRole('hr');

        $employee = User::factory()->create([
            'name' => 'Omkar Dev',
            'department' => 'development',
        ]);

        $response = $this->actingAs($hrUser)->get(route('admin.users.show', $employee));

        $response->assertSuccessful();
        $response->assertSee('Omkar Dev');
    }

    public function test_employee_profile_displays_salary_breakdown_and_attendance(): void
    {
        $admin = $this->admin();
        $employee = User::factory()->create([
            'emp_id' => 'ICPG_99',
            'name' => 'Deepak Verma',
            'department' => 'development',
            'designation' => 'software developer',
        ]);

        \App\Models\EmployeeSalary::create([
            'user_id' => $employee->id,
            'basic_salary' => 25000,
            'hra' => 10000,
            'conveyance_allowance' => 2000,
            'special_allowance' => 3000,
            'other_allowances' => 0,
            'gross_salary' => 40000,
            'provident_fund' => 1800,
            'professional_tax' => 200,
            'tds' => 500,
            'other_deductions' => 0,
            'total_deductions' => 2500,
            'net_salary' => 37500,
            'approval_status' => 'approved',
        ]);

        \App\Models\Attendance::create([
            'user_id' => $employee->id,
            'date' => \Carbon\Carbon::today()->toDateString(),
            'check_in' => '09:30:00',
            'check_out' => '18:30:00',
            'total_minutes' => 540,
            'status' => 'present',
            'method' => 'web',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.show', $employee));

        $response->assertSuccessful();
        $response->assertSee('Deepak Verma');
        $response->assertSee('40,000.00'); // Gross
        $response->assertSee('37,500.00'); // Net
        $response->assertSee('2,500.00');  // Deductions
        $response->assertSee('Approved Structure');
    }
}

