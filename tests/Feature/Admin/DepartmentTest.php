<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\User;
use App\Rbac\Rbac;
use Database\Seeders\RolePermissionSeeder;
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

    public function test_hr_user_sees_departments_section_with_cards_and_employees(): void
    {
        $hrUser = User::factory()->create([
            'name' => 'Katrina HR',
            'department' => 'hr',
            'designation' => 'hr executive',
        ]);
        $hrUser->assignRbacRole('hr');

        $devEmp = User::factory()->create([
            'name' => 'Alex Developer',
            'department' => 'development',
            'emp_id' => 'ICPG_20',
        ]);

        $response = $this->actingAs($hrUser)->get(route('admin.departments.index'));
        $response->assertSuccessful();
        $response->assertSee('Departments');
        $response->assertSee('Department Cards');
        $response->assertSee('Employees Directory');
        $response->assertSee('Development');
        $response->assertSee('Alex Developer');
        $response->assertSee('ICPG_20');

        // Verify Departments link appears in sidebar for HR
        $nav = $this->sidebarHtml($response->getContent());
        $this->assertStringContainsString('Departments', $nav);
        $this->assertStringContainsString(route('admin.departments.index'), $nav);
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

        $department = Department::where('name', 'finance')->firstOrFail();

        $response->assertRedirect(route('admin.departments.show', $department));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'name' => 'finance',
            'manager_id' => $manager->id,
        ]);
    }

    public function test_the_name_is_normalised_so_case_never_creates_a_duplicate(): void
    {
        $manager = $this->managerCandidate();

        $this->actingAs($this->admin())->post(route('admin.departments.store'), [
            'name' => '  Finance   Team  ',
            'manager_id' => $manager->id,
        ])->assertRedirect(route('admin.departments.show', Department::where('name', 'finance team')->firstOrFail()));

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
        $manager = $this->managerCandidate();

        $this->actingAs($this->admin())
            ->post(route('admin.departments.store'), ['name' => '', 'manager_id' => $manager->id])
            ->assertSessionHasErrors('name');
    }

    /**
     * Every department must have a manager, so omitting one is refused rather
     * than silently creating a manager-less row.
     */
    public function test_a_department_cannot_be_added_without_a_manager(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.departments.store'), ['name' => 'support'])
            ->assertSessionHasErrors('manager_id');

        $this->assertDatabaseMissing('departments', ['name' => 'support']);
    }

    public function test_an_empty_manager_id_is_refused_on_create(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.departments.store'), ['name' => 'support', 'manager_id' => ''])
            ->assertSessionHasErrors('manager_id');

        $this->assertDatabaseMissing('departments', ['name' => 'support']);
    }

    /**
     * Same rule on edit: a department must never be left manager-less, even if
     * the admin is only changing the description.
     */
    public function test_a_department_cannot_be_saved_without_a_manager(): void
    {
        $manager = $this->managerCandidate();
        $department = Department::where('name', 'sales')->first();

        $this->actingAs($this->admin())
            ->put(route('admin.departments.update', $department), [
                'manager_id' => '',
                'description' => 'Changed',
            ])
            ->assertSessionHasErrors('manager_id');

        $this->assertNull($department->fresh()->description);
    }

    /**
     * The manager dropdowns must not offer "no manager", otherwise the UI would
     * contradict the validation.
     */
    public function test_the_dropdowns_do_not_offer_a_no_manager_option(): void
    {
        $manager = $this->managerCandidate('manager');
        $department = Department::where('name', 'sales')->firstOrFail();
        $department->update(['manager_id' => $manager->id]);

        // Create page, and the edit page.
        $create = $this->actingAs($this->admin())->get(route('admin.departments.create'))->getContent();
        $edit = $this->actingAs($this->admin())->get(route('admin.departments.edit', $department))->getContent();

        $this->assertNotEmpty($this->managerSelects($create));
        $this->assertNotEmpty($this->managerSelects($edit));

        foreach (array_merge($this->managerSelects($create), $this->managerSelects($edit)) as $select) {
            $this->assertStringContainsString('required', $select);
            $this->assertStringNotContainsString('>No manager', $select);
        }
    }

    public function test_admin_can_open_the_create_page(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.departments.create'));

        $response->assertSuccessful();
        $response->assertSee('Add New Department');
        $response->assertSee('Designations (Job Titles)');
        $response->assertSee('Department Manager');
    }

    public function test_create_page_requires_permission(): void
    {
        $user = $this->userWithPermissions(['departments.view'], 'view_only');

        $this->actingAs($user)
            ->get(route('admin.departments.create'))
            ->assertForbidden();
    }

    /**
     * A department with no manager is flagged on the list, and the edit page
     * does not silently pre-select the first person in the dropdown.
     */
