<?php

namespace Tests\Feature\Rbac;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Service;
use App\Models\User;

/**
 * Route-level enforcement.
 */
class RouteProtectionTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    /**
     * Admin must reach every module index and create page.
     */
    public function test_admin_can_open_every_module_index_and_create_page(): void
    {
        $admin = $this->admin();

        $urls = [
            '/dashboard',
            '/customers',
            '/customers/create',
            '/leads',
            '/leads/create',
            '/leads/create/manual',
            '/leads/create/bulk',
            '/expenses',
            '/expenses/create',
            '/services',
            '/services/create',
            '/invoices',
            '/invoices/create',
            '/recurring-invoices',
            '/recurring-invoices/create',
            '/expense-categories',
        ];

        foreach ($urls as $url) {
            $this->actingAs($admin)
                ->get($url)
                ->assertSuccessful();
        }
    }

    /**
     * Admin detail pages still work.
     */
    public function test_admin_can_open_detail_pages(): void
    {
        $admin = $this->admin();

        $customer = Customer::factory()->create(['created_by' => $admin->id]);
        $lead = Lead::factory()->create(['created_by' => $admin->id]);
        $service = Service::factory()->create(['created_by' => $admin->id]);
        $expense = Expense::factory()->create(['user_id' => $admin->id]);

        foreach ([
            "/customers/{$customer->id}",
            "/customers/{$customer->id}/edit",
            "/leads/{$lead->id}",
            "/leads/{$lead->id}/edit",
            "/services/{$service->id}",
            "/services/{$service->id}/edit",
            "/expenses/{$expense->id}",
            "/expenses/{$expense->id}/edit",
            "/leads/calendar",
            "/leads/trash/list",
            "/leads/dashboard/stats",
            "/expenses/pending/list",
            "/expenses/reports/analytics",
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertSuccessful();
        }
    }

    /**
     * A guest is redirected to login, never shown content.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $urls = [
            '/dashboard',
            '/customers',
            '/leads',
            '/expenses',
            '/services',
            '/invoices',
            '/recurring-invoices',
            '/expense-categories',
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    /**
     * Profile, logout and notifications stay open to any authenticated user,
     * even one with zero permissions.
     */
    public function test_profile_and_notifications_are_open_to_any_authenticated_user(): void
    {
        $user = $this->rolelessUser();

        // The seeded admin e-mail already holds the admin role; assert our own
        // roleless user can still reach the shared pages.
        $this->actingAs($user)->get('/profile')->assertSuccessful();
        $this->actingAs($user)->get('/profile/edit')->assertSuccessful();
        $this->actingAs($user)->get('/notifications/poll')->assertSuccessful();
        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));
    }

    /**
     * A user whose role has no permissions is refused on every module.
     */
    public function test_user_without_permissions_gets_403_on_every_module(): void
    {
        $user = $this->userWithPermissions([], 'empty_role');

        $urls = [
            '/dashboard',
            '/customers',
            '/customers/create',
            '/leads',
            '/leads/create',
            '/leads/create/bulk',
            '/expenses',
            '/expenses/create',
            '/expenses/pending/list',
            '/expenses/reports/analytics',
            '/services',
            '/services/create',
            '/invoices',
            '/invoices/create',
            '/recurring-invoices',
            '/recurring-invoices/create',
            '/expense-categories',
        ];

        foreach ($urls as $url) {
            $this->actingAs($user)
                ->get($url)
                ->assertForbidden();
        }
    }

    /**
     * A user with literally no role at all is also refused.
     */
    public function test_user_with_no_role_at_all_gets_403(): void
    {
        $user = $this->rolelessUser();

        foreach (['/dashboard', '/customers', '/leads', '/expenses', '/services', '/invoices'] as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    /**
     * leads.view alone: can list, cannot create or delete.
     */
    public function test_leads_view_only_role_can_list_but_not_create_or_delete(): void
    {
        $user = $this->userWithPermissions(['leads.view'], 'leads_viewer');

        $ownLead = Lead::factory()->create(['created_by' => $user->id]);

        // Can list.
        $this->actingAs($user)->get('/leads')->assertSuccessful();

        // Cannot open the create page.
        $this->actingAs($user)->get('/leads/create')->assertForbidden();
        $this->actingAs($user)->get('/leads/create/manual')->assertForbidden();

        // Cannot bulk import.
        $this->actingAs($user)->get('/leads/create/bulk')->assertForbidden();

        // Cannot delete.
        $this->actingAs($user)
            ->delete("/leads/{$ownLead->id}")
            ->assertForbidden();

        // Cannot edit.
        $this->actingAs($user)->get("/leads/{$ownLead->id}/edit")->assertForbidden();
    }

    /**
     * The new, more granular permissions each gate their own route.
     */
    public function test_granular_permissions_gate_their_own_routes(): void
    {
        $user = $this->userWithPermissions(['leads.view'], 'granular');

        // Bulk import requires leads.import.
        $this->actingAs($user)->get('/leads/create/bulk')->assertForbidden();

        $importer = $this->userWithPermissions(['leads.import'], 'importer');
        $this->actingAs($importer)->get('/leads/create/bulk')->assertSuccessful();

        // Notes require leads.notes.create.
        $lead = Lead::factory()->create(['created_by' => $importer->id]);
        $this->actingAs($user)
            ->post("/leads/{$lead->id}/notes", ['body' => 'hello'])
            ->assertForbidden();

        // Conversion requires leads.convert.
        $this->actingAs($user)
            ->post("/leads/{$lead->id}/convert")
            ->assertForbidden();

        // Follow-up calendar requires followups.view.
        $this->actingAs($user)->get('/leads/calendar')->assertForbidden();

        $followupViewer = $this->userWithPermissions(['followups.view'], 'fu_viewer');
        $this->actingAs($followupViewer)->get('/leads/calendar')->assertSuccessful();
    }

    /**
     * Expense approval is gated by expenses.approve, not expenses.view.
     */
    public function test_expense_approval_requires_approve_permission(): void
    {
        $viewer = $this->userWithPermissions(['expenses.view'], 'exp_viewer');

        $this->actingAs($viewer)->get('/expenses')->assertSuccessful();
        $this->actingAs($viewer)->get('/expenses/pending/list')->assertForbidden();
        $this->actingAs($viewer)->get('/expenses/reports/analytics')->assertForbidden();

        $approver = $this->userWithPermissions(['expenses.approve'], 'exp_approver');
        $this->actingAs($approver)->get('/expenses/pending/list')->assertSuccessful();
    }

    /**
     * JSON clients get a JSON 403, not the HTML page.
     */
    public function test_ajax_request_receives_json_403(): void
    {
        $user = $this->rolelessUser();

        $response = $this->actingAs($user)
            ->getJson('/leads');

        $response->assertForbidden();
        $this->assertJson($response->content());
        $this->assertStringContainsString('message', $response->content());
    }

    /**
     * A non-JSON forbidden request renders the styled 403 page.
     */
    public function test_html_request_receives_styled_403_page(): void
    {
        $user = $this->rolelessUser();

        $response = $this->actingAs($user)->get('/leads');

        $response->assertForbidden();
        $response->assertSee('Access denied');
        $response->assertSee('Back to Dashboard');
    }
}