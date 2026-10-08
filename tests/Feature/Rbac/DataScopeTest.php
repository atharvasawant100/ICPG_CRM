<?php

namespace Tests\Feature\Rbac;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\User;
use App\Rbac\DataScope;

/**
 * Data scope: own / team / all.
 */
class DataScopeTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    /**
     * Admin resolves to "all" for every module.
     */
    public function test_admin_scope_is_all_for_every_module(): void
    {
        $admin = $this->admin();

        foreach (['customers', 'leads', 'expenses', 'services', 'invoices', 'recurring_invoices', 'followups'] as $module) {
            $this->assertSame(
                DataScope::ALL,
                DataScope::scopeFor($admin, $module),
                "Admin scope for {$module} should be all."
            );
        }
    }

    /**
     * A non-admin defaults to "own".
     */
    public function test_non_admin_defaults_to_own(): void
    {
        $user = $this->userWithPermissions(['leads.view'], 'scoped');

        $this->assertSame(DataScope::OWN, DataScope::scopeFor($user, 'leads'));
        $this->assertSame(DataScope::OWN, DataScope::scopeFor($user, 'invoices'));
    }

    /**
     * Team scope = self + direct reports (same manager_id).
     */
    public function test_team_ids_are_self_plus_direct_reports(): void
    {
        $manager = $this->userWithPermissions(['leads.view'], 'team_scope');
        $reportA = User::factory()->create(['manager_id' => $manager->id]);
        $reportB = User::factory()->create(['manager_id' => $manager->id]);
        $unrelated = User::factory()->create();

        $ids = DataScope::teamIds($manager->fresh());

        $this->assertContains($manager->id, $ids);
        $this->assertContains($reportA->id, $ids);
        $this->assertContains($reportB->id, $ids);
        $this->assertNotContains($unrelated->id, $ids);
    }

    /**
     * With leads.view + own scope, the listing shows only the user's own leads.
     */
    public function test_own_scope_sees_only_own_records_in_listing(): void
    {
        $user = $this->userWithPermissions(['leads.view'], 'scoped');

        $mine = Lead::factory()->create(['created_by' => $user->id]);
        $theirs = Lead::factory()->create();

        $response = $this->actingAs($user)->get('/leads');

        $response->assertSuccessful();
        $response->assertSee($mine->name);
        $response->assertDontSee($theirs->name);
    }

    /**
     * A user cannot open another person's record by guessing the id.
     */
    public function test_own_scope_cannot_open_someone_elses_record_by_id(): void
    {
        $user = $this->userWithPermissions(['leads.view'], 'scoped');

        $mine = Lead::factory()->create(['created_by' => $user->id]);
        $theirs = Lead::factory()->create();

        $this->actingAs($user)->get("/leads/{$mine->id}")->assertSuccessful();
        $this->actingAs($user)->get("/leads/{$theirs->id}")->assertForbidden();
        $this->actingAs($user)->get("/leads/{$theirs->id}/edit")->assertForbidden();
    }

    /**
     * The same protection applies to customers and expenses.
     */
    public function test_scope_is_enforced_per_module(): void
    {
        $user = $this->userWithPermissions(
            ['customers.view', 'expenses.view', 'services.view'],
            'multi_scope'
        );

        $myCustomer = Customer::factory()->create(['created_by' => $user->id]);
        $theirCustomer = Customer::factory()->create();

        $this->actingAs($user)->get("/customers/{$myCustomer->id}")->assertSuccessful();
        $this->actingAs($user)->get("/customers/{$theirCustomer->id}")->assertForbidden();

        $myExpense = Expense::factory()->create(['user_id' => $user->id]);
        $theirExpense = Expense::factory()->create();

        $this->actingAs($user)->get("/expenses/{$myExpense->id}")->assertSuccessful();
        $this->actingAs($user)->get("/expenses/{$theirExpense->id}")->assertForbidden();
    }

    /**
     * Admin sees everything regardless of owner.
     */
    public function test_admin_sees_all_records(): void
    {
        $admin = $this->admin();

        $customer = Customer::factory()->create();
        $lead = Lead::factory()->create();

        $this->actingAs($admin)->get("/customers/{$customer->id}")->assertSuccessful();
        $this->actingAs($admin)->get("/leads/{$lead->id}")->assertSuccessful();
        $this->actingAs($admin)->get('/leads')->assertSee($lead->name);
    }

    /**
     * Team scope lets a manager see their reports' records.
     */
    public function test_team_scope_includes_direct_reports(): void
    {
        config()->set('rbac.scopes.tmp_manager', ['default' => DataScope::TEAM]);

        $manager = $this->userWithPermissions(['leads.view'], 'tmp_manager');
        $report = User::factory()->create(['manager_id' => $manager->id]);

        $reportLead = Lead::factory()->create(['created_by' => $report->id]);
        $strangerLead = Lead::factory()->create();

        $this->assertSame(DataScope::TEAM, DataScope::scopeFor($manager->fresh(), 'leads'));

        $this->actingAs($manager)->get("/leads/{$reportLead->id}")->assertSuccessful();
        $this->actingAs($manager)->get("/leads/{$strangerLead->id}")->assertForbidden();
    }

    /**
     * A record owned by nobody is not exposed below "all" scope, because
     * ownership cannot be proven.
     */
    public function test_unowned_records_are_hidden_from_own_scope(): void
    {
        $user = $this->userWithPermissions(['leads.view'], 'scoped');

        /*
         | Every owner column in this schema is NOT NULL, so no row can be
         | ownerless in practice. The defensive branch is still reachable via
         | an unsaved model, which is what this exercises: an in-memory record
         | with a null owner must not be visible below "all" scope.
         */
        $orphan = new \App\Models\LeadFollowup(['followup_type' => 'call']);
        $orphan->id = 999;

        $this->assertNull($orphan->user_id, 'Sanity: the record has no owner.');

        $this->assertFalse(
            DataScope::allowsRecord($user, $orphan, 'followups'),
            'An ownerless record must not be visible below "all" scope.'
        );

        $this->assertTrue(
            DataScope::allowsRecord($this->admin(), $orphan, 'followups'),
            'Admin scope is all, so an ownerless record is still visible.'
        );
    }

    /**
     * Modules with no owner column are unscoped, so nothing is filtered out.
     */
    public function test_modules_without_owner_column_are_unscoped(): void
    {
        $this->assertNull(DataScope::ownerColumn('settings'));
        $this->assertNull(DataScope::ownerColumn('roles'));
        $this->assertNull(DataScope::ownerColumn('audit_logs'));
    }

    /**
     * Owner columns are the ones that already existed; no new columns.
     */
    public function test_owner_columns_map_to_existing_schema_columns(): void
    {
        $expected = [
            'customers' => 'created_by',
            'leads' => 'created_by',
            'invoices' => 'created_by',
            'services' => 'created_by',
            'recurring_invoices' => 'created_by',
            'expenses' => 'user_id',
            'followups' => 'user_id',
            'expense_categories' => 'created_by',
        ];

        foreach ($expected as $module => $column) {
            $this->assertSame($column, DataScope::ownerColumn($module), "owner column for {$module}");

            $table = DataScope::ownerTable($module);
            $this->assertNotNull($table, "table for {$module}");

            $exists = \Illuminate\Support\Facades\Schema::hasColumn($table, $column);

            $this->assertTrue($exists, "{$table}.{$column} must exist");
        }
    }
}