public function test_a_department_with_no_manager_is_flagged(): void
    {
        $department = Department::where('name', 'hr')->firstOrFail();

        $this->assertNull($department->manager_id);

        $response = $this->actingAs($this->admin())->get(route('admin.departments.index'));

        $response->assertSuccessful();
        $response->assertSee('No manager');
    }

    /**
     * With a manager set, the edit page preselects them and offers no
     * placeholder, so saving is a no-op rather than a reassignment.
     */
public function test_the_edit_page_preselects_the_current_manager(): void
    {
        $manager = $this->managerCandidate('manager');
        $department = Department::where('name', 'sales')->firstOrFail();
        $department->update(['manager_id' => $manager->id]);

        $select = $this->managerSelects(
            $this->actingAs($this->admin())->get(route('admin.departments.edit', $department))->getContent()
        )[0];

        $this->assertStringNotContainsString('Choose a manager', $select);

        // Blade puts the attributes on their own lines, so allow whitespace.
        $this->assertMatchesRegularExpression(
            '/<option\s+value="'.$manager->id.'"\s+selected/',
            $select
        );
    }

    /**
     * Picking a manager for one of the pre-existing departments fills it in.
     */
    public function test_an_existing_department_can_be_given_its_first_manager(): void
    {
        $manager = $this->managerCandidate();
        $department = Department::where('name', 'hr')->first();

        $this->assertNull($department->manager_id);

        $this->actingAs($this->admin())
            ->put(route('admin.departments.update', $department), ['manager_id' => $manager->id])
            ->assertRedirect(route('admin.departments.show', $department));

        $this->assertSame($manager->id, $department->fresh()->manager_id);
    }

    /**
     * With nobody eligible, the page says so instead of showing an empty
     * dropdown the admin cannot satisfy.
     */
    public function test_the_page_explains_when_there_is_nobody_to_choose_as_manager(): void
    {
        // The seeded admin is the only admin/manager user. Deactivating it
        // empties the candidate list.
        User::where('email', RolePermissionSeeder::ADMIN_EMAILS[0])
            ->update(['is_active' => false]);

        /*
         | Someone who can create a department but is not themselves a manager
         | candidate. An acting admin would always be a candidate, so the
         | warning could never reach them.
         */
        $creator = $this->userWithPermissions(['departments.view', 'departments.create'], 'dept_creator');

        $this->assertCount(0, Department::managerCandidates());

        $response = $this->actingAs($creator)->get(route('admin.departments.index'));

        $response->assertSuccessful();
        $response->assertSee('No active user holds the admin or manager role');
    }

    /**
     * The warning is only shown to someone who can create, because it is about
     * the create form. A read-only visitor does not need it.
     */
public function test_the_empty_manager_warning_is_hidden_from_read_only_visitors(): void
    {
        User::where('email', RolePermissionSeeder::ADMIN_EMAILS[0])
            ->update(['is_active' => false]);

        $viewer = $this->userWithPermissions(['departments.view'], 'dept_viewer');

        $this->assertCount(0, Department::managerCandidates());

        $this->actingAs($viewer)
            ->get(route('admin.departments.index'))
            ->assertDontSee('No active user holds the admin or manager role');
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

        // And the candidate list reaches the dedicated create page form.
        $createResponse = $this->actingAs($this->admin())->get(route('admin.departments.create'));

        $createResponse->assertSuccessful();
        $createResponse->assertSee('Add New Department');
        $createResponse->assertSee($eligibleManager->email);
        $createResponse->assertDontSee($ineligibleEmployee->email);
        $createResponse->assertDontSee($inactiveManager->email);
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
            ->assertRedirect(route('admin.departments.show', $department));

        $this->assertSame($new->id, $department->fresh()->manager_id);
    }

    public function test_the_manager_cannot_be_cleared(): void
    {
        $manager = $this->managerCandidate();
        $replacement = $this->managerCandidate();
        $department = Department::where('name', 'sales')->first();
        $department->update(['manager_id' => $manager->id]);

        // Clearing is refused: a department must always have a manager, so the
        // manager is replaced rather than removed.
        $this->actingAs($this->admin())
            ->put(route('admin.departments.update', $department), ['manager_id' => ''])
            ->assertSessionHasErrors('manager_id');

        $this->assertSame($manager->id, $department->fresh()->manager_id);

        // Replacing it works.
        $this->actingAs($this->admin())
            ->put(route('admin.departments.update', $department), ['manager_id' => $replacement->id])
            ->assertRedirect(route('admin.departments.show', $department));

        $this->assertSame($replacement->id, $department->fresh()->manager_id);
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
        $manager = $this->managerCandidate();

        $department = Department::where('name', 'sales')->first();

        $this->actingAs($this->admin())
            ->put(route('admin.departments.update', $department), [
                'manager_id' => $manager->id,
                'is_active' => '0',
            ])
            ->assertRedirect(route('admin.departments.show', $department));

        $this->assertFalse($department->fresh()->is_active);

        // The user's stored department string is untouched.
        $this->assertSame('sales', $user->fresh()->department);
    }

    /**
     * Deactivating a department must not orphan its users either: the string
     * they store still has to validate, or they could not be saved again.
     */
    public function test_a_deactivated_department_stays_assignable_to_its_existing_users(): void
    {
        $user = User::factory()->create(['department' => 'hr']);

        Department::where('name', 'hr')->update(['is_active' => false]);

        $this->actingAs($this->admin())->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'hr',
            'department' => 'hr',
        ])->assertSessionHasNoErrors();
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

    // ---- Detail page: the team ----------------------------------------------

