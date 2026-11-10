<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Rbac\Rbac;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Feature\Rbac\RbacTestCase;

/**
 * The sidebar, header badge and dashboard panel are permission-driven.
 *
 * These assert rendered HTML, which is the only way to prove a Blade @can
 * actually hides something.
 *
 * Assertions are scoped to the relevant region of the page: words like
 * "Customers" and "Expenses" also appear in the Business Health widget and in
 * chart script tags, so a whole-page assertDontSee would fail for the wrong
 * reason.
 */
class PermissionDrivenUiTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    /**
     * A role holding the given permissions.
     *
     * `dashboard.view` is always granted: without it the user cannot render the
     * dashboard at all, so there would be nothing to assert about which cards or
     * sidebar items they can see.
     */
    private function userWith(array $permissions, string $roleName = 'ui_test_role'): User
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => Rbac::GUARD]);

        $role->syncPermissions(
            Permission::whereIn('name', array_merge(['dashboard.view'], $permissions))->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->assignRbacRole($roleName);

        return $user->fresh();
    }

    /** The sidebar markup only. */
    private function sidebar(string $html): string
    {
        $start = strpos($html, '<aside');
        $end = $html === '' ? false : strpos($html, '</aside>');

        return ($start !== false && $end !== false) ? substr($html, $start, $end - $start) : '';
    }

    /** The Quick Actions block only. */
    private function quickActions(string $html): string
    {
        $start = strpos($html, 'Quick Actions');
        $end = $start === false ? false : strpos($html, 'Analytics', $start);

        return ($start !== false && $end !== false) ? substr($html, $start, $end - $start) : '';
    }

    /** The KPI card grid only. Anchors on the div's class, not the CSS rules. */
    private function kpiGrid(string $html): string
    {
        $start = strpos($html, 'class="kpi-grid');
        $end = $start === false ? false : strpos($html, 'Quick Actions', $start);

        return ($start !== false && $end !== false) ? substr($html, $start, $end - $start) : '';
    }

    // ---- Sidebar ---------------------------------------------------------

    public function test_admin_sees_the_full_sidebar_including_administration(): void
    {
        $html = $this->actingAs($this->admin())->get('/dashboard')->assertSuccessful()->getContent();
        $nav = $this->sidebar($html);

        $this->assertNotSame('', $nav, 'The sidebar must render.');

        // Existing modules, unchanged labels.
        foreach (['Dashboard', 'Customers', 'Leads', 'Expenses', 'Services', 'Invoices'] as $label) {
            $this->assertStringContainsString($label, $nav);
        }

        // The new section and its items.
        $this->assertStringContainsString('Administration', $nav);
        $this->assertStringContainsString('Users &amp; Roles', $nav);
        $this->assertStringContainsString('Roles &amp; Permissions', $nav);
        $this->assertStringContainsString('Audit Log', $nav);
        $this->assertStringContainsString('Settings', $nav);

        // Sub-items still present.
        foreach (['Leads Dashboard', 'Add New Lead', 'Follow-up Calendar', 'Recurring Invoices', 'Expense Categories'] as $child) {
            $this->assertStringContainsString($child, $nav);
        }

        // Profile and Logout.
        $this->assertStringContainsString('Profile', $nav);
        $this->assertStringContainsString('Logout', $nav);
    }

    public function test_admin_sidebar_links_point_at_the_admin_urls(): void
    {
        $nav = $this->sidebar($this->actingAs($this->admin())->get('/dashboard')->getContent());

        $this->assertStringContainsString(route('admin.users.index'), $nav);
        $this->assertStringContainsString(route('admin.roles.index'), $nav);
        $this->assertStringContainsString(route('admin.audit-logs.index'), $nav);
        $this->assertStringContainsString(route('admin.settings.index'), $nav);
    }

    public function test_leads_only_role_sees_only_leads_in_the_sidebar(): void
    {
        $user = $this->userWith(['leads.view', 'leads.create'], 'leads_only');

        $nav = $this->sidebar($this->actingAs($user)->get('/dashboard')->assertSuccessful()->getContent());

        $this->assertStringContainsString('Leads', $nav);
        $this->assertStringContainsString('Add New Lead', $nav);
        $this->assertStringContainsString('Dashboard', $nav);

        foreach (['Customers', 'Expenses', 'Services', 'Invoices', 'Administration', 'Audit Log'] as $hidden) {
            $this->assertStringNotContainsString($hidden, $nav, "Sidebar should not show {$hidden}.");
        }
    }

    public function test_restricted_role_does_not_see_the_administration_section(): void
    {
        $user = $this->userWith(['leads.view'], 'leads_only');

        $nav = $this->sidebar($this->actingAs($user)->get('/dashboard')->getContent());

        $this->assertStringNotContainsString('Administration', $nav);
        $this->assertStringNotContainsString(route('admin.users.index'), $nav);
    }

    /**
     * A parent whose children are all hidden disappears entirely, including the
     * collapsible button.
     */
    public function test_parent_menu_with_no_allowed_children_is_hidden(): void
    {
        $user = $this->userWith(['customers.view'], 'customers_only');

        $nav = $this->sidebar($this->actingAs($user)->get('/dashboard')->getContent());

        $this->assertStringContainsString('Customers', $nav);

        // The Leads parent button is gone, not just its sub-items.
        $this->assertStringNotContainsString('data-nav-toggle="leads"', $nav);
        $this->assertStringNotContainsString('Add New Lead', $nav);
        $this->assertStringNotContainsString('Trash Leads', $nav);

        // Customers is a leaf, so it renders as a link not a parent button.
        $this->assertStringContainsString('data-nav-id="customers"', $nav);
    }

    public function test_a_partially_allowed_parent_shows_only_permitted_children(): void
    {
        $user = $this->userWith(['leads.view'], 'partial_leads');

        $nav = $this->sidebar($this->actingAs($user)->get('/dashboard')->getContent());

        $this->assertStringContainsString('data-nav-toggle="leads"', $nav);
        $this->assertStringContainsString('Trash Leads', $nav);
        $this->assertStringNotContainsString('Add New Lead', $nav);
        $this->assertStringNotContainsString('Follow-up Calendar', $nav);
    }

    // ---- Header role badge -----------------------------------------------

    public function test_admin_sees_a_role_badge(): void
    {
        $html = $this->actingAs($this->admin())->get('/dashboard')->assertSuccessful()->getContent();

        $this->assertStringContainsString('Role: Admin', $html);
    }

    public function test_non_admin_also_sees_a_role_badge(): void
    {
        $user = $this->userWith(['leads.view'], 'sales');
        $user->forceFill(['department' => 'sales'])->save();

        $html = $this->actingAs($user->fresh())->get('/dashboard')->assertSuccessful()->getContent();

        $this->assertStringContainsString('Role: Sales', $html);
    }

    public function test_department_appears_in_the_badge(): void
    {
        $user = $this->userWith(['leads.view'], 'sales');
        $user->forceFill(['department' => 'marketing'])->save();

        $html = $this->actingAs($user->fresh())->get('/dashboard')->getContent();

        $this->assertStringContainsString('Role: Sales', $html);
        $this->assertStringContainsString('Department: Marketing', $html);
    }

    /**
     * A user can hold a permission directly with no role at all, which is the
     * only way to reach the dashboard while having no role to display.
     */
    public function test_no_badge_without_a_role(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            Permission::findByName('dashboard.view', Rbac::GUARD)
        );
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $html = $this->actingAs($user->fresh())->get('/dashboard')->assertSuccessful()->getContent();

        $this->assertFalse($user->fresh()->hasRole(Rbac::ADMIN_ROLE));
        $this->assertStringNotContainsString('Role: ', $html);
    }

    // ---- Dashboard cards and quick actions -------------------------------

    public function test_admin_sees_all_kpi_cards_and_quick_actions(): void
    {
        $html = $this->actingAs($this->admin())->get('/dashboard')->assertSuccessful()->getContent();
        $kpis = $this->kpiGrid($html);
        $actions = $this->quickActions($html);

        foreach ([
            'Amount Collected', 'Overdue Outstanding', 'Monthly Revenue', 'Monthly Expenses',
            'Total Leads', 'Converted Leads', 'Conversion Rate', 'Total Invoices', 'Net Profit',
        ] as $card) {
            $this->assertStringContainsString($card, $kpis, "Admin should see the {$card} card.");
        }

        foreach ([
            'New Lead', 'New Invoice', 'Add Expense', 'Add Customer',
            'Follow-up Calendar', 'Recurring Invoice',
        ] as $action) {
            $this->assertStringContainsString($action, $actions, "Admin should see {$action}.");
        }
    }

    public function test_leads_only_role_sees_only_lead_cards_and_the_new_lead_action(): void
    {
        $user = $this->userWith(['leads.view', 'leads.create'], 'leads_only');

        $html = $this->actingAs($user)->get('/dashboard')->assertSuccessful()->getContent();
        $kpis = $this->kpiGrid($html);
        $actions = $this->quickActions($html);

        // Lead cards survive.
        foreach (['Total Leads', 'Converted Leads', 'Conversion Rate'] as $card) {
            $this->assertStringContainsString($card, $kpis);
        }

        // Money and expense cards do not.
        foreach (['Amount Collected', 'Overdue Outstanding', 'Monthly Revenue', 'Monthly Expenses', 'Total Invoices'] as $card) {
            $this->assertStringNotContainsString($card, $kpis, "Should not see {$card}.");
        }

        // Only the matching quick action.
        $this->assertStringContainsString('New Lead', $actions);
        foreach (['New Invoice', 'Add Expense', 'Add Customer', 'Follow-up Calendar', 'Recurring Invoice'] as $action) {
            $this->assertStringNotContainsString($action, $actions, "Should not see {$action}.");
        }
    }

    public function test_leads_only_role_sees_only_the_leads_analytics_tab(): void
    {
        $user = $this->userWith(['leads.view'], 'leads_only');

        $html = $this->actingAs($user)->get('/dashboard')->assertSuccessful()->getContent();

        $this->assertStringContainsString('id="tab-leads"', $html);
        $this->assertStringNotContainsString('id="tab-invoices"', $html);
        $this->assertStringNotContainsString('id="tab-expenses"', $html);
        $this->assertStringNotContainsString('id="tab-revenue"', $html);

        // The first permitted tab must be active, so the panel is not blank.
        $this->assertStringContainsString('id="panel-leads" class="chart-panel p-4', $html);
    }

    public function test_invoices_only_role_defaults_to_the_invoices_tab(): void
    {
        $user = $this->userWith(['invoices.view'], 'invoices_only');

        $html = $this->actingAs($user)->get('/dashboard')->assertSuccessful()->getContent();

        $this->assertStringNotContainsString('id="tab-leads"', $html);
        $this->assertStringContainsString('id="panel-invoices" class="chart-panel p-4', $html);
    }

    // ---- Users & Roles panel ---------------------------------------------

    public function test_admin_sees_the_users_and_roles_panel(): void
    {
        $html = $this->actingAs($this->admin())->get('/dashboard')->assertSuccessful()->getContent();

        $this->assertStringContainsString('Users &amp; Roles', $html);
        $this->assertStringContainsString('Edit permissions', $html);

        foreach (Rbac::ROLES as $roleName) {
            $this->assertStringContainsString(ucfirst($roleName), $html);
        }
    }

    public function test_admin_row_shows_full_access_and_all_records(): void
    {
        $html = $this->actingAs($this->admin())->get('/dashboard')->getContent();

        $this->assertStringContainsString('All modules, users, roles, settings', $html);
        $this->assertStringContainsString('All records', $html);
    }

    public function test_panel_access_text_is_built_from_real_permissions(): void
    {
        Role::findByName('sales', Rbac::GUARD)
            ->syncPermissions(['leads.view', 'leads.create']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $html = $this->actingAs($this->admin())->get('/dashboard')->getContent();

        // Derived from the granted permissions, grouped by module.
        $this->assertStringContainsString('Leads (create, view)', $html);
    }

    public function test_panel_data_scope_comes_from_the_scope_settings(): void
    {
        \Illuminate\Support\Facades\DB::table('role_module_scopes')
            ->where('role_name', 'manager')
            ->where('module', 'leads')
            ->update(['scope' => 'team']);

        $html = $this->actingAs($this->admin())->get('/dashboard')->getContent();

        $this->assertStringContainsString('Own + direct reports: leads', $html);
    }

    public function test_panel_is_hidden_without_roles_view(): void
    {
        $user = $this->userWith(['leads.view'], 'leads_only');

        $html = $this->actingAs($user)->get('/dashboard')->getContent();

        $this->assertStringNotContainsString('Edit permissions', $html);
    }

    public function test_panel_edit_link_points_at_the_role_matrix(): void
    {
        $sales = Role::findByName('sales', Rbac::GUARD);

        $html = $this->actingAs($this->admin())->get('/dashboard')->getContent();

        $this->assertStringContainsString(route('admin.roles.edit', $sales), $html);
    }

    // ---- Server-side still wins -------------------------------------------

    public function test_a_restricted_user_still_gets_403_on_a_blocked_page(): void
    {
        $user = $this->userWith(['leads.view'], 'leads_only');

        // The sidebar hides these, but a direct URL must still be refused.
        $this->actingAs($user)->get('/customers')->assertForbidden();
        $this->actingAs($user)->get('/admin/users')->assertForbidden();
        $this->actingAs($user)->get('/admin/audit-logs')->assertForbidden();
    }

    public function test_hr_dashboard_does_not_show_task_containers_or_overdue(): void
    {
        $hrUser = User::factory()->create([
            'email' => 'katrina_hr@example.com',
            'department' => 'hr',
            'designation' => 'Hr Executive',
            'role' => 'hr',
            'is_active' => true,
        ]);
        $hrUser->assignRbacRole('hr');
        $hrUser->givePermissionTo('dashboard.view');

        $response = $this->actingAs($hrUser)->get('/dashboard');
        $response->assertSuccessful();

        $content = $response->getContent();

        // Must show Workplace metrics like Working Hours, Leave Remaining, Attendance
        $this->assertStringContainsString('Working Hours', $content);
        $this->assertStringContainsString('Leave Remaining', $content);
        $this->assertStringContainsString('Attendance', $content);

        // Must NOT show Task Completed or Overdue Tasks containers on the workplace dashboard
        $this->assertStringNotContainsString('Tasks Completed', $content);
        $this->assertStringNotContainsString('Overdue Tasks', $content);
        $this->assertStringNotContainsString('My Assigned Tasks', $content);

        // Sidebar must NOT show Task Pool
        $this->assertStringNotContainsString('Task Pool', $content);
    }

    public function test_sales_manager_dashboard_does_not_show_task_containers_or_pool(): void
    {
        $salesManager = User::factory()->create([
            'name' => 'Akshata Atharva Sawant',
            'email' => 'akshata_sales@example.com',
            'department' => 'sales',
            'designation' => 'Manager',
            'role' => 'manager',
            'is_active' => true,
        ]);
        $salesManager->assignRbacRole('manager');
        $salesManager->givePermissionTo('dashboard.view');

        $response = $this->actingAs($salesManager)->get('/dashboard');
        $response->assertSuccessful();

        $content = $response->getContent();

        // Must show manager overview cards
        $this->assertStringContainsString('Department &amp; Team Overview', $content);
        $this->assertStringContainsString('Department Team', $content);
        $this->assertStringContainsString('Paid Leaves Balance', $content);

        // Must NOT show any task-related elements
        $this->assertStringNotContainsString('Team Tasks', $content);
        $this->assertStringNotContainsString('Daily Tasks Done (Today)', $content);
        $this->assertStringNotContainsString('Team Tasks Container', $content);
        $this->assertStringNotContainsString('Task Pool', $content);
    }

    public function test_sales_employee_dashboard_does_not_show_tasks(): void
    {
        $salesUser = User::factory()->create([
            'email' => 'rahul_sales@example.com',
            'department' => 'sales',
            'designation' => 'Sales Executive',
            'role' => 'sales',
            'is_active' => true,
        ]);
        $salesUser->assignRbacRole('sales');
        $salesUser->givePermissionTo('dashboard.view');

        $response = $this->actingAs($salesUser)->get('/dashboard');
        $response->assertSuccessful();

        $content = $response->getContent();

        // Must show Workplace metrics
        $this->assertStringContainsString('Working Hours', $content);
        $this->assertStringContainsString('Leave Remaining', $content);
        $this->assertStringContainsString('Attendance', $content);

        // Must NOT show Task Completed or Overdue Tasks containers
        $this->assertStringNotContainsString('Tasks Completed', $content);
        $this->assertStringNotContainsString('Overdue Tasks', $content);
        $this->assertStringNotContainsString('My Assigned Tasks', $content);
        $this->assertStringNotContainsString('Task Pool', $content);
    }
}