<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\User;
use App\Rbac\Rbac;
use Tests\Feature\Rbac\RbacTestCase;

/**
 * Admin Audit Log module (read only).
 */
class AuditLogModuleTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_admin_can_open_the_audit_log(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/audit-logs')
            ->assertSuccessful();
    }

    public function test_user_without_permission_gets_403(): void
    {
        $this->actingAs($this->rolelessUser())
            ->get('/admin/audit-logs')
            ->assertForbidden();
    }

    public function test_guest_is_redirected(): void
    {
        $this->get('/admin/audit-logs')->assertRedirect(route('login'));
    }

    public function test_entries_are_listed_newest_first(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        Customer::create(['name' => 'First', 'email' => 'a@x.com', 'status' => 'active', 'created_by' => $admin->id]);
        Customer::create(['name' => 'Second', 'email' => 'b@x.com', 'status' => 'active', 'created_by' => $admin->id]);

        $logs = AuditLog::latest('id')->take(2)->get();

        $response = $this->get('/admin/audit-logs');

        $response->assertSuccessful();

        // The newest entry's id should appear before the older one's.
        $body = $response->getContent();
        $this->assertLessThan(
            strpos($body, 'record-'.$logs[1]->id),
            strpos($body, 'record-'.$logs[0]->id),
            'Entries must be ordered newest first.'
        );
    }

    public function test_can_filter_by_module(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        Customer::create(['name' => 'Cus', 'email' => 'c@x.com', 'status' => 'active', 'created_by' => $admin->id]);

        $this->get('/admin/audit-logs?module=customers')
            ->assertSuccessful()
            ->assertSee('Customers');

        $this->get('/admin/audit-logs?module=nothing_here')
            ->assertSuccessful()
            ->assertSee('No audit entries found.');
    }

    public function test_can_filter_by_action(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $customer = Customer::create(['name' => 'Act', 'email' => 'x@x.com', 'status' => 'active', 'created_by' => $admin->id]);
        $customer->update(['name' => 'Act Changed']);

        $this->get('/admin/audit-logs?action=update')->assertSuccessful();
        $this->get('/admin/audit-logs?action=create')->assertSuccessful();
    }

    public function test_can_filter_by_user(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        Customer::create(['name' => 'Owned', 'email' => 'o@x.com', 'status' => 'active', 'created_by' => $admin->id]);

        $this->get('/admin/audit-logs?user_id='.$admin->id)
            ->assertSuccessful()
            ->assertSee($admin->name);
    }

    public function test_can_filter_by_date_range(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        Customer::create(['name' => 'Dated', 'email' => 'd@x.com', 'status' => 'active', 'created_by' => $admin->id]);

        $today = now()->toDateString();
        $future = now()->addDay()->toDateString();

        $this->get("/admin/audit-logs?date_from={$today}&date_to={$today}")
            ->assertSuccessful()
            ->assertDontSee('No audit entries found.');

        // A range entirely in the future must exclude today's rows.
        $this->get("/admin/audit-logs?date_from={$future}&date_to={$future}")
            ->assertSuccessful()
            ->assertSee('No audit entries found.');
    }

    public function test_no_edit_or_delete_routes_exist(): void
    {
        $routes = collect(\Illuminate\Support\Facades\Route::getRoutes())
            ->filter(fn ($r) => str_starts_with($r->uri(), 'admin/audit-logs'));

        $this->assertCount(1, $routes, 'The audit log must expose exactly one route: the index.');
        $this->assertSame(['GET', 'HEAD'], $routes->first()->methods());
    }

    public function test_old_and_new_values_are_rendered_side_by_side(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $customer = Customer::create([
            'name' => 'Before',
            'email' => 'bn@x.com',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $customer->update(['name' => 'After']);

        $response = $this->get('/admin/audit-logs');

        $response->assertSuccessful();
        $response->assertSee('Show values');
        $response->assertSee('Before');
        $response->assertSee('After');
    }

    public function test_passwords_never_appear_in_the_trail(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $user = User::factory()->create();
        $user->update(['password' => 'top-secret-value']);

        $body = $this->get('/admin/audit-logs')->getContent();

        $this->assertStringNotContainsString('top-secret-value', $body);
        $this->assertStringNotContainsString('$2y$', $body);
    }
}