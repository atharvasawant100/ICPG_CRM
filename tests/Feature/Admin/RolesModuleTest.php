<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Rbac\DataScope;
use App\Rbac\Rbac;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Feature\Rbac\RbacTestCase;

/**
 * Admin Roles & Permissions module.
 */
class RolesModuleTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    private function role(string $name): Role
    {
        return Role::findByName($name, Rbac::GUARD);
    }

    public function test_admin_can_open_the_roles_index(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/roles')
            ->assertSuccessful();
    }

    public function test_index_lists_every_seeded_role(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/roles');

        $response->assertSuccessful();

        foreach (Rbac::ROLES as $roleName) {
            $response->assertSee(ucfirst($roleName));
        }
    }

    public function test_admin_can_open_a_role_matrix(): void
    {
        $this->actingAs($this->admin())
            ->get("/admin/roles/{$this->role('sales')->id}/edit")
            ->assertSuccessful();
    }

    public function test_user_without_roles_permission_gets_403(): void
    {
        $this->actingAs($this->rolelessUser())
            ->get('/admin/roles')
            ->assertForbidden();
    }

    public function test_editing_the_admin_role_is_rejected_on_the_server(): void
    {
        $adminRole = $this->role(Rbac::ADMIN_ROLE);

        $before = $adminRole->permissions()->count();

        $response = $this->actingAs($this->admin())
            ->put("/admin/roles/{$adminRole->id}", [
                'permissions' => ['leads' => ['view' => '1']],
            ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('cannot be edited', session('error'));

        $this->assertSame(
            $before,
            $adminRole->permissions()->count(),
            'The admin role must be untouched by a rejected update.'
        );
    }

    public function test_saving_the_matrix_changes_permissions(): void
    {
        $sales = $this->role('sales');

        $this->assertFalse($sales->hasPermissionTo('leads.view'));

        $this->actingAs($this->admin())
            ->put("/admin/roles/{$sales->id}", [
                'permissions' => [
                    'leads' => ['view' => '1', 'create' => '1'],
                    'customers' => ['view' => '1'],
                ],
                'scopes' => ['leads' => DataScope::OWN],
            ])
            ->assertSessionHas('success');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $sales = $this->role('sales');

        $this->assertTrue($sales->hasPermissionTo('leads.view'));
        $this->assertTrue($sales->hasPermissionTo('leads.create'));
        $this->assertTrue($sales->hasPermissionTo('customers.view'));
        $this->assertFalse($sales->hasPermissionTo('leads.delete'));
    }

    public function test_unchecking_removes_a_permission(): void
    {
        $sales = $this->role('sales');
        $sales->givePermissionTo(Permission::findByName('leads.view', Rbac::GUARD));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Submit with the box unticked.
        $this->actingAs($this->admin())
            ->put("/admin/roles/{$sales->id}", [
                'permissions' => ['leads' => ['create' => '1']],
            ])
            ->assertSessionHas('success');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertFalse($this->role('sales')->hasPermissionTo('leads.view'));
    }

    public function test_a_crafted_request_cannot_invent_a_permission(): void
    {
        $sales = $this->role('sales');

        $this->actingAs($this->admin())
            ->put("/admin/roles/{$sales->id}", [
                'permissions' => [
                    'leads' => ['view' => '1', 'not_a_real_action' => '1'],
                    'fabricated' => ['view' => '1'],
                ],
            ])
            ->assertSessionHas('success');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // hasPermissionTo() throws for an unknown name rather than returning
        // false, so assert against what actually landed in the pivot table.
        $granted = DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('role_has_permissions.role_id', $sales->id)
            ->pluck('permissions.name');

        $this->assertNotContains('leads.not_a_real_action', $granted->all(), 'Unknown action must be discarded.');
        $this->assertNotContains('fabricated.view', $granted->all(), 'Unknown module must be discarded.');
        $this->assertContains('leads.view', $granted->all(), 'The real permission is still saved.');
    }

    public function test_saving_clears_the_permission_cache(): void
    {
        $sales = $this->role('sales');

        $this->actingAs($this->admin())->put("/admin/roles/{$sales->id}", [
            'permissions' => ['leads' => ['view' => '1']],
        ]);

        // No manual cache flush here: if the controller forgot to clear it, the
        // permission would not be visible to a freshly-loaded user.
        $user = \App\Models\User::factory()->create();
        $user->assignRbacRole('sales');

        $this->assertTrue($user->fresh()->can('leads.view'));
    }

    public function test_a_temporary_role_can_be_used_immediately_after_saving(): void
    {
        $sales = $this->role('sales');
        $user = \App\Models\User::factory()->create();
        $user->assignRbacRole('sales');

        // Before: cannot list leads.
        $this->actingAs($user->fresh())->get('/leads')->assertForbidden();

        $this->actingAs($this->admin())->put("/admin/roles/{$sales->id}", [
            'permissions' => ['leads' => ['view' => '1']],
        ])->assertSessionHas('success');

        // After: can list, still cannot create.
        $this->actingAs($user->fresh())->get('/leads')->assertSuccessful();
        $this->actingAs($user->fresh())->get('/leads/create')->assertForbidden();
    }

    public function test_permission_changes_are_audited(): void
    {
        $sales = $this->role('sales');

        $this->actingAs($this->admin())->put("/admin/roles/{$sales->id}", [
            'permissions' => ['leads' => ['view' => '1']],
        ]);

        $row = AuditLog::where('module', 'roles')->latest('id')->firstOrFail();

        $this->assertSame('update', $row->action);
        $this->assertSame($sales->id, (int) $row->record_id);
        $this->assertContains('leads.view', $row->new_values['permissions']);
    }

    public function test_data_scopes_are_saved(): void
    {
        $manager = $this->role('manager');

        $this->actingAs($this->admin())->put("/admin/roles/{$manager->id}", [
            'permissions' => ['leads' => ['view' => '1']],
            'scopes' => [
                'leads' => DataScope::TEAM,
                'customers' => DataScope::ALL,
            ],
        ])->assertSessionHas('success');

        $this->assertSame(DataScope::TEAM, DataScope::scopeForRole('manager', 'leads'));
        $this->assertSame(DataScope::ALL, DataScope::scopeForRole('manager', 'customers'));
    }

    public function test_a_team_scope_lets_a_manager_see_direct_reports_records(): void
    {
        $manager = $this->role('manager');
        $manager->givePermissionTo(Permission::findByName('leads.view', Rbac::GUARD));

        $boss = \App\Models\User::factory()->create();
        $boss->assignRbacRole('manager');

        $report = \App\Models\User::factory()->create(['manager_id' => $boss->id]);
        $reportLead = \App\Models\Lead::factory()->create(['created_by' => $report->id]);
        $strangerLead = \App\Models\Lead::factory()->create();

        $this->actingAs($this->admin())->put("/admin/roles/{$manager->id}", [
            'permissions' => ['leads' => ['view' => '1']],
            'scopes' => ['leads' => DataScope::TEAM],
        ])->assertSessionHas('success');

        $this->actingAs($boss->fresh())->get("/leads/{$reportLead->id}")->assertSuccessful();
        $this->actingAs($boss->fresh())->get("/leads/{$strangerLead->id}")->assertForbidden();
    }

    public function test_an_invalid_scope_value_falls_back_to_own(): void
    {
        $sales = $this->role('sales');

        $this->actingAs($this->admin())->put("/admin/roles/{$sales->id}", [
            'permissions' => ['leads' => ['view' => '1']],
            'scopes' => ['leads' => 'everything-for-everyone'],
        ])->assertSessionHas('success');

        $this->assertSame(DataScope::OWN, DataScope::scopeForRole('sales', 'leads'));
    }

    public function test_admin_role_lock_is_reflected_in_the_view(): void
    {
        $response = $this->actingAs($this->admin())
            ->get("/admin/roles/{$this->role(Rbac::ADMIN_ROLE)->id}/edit");

        $response->assertSuccessful();
        $response->assertSee('Admin always has full access');
        $response->assertDontSee('Save Permissions');
    }

    public function test_the_matrix_shows_only_cells_that_exist(): void
    {
        $response = $this->actingAs($this->admin())
            ->get("/admin/roles/{$this->role('sales')->id}/edit");

        $response->assertSuccessful();

        // settings has view but no create, so no settings.create input exists.
        $this->assertStringNotContainsString(
            'permissions[settings][create]',
            $response->getContent()
        );

        // but settings.view does exist.
        $response->assertSee('permissions[settings][view]', false);
    }
}