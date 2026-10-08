<?php

namespace Tests\Feature\Rbac;

use App\Models\User;
use App\Rbac\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Guards the invariant that users.role is a faithful read-only mirror of the
 * Spatie role.
 *
 * These tests fail the moment the two sources of truth disagree, which is the
 * whole point of writing the mirror in exactly one place.
 */
class RoleMirrorIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The seeder refuses to create users, so the admin must already exist —
        // exactly as it does in the real database.
        User::firstOrCreate(
            ['email' => RolePermissionSeeder::ADMIN_EMAILS[0]],
            ['name' => 'Admin', 'password' => bcrypt('password'), 'role' => 'admin'],
        );

        $this->seed(RolePermissionSeeder::class);
    }

    protected function assertMirrorIsConsistent(User $user, string $context): void
    {
        $user->refresh();

        $this->assertSame(
            $user->rbacRoleName(),
            $user->getAttribute('role'),
            "users.role mirror drifted from Spatie: {$context}"
        );
    }

    /**
     * Sanity helper: confirm drift genuinely exists, so a "repair" test cannot
     * pass simply because nothing was ever broken.
     */
    protected function assertMirrorHasDrifted(User $user, string $context): void
    {
        $user->refresh();

        $this->assertNotSame(
            $user->rbacRoleName(),
            $user->getAttribute('role'),
            "Test setup failed: expected drift {$context}, but the mirror already agrees."
        );
    }

    public function test_mirror_matches_after_assigning_a_role(): void
    {
        $user = User::factory()->create();

        $user->assignRbacRole('manager');

        $this->assertSame('manager', $user->fresh()->getAttribute('role'));
        $this->assertMirrorIsConsistent($user, 'after assignRbacRole(manager)');
    }

    public function test_mirror_matches_after_assigning_admin(): void
    {
        $user = User::factory()->create();

        $user->assignRbacRole(Rbac::ADMIN_ROLE);

        $this->assertSame(Rbac::ADMIN_ROLE, $user->fresh()->getAttribute('role'));
        $this->assertTrue($user->fresh()->isAdmin());
    }

    public function test_mirror_is_cleared_when_roles_are_cleared(): void
    {
        $user = User::factory()->create();
        $user->assignRbacRole('hr');

        $user->clearRbacRoles();

        $this->assertNull($user->fresh()->getAttribute('role'));
        $this->assertMirrorIsConsistent($user, 'after clearRbacRoles()');
    }

    /**
     * The factory must not set users.role directly, otherwise every created
     * user would arrive with drift.
     */
public function test_mirror_is_consistent_for_every_seeded_user(): void
{
    $users = User::factory()->count(3)->create();

    foreach ($users as $user) {
        $this->assertNull($user->fresh()->rbacRoleName(), 'Factory must not create a role.');
        $this->assertMirrorIsConsistent($user, 'freshly created user');
    }
}

    public function test_isAdmin_agrees_with_spatie_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRbacRole(Rbac::ADMIN_ROLE);

        $nonAdmin = User::factory()->create();
        $nonAdmin->assignRbacRole('sales');

        $this->assertTrue($admin->fresh()->isAdmin());
        $this->assertFalse($nonAdmin->fresh()->isAdmin());
    }

    /**
     * Writing users.role directly bypasses Spatie and must be detectable.
     *
     * This documents that direct writes are the failure mode the mirror test
     * exists to catch.
     */
    public function test_detects_drift_when_legacy_column_is_written_directly(): void
    {
        $user = User::factory()->create();
        $user->assignRbacRole('development');

        // Simulate a rogue direct write to the mirror.
        User::whereKey($user->id)->update(['role' => 'marketing']);

        $this->assertMirrorHasDrifted($user, 'after a direct write');
    }

    /**
     * The seeder repairs a drifted mirror from the Spatie side of truth.
     */
    public function test_seeder_repairs_a_drifted_mirror(): void
    {
        $user = User::factory()->create();
        $user->assignRbacRole('marketing');

        User::whereKey($user->id)->update(['role' => null]);

        $this->assertMirrorHasDrifted($user, 'before repair');

        $this->seed(RolePermissionSeeder::class);

        $this->assertSame('marketing', $user->fresh()->getAttribute('role'));
        $this->assertMirrorIsConsistent($user, 'after re-seeding');
    }

    /**
     * assignRbacRole is the single write path: calling Spatie directly is what
     * would cause drift, so assert the wrapper is what keeps them aligned.
     */
    public function test_wrapper_is_the_single_write_path(): void
    {
        $user = User::factory()->create();

        $user->assignRbacRole('sales', syncLegacyColumn: false);

        $this->assertSame('sales', $user->fresh()->rbacRoleName());
        $this->assertNull(
            $user->fresh()->getAttribute('role'),
            'Opting out of the mirror is explicit and must not be the default.'
        );

        // Repair via the normal path.
        $user->assignRbacRole('sales');

        $this->assertMirrorIsConsistent($user, 'after repairing via wrapper');
    }

    /**
     * Legacy values with no mapping are not silently promoted to a real role.
     */
    public function test_unmapped_legacy_value_is_not_promoted(): void
    {
        $user = User::factory()->create();

        // 'employee' was a legacy enum value that Rbac::legacyRoleMap()
        // deliberately does not map.
        User::whereKey($user->id)->update(['role' => 'employee']);

        $this->seed(RolePermissionSeeder::class);

        $this->assertNull(
            $user->fresh()->rbacRoleName(),
            'An unmapped legacy value must not be assigned a Spatie role.'
        );
        $this->assertFalse($user->fresh()->isAdmin());
    }

    public function test_legacy_admin_value_is_migrated_to_spatie(): void
    {
        $user = User::factory()->create();

        // Simulate a pre-RBAC user: legacy column set, no Spatie rows.
        User::whereKey($user->id)->update(['role' => 'admin']);

        $this->assertNull($user->fresh()->rbacRoleName());

        $this->seed(RolePermissionSeeder::class);

        $this->assertTrue($user->fresh()->hasRole(Rbac::ADMIN_ROLE));
        $this->assertTrue($user->fresh()->isAdmin());
    }
}