<?php

namespace Tests\Feature\Rbac;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use App\Rbac\AuditLogger;

/**
 * Audit trail behaviour.
 */
class AuditLogTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_creating_a_customer_writes_an_audit_row(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin);

        $customer = Customer::create([
            'name' => 'Acme',
            'email' => 'acme@example.com',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'create',
            'module' => 'customers',
            'record_id' => $customer->id,
        ]);
    }

    public function test_updating_a_record_records_old_and_new_values(): void
    {
        $admin = $this->admin();

        $customer = Customer::create([
            'name' => 'Acme',
            'email' => 'acme@example.com',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin);
        AuditLog::truncate();

        $customer->update(['name' => 'Acme Renamed']);

        $row = AuditLog::where('module', 'customers')
            ->where('record_id', $customer->id)
            ->where('action', 'update')
            ->firstOrFail();

        $this->assertSame('Acme', $row->old_values['name']);
        $this->assertSame('Acme Renamed', $row->new_values['name']);
    }

    public function test_deleting_a_record_writes_an_audit_row(): void
    {
        $admin = $this->admin();

        $customer = Customer::create([
            'name' => 'Acme',
            'email' => 'acme@example.com',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin);
        AuditLog::truncate();

        $id = $customer->id;
        $customer->delete();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'delete',
            'module' => 'customers',
            'record_id' => $id,
        ]);
    }

    public function test_no_op_update_is_not_audited(): void
    {
        $admin = $this->admin();

        $customer = Customer::create([
            'name' => 'Acme',
            'email' => 'acme@example.com',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin);
        AuditLog::truncate();

        $customer->update(['name' => 'Acme']); // same value

        $this->assertSame(0, AuditLog::where('action', 'update')->count());
    }

    /**
     * All the required modules write audit rows.
     */
    public function test_every_audited_module_writes_rows(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        Customer::create(['name' => 'C', 'email' => 'c@x.com', 'status' => 'active', 'created_by' => $admin->id]);
        Lead::create(['name' => 'L', 'email' => 'l@x.com', 'status' => 'new', 'created_by' => $admin->id]);
        Service::create(['name' => 'S', 'rate' => 100, 'frequency' => 'one-time', 'created_by' => $admin->id]);
        Expense::factory()->create(['user_id' => $admin->id]);

        foreach (['customers', 'leads', 'services', 'expenses'] as $module) {
            $this->assertGreaterThan(
                0,
                AuditLog::where('module', $module)->where('action', 'create')->count(),
                "No audit row for module {$module}."
            );
        }
    }

    /**
     * The critical one: a password change must never land in the trail.
     */
    public function test_passwords_are_never_stored_in_the_audit_log(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $user = User::factory()->create();
        AuditLog::truncate();

        $user->update(['password' => 'super-secret-value']);

        $rows = AuditLog::where('module', 'users')->get();

        foreach ($rows as $row) {
            $serialised = json_encode([$row->old_values, $row->new_values]);

            $this->assertStringNotContainsString(
                'super-secret-value',
                $serialised,
                'The plaintext password leaked into the audit trail.'
            );
            $this->assertStringNotContainsString('$2y$', $serialised, 'A password hash leaked.');
        }
    }

    public function test_remember_token_is_never_stored(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $user = User::factory()->create();
        AuditLog::truncate();

        $user->update(['remember_token' => 'secret-token-abc']);

        $serialised = json_encode(
            AuditLog::where('module', 'users')->get()->map(fn ($r) => [$r->old_values, $r->new_values])->all()
        );

        $this->assertStringNotContainsString('secret-token-abc', $serialised);
    }

    /**
     * The redactor strips secrets even when called directly.
     */
    public function test_redact_removes_secrets_recursively(): void
    {
        $clean = AuditLogger::redact([
            'name' => 'Ada',
            'password' => 'hunter2',
            'nested' => ['remember_token' => 'tok', 'keep' => 'yes'],
        ]);

        $this->assertSame('Ada', $clean['name']);
        $this->assertSame('[redacted]', $clean['password']);
        $this->assertSame('[redacted]', $clean['nested']['remember_token']);
        $this->assertSame('yes', $clean['nested']['keep']);
    }

    public function test_login_and_logout_are_audited(): void
    {
        $admin = $this->admin(['email' => 'audited@iconicpages.com']);

        $this->post('/login', [
            'email' => 'audited@iconicpages.com',
            'password' => 'password',
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login',
            'user_id' => $admin->id,
        ]);

        $this->post('/logout')->assertRedirect(route('login'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'logout',
            'user_id' => $admin->id,
        ]);
    }

    public function test_failed_login_is_audited(): void
    {
        $this->admin();

        $this->from('/login')->post('/login', [
            'email' => 'admin@iconicpages.com',
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'login_failed']);
    }

    /**
     * A role assignment is captured in the trail.
     */
    public function test_role_assignment_is_audited(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $user = User::factory()->create();

        $user->assignRbacRole('sales');

        $this->assertSame('sales', $user->fresh()->rbacRoleName());
        // The users row update is audited, and the mirror is part of it.
        $this->assertGreaterThan(0, AuditLog::where('module', 'users')->count());
    }

    /**
     * IP and user agent are captured.
     */
    public function test_ip_and_user_agent_are_captured(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin);

        Customer::create(['name' => 'C', 'email' => 'c@x.com', 'status' => 'active', 'created_by' => $admin->id]);

        $row = AuditLog::where('module', 'customers')->latest('id')->firstOrFail();

        $this->assertNotNull($row->ip_address);
        $this->assertNotNull($row->user_agent);
    }

    /**
     * A failing audit write must not break the request.
     */
    public function test_audit_failure_does_not_break_the_request(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        // Point the logger at a table that does not exist so the insert throws.
        \Illuminate\Support\Facades\Schema::drop('audit_logs');

        $response = $this->get('/customers');

        $response->assertSuccessful();
    }
}