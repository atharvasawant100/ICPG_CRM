<?php

namespace Tests\Feature\Admin;

use Tests\Feature\Rbac\RbacTestCase;

/**
 * Admin Settings placeholder.
 */
class SettingsModuleTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_admin_can_open_settings(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/settings')
            ->assertSuccessful();
    }

    public function test_it_says_coming_soon(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/settings')
            ->assertSee('Settings coming soon');
    }

    public function test_user_without_permission_gets_403(): void
    {
        $this->actingAs($this->rolelessUser())
            ->get('/admin/settings')
            ->assertForbidden();
    }

    public function test_guest_is_redirected(): void
    {
        $this->get('/admin/settings')->assertRedirect(route('login'));
    }

    public function test_it_uses_the_shared_layout(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/settings');

        // The shared layout renders the sidebar and the Tailwind CDN.
        $response->assertSee('cdn.tailwindcss.com', false);
        $response->assertSee('Logout', false);
    }
}