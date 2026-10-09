<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\User;
use App\Rbac\Rbac;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Tests\Feature\Rbac\RbacTestCase;

/**
 * The Departments module: list, add, and assign a manager.
 *
 * Also covers the knock-on effect of making the department list table-driven:
 * a department added here must immediately become assignable on the Users page.
 */
class DepartmentTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    /**
     * An active user holding a role that may manage a department.
     */
    private function managerCandidate(string $role = 'manager', array $attributes = []): User
    {
        $user = User::factory()->create(array_merge(['is_active' => true], $attributes));
        $user->assignRbacRole($role);

        return $user->fresh();
    }

    // ---- Listing ----------------------------------------------------------

    public function test_admin_sees_the_department_list(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.departments.index'));

        $response->assertSuccessful();
        $response->assertSee('Departments');

        // The four departments that predate this table are seeded, so every
        // existing user's department still resolves.
        foreach (['Sales', 'Marketing', 'Development', 'Hr'] as $name) {
            $response->assertSee($name);
        }
    }

    public function test_admin_sees_departments_in_the_sidebar(): void
    {
        $nav = $this->sidebarHtml(
            $this->actingAs($this->admin())->get('/dashboard')->getContent()
        );

        $this->assertStringContainsString('Departments', $nav);
        $this->assertStringContainsString(route('admin.departments.index'), $nav);
    }

    public function test_the_list_shows_the_manager_and_headcount(): void
    {
        $manager = $this->managerCandidate();

        Department::where('name', 'sales')->update(['manager_id' => $manager->id]);

        $response = $this->actingAs($this->admin())->get(route('admin.departments.index'));

        $response->assertSuccessful();
        $response->assertSee($manager->name);
        $response->assertSee($manager->email);
    }

    public function test_a_user_without_the_permission_is_refused(): void
    {
        $user = $this->userWithPermissions(['users.view'], 'no_departments');

        $this->actingAs($user)->get(route('admin.departments.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.departments.store'), ['name' => 'x'])->assertForbidden();
    }

    public function test_the_sidebar_hides_departments_without_the_permission(): void
    {
        $user = $this->userWithPermissions(['users.view', 'dashboard.view'], 'no_departments');

        $nav = $this->sidebarHtml($this->actingAs($user)->get('/dashboard')->getContent());

        $this->assertStringNotContainsString(route('admin.departments.index'), $nav);
    }

    // ---- Creating ---------------------------------------------------------

    public function test_admin_can_add_a_department_with_a_manager(): void
    {
        $manager = $this->managerCandidate();

        $response = $this->actingAs($this->admin())->post(route('admin.departments.store'), [
            'name' => 'Finance',
            'description' => 'Billing and accounts',
            'manager_id' => $manager->id,
        ]);

        $response->assertRedirect(route('admin.departments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'name' => 'finance',
            'manager_id' => $manager->id,
        ]);
    }

    public function test_the_name_is_normalised_so_case_never_creates_a_duplicate(): void
    {
        $this->actingAs($this->admin())->post(route('admin.departments.store'), [
            'name' => '  Finance   Team  ',
        ])->assertRedirect(route('admin.departments.index'));

        $this->assertDatabaseHas('departments', ['name' => 'finance team']);
    }

    public function test_a_duplicate_name_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.departments.store'), ['name' => 'Sales'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Department::where('name', 'sales')->count());
    }

    public function test_the_name_is_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.departments.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_a_department_can_be_added_without_a_manager(): void
    {
        $this->actingAs($this->admin())->post(route('admin.departments.store'), [
            'name' => 'support',
        ])->assertRedirect(route('admin.departments.index'));

        $this->assertDatabaseHas('departments', [
            'name' => 'support',
            'manager_id' => null,
        ]);
    }

    /**
     * The manager list must only offer people who could actually manage.
     */
    public function test_the_manager_dropdown_only_lists_active_admins_and_managers(): void
    {
        $eligibleManager = $this->managerCandidate('manager');
        $eligibleAdmin = $this->managerCandidate(Rbac::ADMIN_ROLE);
        $ineligibleEmployee = $this->managerCandidate('sales');
        $inactiveManager = $this->managerCandidate('manager', ['is_active' => false]);

        $candidates = Department::managerCandidates()->pluck('id');

        $this->assertTrue($candidates->contains($eligibleManager->id));
        $this->assertTrue($candidates->contains($eligibleAdmin->id));
        $this->assertFalse($candidates->contains($ineligibleEmployee->id));
        $this->assertFalse($candidates->contains($inactiveManager->id));

        // And the same list reaches the form.
        $response = $this->actingAs($this->admin())->get(route('admin.departments.index'));

        $response->assertSuccessful();
        $response->assertSee('Add Department');
        $response->assertSee($eligibleManager->email);
        $response->assertDontSee($ineligibleEmployee->email);
        $response->assertDontSee($inactiveManager->email);
    }

    public function test_an_ineligible_manager_is_rejected_even_if_forged(): void
    {
        $employee = $this->managerCandidate('sales');

        $this->actingAs($this->admin())
            ->post(route('admin.departments.store'), [
                'name' => 'finance',
                'manager_id' => $employee->id,
            ])
            ->assertSessionHasErrors('manager_id');

        $this->assertDatabaseMissing('departments', ['name' => 'finance']);
    }

    public function test_an_inactive_manager_is_rejected(): void
    {
        $inactive = $this->managerCandidate('manager', ['is_active' => false]);

        $this->actingAs($this->admin())
            ->post(route('admin.departments.store'), [
                'name' => 'finance',
                'manager_id' => $inactive->id,
            ])
            ->assertSessionHasErrors('manager_id');
    }

    public function test_viewing_alone_does_not_allow_creating(): void
    {
        $viewer = $this->userWithPermissions(['departments.view'], 'dept_viewer');

        $this->actingAs($viewer)->get(route('admin.departments.index'))->assertSuccessful();

        $this->actingAs($viewer)
            ->post(route('admin.departments.store'), ['name' => 'finance'])
            ->assertForbidden();

        $this->assertDatabaseMissing('departments', ['name' => 'finance']);
    }

    // ---- Changing the manager --------------------------------------------

    public function test_admin_can_change_a_departments_manager(): void
    {
        $old = $this->managerCandidate('manager');
        $new = $this->managerCandidate('manager');

        $department = Department::where('name', 'sales')->first();
        $department->update(['manager_id' => $old->id]);

        $this->actingAs($this->admin())
            ->put(route('admin.departments.update', $department), ['manager_id' => $new->id])
            ->assertRedirect(route('admin.departments.index'));

        $this->assertSame($new->id, $department->fresh()->manager_id);
    }

    public function test_the_manager_can_be_cleared(): void
    {
        $manager = $this->managerCandidate();
        $department = Department::where('name', 'sales')->first();
        $department->update(['manager_id' => $manager->id]);

        $this->actingAs($this->admin())
            ->put(route('admin.departments.update', $department), ['manager_id' => ''])
            ->assertRedirect(route('admin.departments.index'));

        $this->assertNull($department->fresh()->manager_id);
    }

    public function test_changing_the_manager_requires_the_edit_permission(): void
    {
        $department = Department::where('name', 'sales')->first();
        $manager = $this->managerCandidate();

        $viewer = $this->userWithPermissions(['departments.view'], 'dept_viewer');

        $this->actingAs($viewer)
            ->put(route('admin.departments.update', $department), ['manager_id' => $manager->id])
            ->assertForbidden();

        $this->assertNull($department->fresh()->manager_id);
    }

    public function test_a_department_can_be_deactivated_without_losing_its_users(): void
    {
        $user = User::factory()->create(['department' => 'sales']);

        $department = Department::where('name', 'sales')->first();

        $this->actingAs($this->admin())
            ->put(route('admin.departments.update', $department), [
                'manager_id' => '',
                'is_active' => '0',
            ])
            ->assertRedirect(route('admin.departments.index'));

        $this->assertFalse($department->fresh()->is_active);

        // The user's stored department string is untouched.
        $this->assertSame('sales', $user->fresh()->department);
    }

    // ---- Knock-on effect on the Users form -------------------------------

    /**
     * A department created here must be assignable on the Users page straight
     * away. This is the reason the list is table-driven rather than a constant.
     */
    public function test_a_new_department_becomes_assignable_to_a_user(): void
    {
        Department::create(['name' => 'finance']);

        $this->actingAs($this->admin())->post(route('admin.users.store'), [
            'name' => 'Finance Admin',
            'email' => 'finance@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'sales',
            'department' => 'finance',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'finance@example.com',
            'department' => 'finance',
        ]);
    }

    public function test_an_unknown_department_is_still_rejected_on_the_user_form(): void
    {
        $this->actingAs($this->admin())->post(route('admin.users.store'), [
            'name' => 'Nobody',
            'email' => 'nobody@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'sales',
            'department' => 'not-a-department',
        ])->assertSessionHasErrors('department');
    }

    public function test_the_user_form_dropdown_lists_new_departments(): void
    {
        Department::create(['name' => 'finance']);

        $response = $this->actingAs($this->admin())->get(route('admin.users.create'));

        $response->assertSuccessful();

        $departments = $this->selectOptions($response->getContent(), 'department');

        $this->assertStringContainsString('value="finance"', $departments);
    }

    public function test_an_inactive_department_is_not_offered_for_new_users(): void
    {
        Department::where('name', 'hr')->update(['is_active' => false]);

        $response = $this->actingAs($this->admin())->get(route('admin.users.create'));

        $response->assertSuccessful();

        // Scoped to the department select: "hr" is also a role name, so a
        // whole-page search would find the role dropdown.
        $departments = $this->selectOptions($response->getContent(), 'department');

        $this->assertStringNotContainsString('value="hr"', $departments);
        $this->assertStringContainsString('value="sales"', $departments);
    }

    // ---- Seeding ----------------------------------------------------------

    public function test_reseeding_never_overwrites_a_manager_an_admin_assigned(): void
    {
        $manager = $this->managerCandidate();

        Department::where('name', 'sales')->update(['manager_id' => $manager->id]);

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertSame($manager->id, Department::where('name', 'sales')->first()->manager_id);
    }

    public function test_reseeding_never_duplicates_a_department(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->assertSame(1, Department::where('name', 'sales')->count());
    }

    // ---- Helpers ----------------------------------------------------------

    private function sidebarHtml(string $html): string
    {
        $start = strpos($html, '<aside');
        $end = strpos($html, '</aside>');

        return ($start !== false && $end !== false) ? substr($html, $start, $end - $start) : '';
    }

    /**
     * The markup inside a named <select>, so an assertion cannot accidentally
     * match a same-named value in a different dropdown.
     */
    private function selectOptions(string $html, string $name): string
    {
        $open = '<select name="'.$name.'"';

        if (! str_contains($html, $open)) {
            // The tag carries attributes across lines; fall back to a looser match.
            preg_match('/<select\b[^>]*\bname="'.preg_quote($name, '/').'"[^>]*>(.*?)<\/select>/s', $html, $m);

            return $m[1] ?? '';
        }

        $start = strpos($html, $open);
        $end = strpos($html, '</select>', $start);

        return $end === false ? '' : substr($html, $start, $end - $start);
    }
}