public function test_the_detail_page_lists_the_department_team(): void
    {
        $manager = $this->managerCandidate('manager');
        Department::where('name', 'sales')->update(['manager_id' => $manager->id]);

        User::factory()->create([
            'name' => 'Team Member',
            'email' => 'member@example.com',
            'department' => 'sales',
            'designation' => 'intern',
        ])->assignRbacRole('sales');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.departments.show', Department::where('name', 'sales')->firstOrFail()));

        $response->assertSuccessful();
        $response->assertSee('Team');
        $response->assertSee('Team Member');
        $response->assertSee('member@example.com');
        $response->assertSee('Intern');
        $response->assertSee('Sales');
    }

    public function test_the_team_excludes_users_of_other_departments(): void
    {
        User::factory()->create([
            'name' => 'Marketing Person',
            'email' => 'marketer@example.com',
            'department' => 'marketing',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.departments.show', Department::where('name', 'sales')->firstOrFail()));

        $response->assertSuccessful();
        $response->assertDontSee('marketer@example.com');
    }

    public function test_the_team_counts_active_and_deactivated_members(): void
    {
        User::factory()->create(['department' => 'hr', 'is_active' => true]);
        User::factory()->create(['department' => 'hr', 'is_active' => false]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.departments.show', Department::where('name', 'hr')->firstOrFail()));

        $response->assertSuccessful();

        // Blade breaks the sentence across lines, so assert the fragments.
        $response->assertSee('1 active');
        $response->assertSee('1 deactivated');
    }

    public function test_the_detail_page_says_when_there_is_no_team(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.departments.show', Department::where('name', 'sales')->firstOrFail()));

        $response->assertSuccessful();
        $response->assertSee('Nobody is assigned to this');
    }

    public function test_the_detail_page_requires_the_view_permission(): void
    {
        $user = $this->userWithPermissions(['users.view'], 'no_departments');
        $department = Department::where('name', 'sales')->firstOrFail();

        $this->actingAs($user)->get(route('admin.departments.show', $department))->assertForbidden();
    }

    // ---- Edit page --------------------------------------------------------

public function test_the_edit_page_shows_the_current_manager(): void
    {
        $manager = $this->managerCandidate('manager');
        $department = Department::where('name', 'sales')->firstOrFail();
        $department->update(['manager_id' => $manager->id]);

        $response = $this->actingAs($this->admin())->get(route('admin.departments.edit', $department));

        $response->assertSuccessful();
        $response->assertSee($manager->email);
    }

    public function test_the_edit_page_requires_the_edit_permission(): void
    {
        $viewer = $this->userWithPermissions(['departments.view'], 'dept_viewer');
        $department = Department::where('name', 'sales')->firstOrFail();

        $this->actingAs($viewer)->get(route('admin.departments.edit', $department))->assertForbidden();
    }

    /**
     * The index no longer carries a Manager column or inline dropdowns, which
     * is what made it hard to read.
     */
    public function test_the_index_has_no_manager_column_or_inline_dropdown(): void
    {
        $manager = $this->managerCandidate('manager');
        Department::where('name', 'sales')->update(['manager_id' => $manager->id]);

        $html = $this->actingAs($this->admin())->get(route('admin.departments.index'))->getContent();

        // Scoped to the table: the create panel legitimately offers this
        // manager in its dropdown, which is not the removed column.
        $table = substr($html, strpos($html, '<tbody'), strpos($html, '</tbody>') - strpos($html, '<tbody'));

        $this->assertStringNotContainsString($manager->email, $table);
        $this->assertStringNotContainsString('MANAGER</th>', strtoupper($table));
        $this->assertStringNotContainsString('Save', $table);

        // Zero dropdowns on index: the create form is now on its own separate page.
        $this->assertCount(0, $this->managerSelects($html));
    }

    public function test_the_index_links_to_view_and_edit_for_each_department(): void
    {
        $department = Department::where('name', 'sales')->firstOrFail();

        $response = $this->actingAs($this->admin())->get(route('admin.departments.index'));

        $response->assertSuccessful();
        $response->assertSee(route('admin.departments.show', $department), false);
        $response->assertSee(route('admin.departments.edit', $department), false);
        $response->assertSee('View');
        $response->assertSee('Edit');
    }

    public function test_the_index_hides_edit_without_the_edit_permission(): void
    {
        $viewer = $this->userWithPermissions(['departments.view'], 'dept_viewer');
        $department = Department::where('name', 'sales')->firstOrFail();

        $response = $this->actingAs($viewer)->get(route('admin.departments.index'));

        $response->assertSuccessful();
        $response->assertSee(route('admin.departments.show', $department), false);
        $response->assertDontSee(route('admin.departments.edit', $department), false);
    }

    public function test_the_edit_page_does_not_allow_renaming(): void
    {
        $manager = $this->managerCandidate('manager');
        $department = Department::where('name', 'sales')->firstOrFail();

        $this->actingAs($this->admin())
            ->put(route('admin.departments.update', $department), [
                'name' => 'renamed',
                'manager_id' => $manager->id,
            ])
            ->assertRedirect(route('admin.departments.show', $department));

        $this->assertSame('sales', $department->fresh()->name);
    }

    /**
     * Creating a department automatically creates its Spatie role and designations,
     * which then appears in the Roles & Permissions dashboard.
     */
    public function test_creating_department_creates_spatie_role_and_designations(): void
    {
        $manager = $this->managerCandidate();

        $response = $this->actingAs($this->admin())->post(route('admin.departments.store'), [
            'name' => 'Quality Assurance',
            'description' => 'Software testing and QA',
            'manager_id' => $manager->id,
            'designations' => 'Manager, QA Lead, QA Engineer, QA Intern',
            'is_active' => '1',
        ]);

        $role = \Spatie\Permission\Models\Role::where('name', 'quality assurance')->first();
        $dept = Department::where('name', 'quality assurance')->first();
        $this->assertNotNull($dept);
        $response->assertRedirect(route('admin.departments.show', $dept));

        // Check designations
        $designations = $dept->parsedDesignations();
        $this->assertArrayHasKey('manager', $designations);
        $this->assertArrayHasKey('qa lead', $designations);
        $this->assertArrayHasKey('qa engineer', $designations);
        $this->assertArrayHasKey('qa intern', $designations);

        // Check Spatie role was created
        $role = \Spatie\Permission\Models\Role::where('name', 'quality assurance')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('dashboard.view'));
        $this->assertTrue($role->hasPermissionTo('my_attendance.view'));

        // Check role appears on the Roles & Permissions dashboard
        $rolesResponse = $this->actingAs($this->admin())->get(route('admin.roles.index'));
        $rolesResponse->assertSuccessful();
        $rolesResponse->assertSee('Quality assurance');

        // Check user creation with this department and regular designation gets the department role
        $empRole = \App\Rbac\Designation::roleFor('quality assurance', 'qa engineer');
        $this->assertSame('quality assurance', $empRole);

        // Check user creation with manager designation gets the manager role
        $mgrRole = \App\Rbac\Designation::roleFor('quality assurance', 'manager');
        $this->assertSame('manager', $mgrRole);
    }

    // ---- Helpers ----------------------------------------------------------

    private function sidebarHtml(string $html): string
    {
        $start = strpos($html, '<aside');
        $end = strpos($html, '</aside>');

        return ($start !== false && $end !== false) ? substr($html, $start, $end - $start) : '';
    }

    /**
     * The markup of every <select name="manager_id"> on the page.
     *
     * @return array<int, string>
     */
    private function managerSelects(string $html): array
    {
        preg_match_all(
            '/<select\b[^>]*name="manager_id"[^>]*>.*?<\/select>/s',
            $html,
            $matches
        );

        return $matches[0] ?? [];
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
