<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\EmployeeSalary;
use App\Models\User;
use App\Support\Navigation;
use Tests\Feature\Rbac\RbacTestCase;

class CostingReportTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_admin_can_access_costing_employees_report(): void
    {
        $admin = $this->admin();

        $employee = User::factory()->create([
            'name' => 'John Doe',
            'department' => 'development',
            'designation' => 'Senior Developer',
            'is_active' => true,
        ]);

        EmployeeSalary::create([
            'user_id' => $employee->id,
            'basic_salary' => 60000,
            'hra' => 20000,
            'conveyance_allowance' => 2000,
            'special_allowance' => 3000,
            'other_allowances' => 0,
            'gross_salary' => 85000,
            'provident_fund' => 5000,
            'professional_tax' => 200,
            'tds' => 4000,
            'other_deductions' => 0,
            'total_deductions' => 9200,
            'net_salary' => 75800,
            'payment_method' => 'bank_transfer',
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports/costing');

        $response->assertSuccessful();
        $response->assertSee('Employee Costing');
        $response->assertSee('John Doe');
        $response->assertSee('85,000.00');
    }

    public function test_non_admin_cannot_access_costing_employees_report(): void
    {
        $hrUser = User::factory()->create([
            'department' => 'hr',
            'designation' => 'hr executive',
            'is_active' => true,
        ]);
        $hrUser->assignRbacRole('hr');

        $this->actingAs($hrUser)->get('/admin/reports/costing')->assertForbidden();

        $regularUser = $this->rolelessUser();
        $this->actingAs($regularUser)->get('/admin/reports/costing')->assertForbidden();
    }

    public function test_admin_navigation_hides_operational_modules_and_shows_reports(): void
    {
        $admin = $this->admin();

        // Admin does not have operational payroll, user management, or user creation in navigation
        $this->assertFalse(Navigation::can($admin, 'payroll.view'));
        $this->assertFalse(Navigation::can($admin, 'payroll.create'));
        $this->assertFalse(Navigation::can($admin, 'users.view'));
        $this->assertFalse(Navigation::can($admin, 'users.create'));
        $this->assertFalse(Navigation::can($admin, 'attendance.create'));
        $this->assertFalse(Navigation::can($admin, 'leaves.create'));

        // Admin has Costing Employees, Roles & Permissions, Departments, and Audit Log in navigation
        $this->assertTrue(Navigation::can($admin, 'roles.view'));
        $this->assertTrue(Navigation::can($admin, 'reports.costing'));
        $this->assertTrue(Navigation::can($admin, 'departments.view'));
        $this->assertTrue(Navigation::can($admin, 'audit_logs.view'));

        // Admin navigation hides attendance, leaves, email log, settings
        $this->assertFalse(Navigation::can($admin, 'attendance.view'));
        $this->assertFalse(Navigation::can($admin, 'leaves.view'));
        $this->assertFalse(Navigation::can($admin, 'email_logs.view'));
        $this->assertFalse(Navigation::can($admin, 'settings.view'));

        // Check full Administration section items for Admin
        $sections = Navigation::sections($admin);
        $adminSection = collect($sections)->firstWhere('label', 'Administration');
        $this->assertNotNull($adminSection);

        $labels = collect($adminSection['items'])->pluck('label')->all();

        // In Admin Panel administration section, only Roles & Permissions, Departments, and Audit Log are kept
        $this->assertEquals(['Roles & Permissions', 'Departments', 'Audit Log'], $labels);

        // Costing Employees is grouped in the Accounting section
        $accountingSection = collect($sections)->firstWhere('label', 'Accounting');
        $this->assertNotNull($accountingSection);
        $accountingLabels = collect($accountingSection['items'])->pluck('label')->all();
        $this->assertContains('Costing Employees', $accountingLabels);
    }

    public function test_hr_navigation_retains_operational_payroll_and_user_creation(): void
    {
        $hrUser = User::factory()->create([
            'department' => 'hr',
            'designation' => 'hr executive',
            'is_active' => true,
        ]);
        $hrUser->assignRbacRole('hr');

        // HR has operational permissions
        $this->assertTrue(Navigation::can($hrUser, 'payroll.view'));
        $this->assertTrue(Navigation::can($hrUser, 'payroll.create'));
        $this->assertTrue(Navigation::can($hrUser, 'users.view'));
        $this->assertTrue(Navigation::can($hrUser, 'users.create'));
        $this->assertTrue(Navigation::can($hrUser, 'attendance.create'));

        // HR does NOT have Costing Employees or Roles & Permissions
        $this->assertFalse(Navigation::can($hrUser, 'reports.costing'));
        $this->assertFalse(Navigation::can($hrUser, 'roles.view'));

        // Check full Administration section items for HR
        $sections = Navigation::sections($hrUser);
        $adminSection = collect($sections)->firstWhere('label', 'Administration');
        $this->assertNotNull($adminSection);

        $labels = collect($adminSection['items'])->pluck('label')->all();

        // HR sees Payroll and Users
        $this->assertContains('Payroll', $labels);
        $this->assertContains('Users', $labels);

        // HR does not see Costing Employees or Roles & Permissions
        $this->assertNotContains('Costing Employees', $labels);
        $this->assertNotContains('Roles & Permissions', $labels);

        // HR has All Users and Add User under Users
        $usersItem = collect($adminSection['items'])->firstWhere('label', 'Users');
        $this->assertNotNull($usersItem);
        $userChildren = collect($usersItem['_children'])->pluck('label')->all();
        $this->assertContains('All Users', $userChildren);
        $this->assertContains('Add User', $userChildren);
    }

    public function test_team_module_is_only_accessible_to_department_managers(): void
    {
        $admin = $this->admin();
        $hrUser = User::factory()->create([
            'department' => 'hr',
            'designation' => 'hr executive',
            'is_active' => true,
        ]);
        $hrUser->assignRbacRole('hr');

        $manager = User::factory()->create([
            'department' => 'development',
            'designation' => 'manager',
            'is_active' => true,
        ]);
        $manager->assignRbacRole('manager');

        // Admin cannot see or access Team module
        $this->assertFalse(Navigation::can($admin, 'team.view'));
        $this->actingAs($admin)->get('/team')->assertForbidden();

        // HR cannot see or access Team module
        $this->assertFalse(Navigation::can($hrUser, 'team.view'));
        $this->actingAs($hrUser)->get('/team')->assertForbidden();

        // Department Manager can see and access Team module
        $this->assertTrue(Navigation::can($manager, 'team.view'));
        $teamResponse = $this->actingAs($manager)->get('/team');
        $teamResponse->assertSuccessful();

        // Subordinate in same department
        $subordinate = User::factory()->create([
            'emp_id' => 'ICPG_04',
            'name' => 'Omkar D',
            'department' => 'development',
            'designation' => 'software developer',
            'manager_id' => $manager->id,
            'is_active' => true,
        ]);

        $teamWithSubordinate = $this->actingAs($manager)->get('/team');
        $teamWithSubordinate->assertSuccessful();
        $teamWithSubordinate->assertSee(route('admin.users.show', $subordinate));

        // Direct show route
        $profileResponse = $this->actingAs($manager)->get(route('admin.users.show', $subordinate));
        $profileResponse->assertSuccessful();
        $profileResponse->assertSee('Omkar D');
        $profileResponse->assertSee('ICPG_04');

        // /team/{user} redirect to profile
        $redirectResponse = $this->actingAs($manager)->get(route('team.show', $subordinate));
        $redirectResponse->assertRedirect(route('admin.users.show', $subordinate));
    }
}
