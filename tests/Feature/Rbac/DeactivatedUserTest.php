<?php

namespace Tests\Feature\Rbac;

use App\Models\Lead;
use App\Models\User;
use App\Rbac\Rbac;

/**
 * Deactivated users: cannot sign in, and are ejected mid-session.
 */
class DeactivatedUserTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_active_user_can_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'active@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'active@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        User::factory()->create([
            'email' => 'gone@example.com',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'gone@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->assertDatabaseHas('audit_logs', ['action' => 'login_blocked_inactive']);
    }

    public function test_deactivated_user_is_logged_out_mid_session(): void
    {
        $admin = $this->admin();

        // Session is fine while active.
        $this->actingAs($admin)->get('/leads')->assertSuccessful();

        // Flip the flag directly in the database.
        User::whereKey($admin->id)->update(['is_active' => false]);

        $response = $this->actingAs($admin->fresh())->get('/leads');

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deactivated_access',
            'user_id' => $admin->id,
        ]);
    }

    public function test_deactivated_user_receives_json_403_for_ajax(): void
    {
        $admin = $this->admin();
        User::whereKey($admin->id)->update(['is_active' => false]);

        $response = $this->actingAs($admin->fresh())->getJson('/leads');

        $response->assertForbidden();
        $this->assertStringContainsString('deactivated', $response->content());
    }

    /**
     * Deactivation is checked before permissions, so a deactivated admin cannot
     * rely on Gate::before to slip through.
     */
    public function test_active_check_runs_before_permission_check(): void
    {
        $admin = $this->admin();
        User::whereKey($admin->id)->update(['is_active' => false]);

        // Would be 200 via Gate::before if `active` were not applied first.
        $this->actingAs($admin->fresh())->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_is_active_defaults_to_true_for_new_users(): void
    {
        $user = User::factory()->create();

        $this->assertTrue((bool) $user->fresh()->is_active);
    }
}