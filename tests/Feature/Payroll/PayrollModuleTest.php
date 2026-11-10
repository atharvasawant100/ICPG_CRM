<?php

namespace Tests\Feature\Payroll;

use App\Models\EmployeeSalary;
use App\Models\Payroll;
use App\Models\User;
use Tests\Feature\Rbac\RbacTestCase;

class PayrollModuleTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/payrolls')->assertRedirect(route('login'));
        $this->get('/employee-salaries')->assertRedirect(route('login'));
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $user = $this->rolelessUser();

        $this->actingAs($user)->get('/payrolls')->assertForbidden();
        $this->actingAs($user)->get('/payrolls/create')->assertForbidden();
        $this->actingAs($user)->get('/employee-salaries')->assertForbidden();
    }

    public function test_admin_can_open_payroll_index_and_create(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/payrolls')->assertSuccessful();
        $this->actingAs($admin)->get('/payrolls/create')->assertSuccessful();
        $this->actingAs($admin)->get('/employee-salaries')->assertSuccessful();
    }

    public function test_admin_can_configure_employee_salary(): void
    {
        $admin = $this->admin();
        $emp = User::factory()->create(['name' => 'John Doe', 'emp_id' => 'ICPG_05']);

        $response = $this->actingAs($admin)->put("/employee-salaries/{$emp->id}", [
            'basic_salary' => 50000,
            'hra' => 20000,
            'conveyance_allowance' => 3000,
            'special_allowance' => 5000,
            'other_allowances' => 2000,
            'provident_fund' => 6000,
            'professional_tax' => 200,
            'tds' => 4000,
            'other_deductions' => 800,
            'payment_method' => 'bank_transfer',
            'bank_name' => 'HDFC Bank',
            'account_number' => '50100987654321',
            'ifsc_code' => 'HDFC0001234',
        ]);

        $response->assertRedirect(route('employee-salaries.index'));

        $salary = EmployeeSalary::where('user_id', $emp->id)->firstOrFail();
        $this->assertEquals(50000, (float) $salary->basic_salary);
        // Gross: 50000 + 20000 + 3000 + 5000 + 2000 = 80000
        $this->assertEquals(80000, (float) $salary->gross_salary);
        // Deductions: 6000 + 200 + 4000 + 800 = 11000
        $this->assertEquals(11000, (float) $salary->total_deductions);
        // Net: 80000 - 11000 = 69000
        $this->assertEquals(69000, (float) $salary->net_salary);
    }

    public function test_admin_can_generate_payroll_and_view_payslip(): void
    {
        $admin = $this->admin();
        $emp = User::factory()->create(['name' => 'Jane Smith', 'emp_id' => 'ICPG_06']);

        // Set salary
        EmployeeSalary::create([
            'user_id' => $emp->id,
            'basic_salary' => 40000,
            'hra' => 15000,
            'conveyance_allowance' => 2000,
            'special_allowance' => 3000,
            'other_allowances' => 0,
            'provident_fund' => 4800,
            'professional_tax' => 200,
            'tds' => 2000,
            'other_deductions' => 0,
            'gross_salary' => 60000,
            'total_deductions' => 7000,
            'net_salary' => 53000,
            'payment_method' => 'bank_transfer',
        ]);

        $response = $this->actingAs($admin)->post('/payrolls', [
            'payroll_year' => 2026,
            'payroll_month' => 10,
            'user_ids' => [$emp->id],
            'bonus' => [$emp->id => 5000],
            'overtime_pay' => [$emp->id => 1000],
            'payment_status' => 'pending',
        ]);

        $response->assertRedirect(route('payrolls.index', ['year' => 2026, 'month' => 10]));

        $payroll = Payroll::where('user_id', $emp->id)
            ->where('payroll_year', 2026)
            ->where('payroll_month', 10)
            ->firstOrFail();

        // Gross: 40000 (basic) + 15000 (hra) + 5000 (allowances) + 5000 (bonus) + 1000 (overtime) = 66000
        $this->assertEquals(66000, (float) $payroll->gross_salary);
        // Deductions: 4800 + 200 + 2000 = 7000
        $this->assertEquals(7000, (float) $payroll->total_deductions);
        // Net: 66000 - 7000 = 59000
        $this->assertEquals(59000, (float) $payroll->net_salary);
        $this->assertStringContainsString('ICPG06', $payroll->payroll_code);

        // View payslip
        $this->actingAs($admin)->get("/payrolls/{$payroll->id}")
            ->assertSuccessful()
            ->assertSee('Jane Smith')
            ->assertSee('ICPG_06')
            ->assertSee('October 2026')
            ->assertSee('59,000.00');
    }

    public function test_admin_can_mark_payroll_as_paid(): void
    {
        $admin = $this->admin();
        $emp = User::factory()->create(['emp_id' => 'ICPG_07']);

        $payroll = Payroll::create([
            'user_id' => $emp->id,
            'payroll_month' => 10,
            'payroll_year' => 2026,
            'payroll_code' => 'PAY-202610-ICPG07',
            'basic_salary' => 30000,
            'gross_salary' => 30000,
            'total_deductions' => 0,
            'net_salary' => 30000,
            'payment_status' => 'pending',
        ]);

        $this->actingAs($admin)->post("/payrolls/{$payroll->id}/pay", [
            'payment_date' => '2026-10-15',
            'transaction_reference' => 'TXN12345678',
        ])->assertRedirect();

        $payroll->refresh();
        $this->assertSame('paid', $payroll->payment_status);
        $this->assertEquals('2026-10-15', $payroll->payment_date->toDateString());
        $this->assertSame('TXN12345678', $payroll->transaction_reference);
    }

    public function test_hr_setting_up_salary_requires_admin_approval(): void
    {
        $hrUser = User::factory()->create([
            'department' => 'hr',
            'designation' => 'hr executive',
            'is_active' => true,
        ]);
        $hrUser->assignRbacRole('hr');

        $emp = User::factory()->create(['name' => 'Alice Tech', 'emp_id' => 'ICPG_08']);

        $response = $this->actingAs($hrUser)->put("/employee-salaries/{$emp->id}", [
            'basic_salary' => 45000,
            'hra' => 15000,
            'conveyance_allowance' => 2000,
            'special_allowance' => 3000,
            'other_allowances' => 0,
            'provident_fund' => 5400,
            'professional_tax' => 200,
            'tds' => 2500,
            'other_deductions' => 0,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertRedirect(route('employee-salaries.index'));
        $response->assertSessionHas('success');
        $this->assertStringContainsString('submitted for Admin approval', session('success'));

        $salary = EmployeeSalary::where('user_id', $emp->id)->firstOrFail();
        $this->assertSame('pending_approval', $salary->approval_status);
        $this->assertTrue($salary->isPendingApproval());
        $this->assertFalse($salary->isApproved());
        $this->assertEquals($hrUser->id, $salary->submitted_by);
        $this->assertNull($salary->approved_by);
        $this->assertNull($salary->approved_at);
    }

    public function test_admin_setting_up_salary_is_auto_approved(): void
    {
        $admin = $this->admin();
        $emp = User::factory()->create(['name' => 'Bob Dev', 'emp_id' => 'ICPG_09']);

        $response = $this->actingAs($admin)->put("/employee-salaries/{$emp->id}", [
            'basic_salary' => 60000,
            'hra' => 20000,
            'conveyance_allowance' => 3000,
            'special_allowance' => 5000,
            'other_allowances' => 0,
            'provident_fund' => 7200,
            'professional_tax' => 200,
            'tds' => 4000,
            'other_deductions' => 0,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertRedirect(route('employee-salaries.index'));
        $response->assertSessionHas('success');
        $this->assertStringContainsString('approved', session('success'));

        $salary = EmployeeSalary::where('user_id', $emp->id)->firstOrFail();
        $this->assertSame('approved', $salary->approval_status);
        $this->assertTrue($salary->isApproved());
        $this->assertEquals($admin->id, $salary->approved_by);
        $this->assertNotNull($salary->approved_at);
    }

    public function test_admin_can_approve_pending_salary_structure(): void
    {
        $admin = $this->admin();
        $hrUser = User::factory()->create(['department' => 'hr', 'designation' => 'hr executive']);
        $hrUser->assignRbacRole('hr');

        $emp = User::factory()->create(['name' => 'Charlie Ops', 'emp_id' => 'ICPG_10']);

        $salary = EmployeeSalary::create([
            'user_id' => $emp->id,
            'basic_salary' => 50000,
            'gross_salary' => 65000,
            'total_deductions' => 6000,
            'net_salary' => 59000,
            'approval_status' => 'pending_approval',
            'submitted_by' => $hrUser->id,
            'payment_method' => 'bank_transfer',
        ]);

        $this->assertFalse($salary->isApproved());

        $response = $this->actingAs($admin)->post("/admin/employee-salaries/{$salary->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $salary->refresh();
        $this->assertSame('approved', $salary->approval_status);
        $this->assertTrue($salary->isApproved());
        $this->assertEquals($admin->id, $salary->approved_by);
        $this->assertNotNull($salary->approved_at);
        $this->assertNull($salary->rejection_reason);
    }

    public function test_admin_can_reject_pending_salary_with_reason(): void
    {
        $admin = $this->admin();
        $emp = User::factory()->create(['name' => 'Dana Sales', 'emp_id' => 'ICPG_11']);

        $salary = EmployeeSalary::create([
            'user_id' => $emp->id,
            'basic_salary' => 80000,
            'gross_salary' => 120000,
            'total_deductions' => 10000,
            'net_salary' => 110000,
            'approval_status' => 'pending_approval',
            'payment_method' => 'bank_transfer',
        ]);

        $response = $this->actingAs($admin)->post("/admin/employee-salaries/{$salary->id}/reject", [
            'rejection_reason' => 'Gross salary exceeds department budget guidelines.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $salary->refresh();
        $this->assertSame('rejected', $salary->approval_status);
        $this->assertTrue($salary->isRejected());
        $this->assertFalse($salary->isApproved());
        $this->assertSame('Gross salary exceeds department budget guidelines.', $salary->rejection_reason);
    }

    public function test_non_admin_cannot_approve_or_reject_salaries(): void
    {
        $hrUser = User::factory()->create(['department' => 'hr', 'designation' => 'hr executive']);
        $hrUser->assignRbacRole('hr');

        $emp = User::factory()->create(['name' => 'Eve Dev', 'emp_id' => 'ICPG_12']);
        $salary = EmployeeSalary::create([
            'user_id' => $emp->id,
            'basic_salary' => 50000,
            'gross_salary' => 65000,
            'total_deductions' => 5000,
            'net_salary' => 60000,
            'approval_status' => 'pending_approval',
            'payment_method' => 'bank_transfer',
        ]);

        $this->actingAs($hrUser)->post("/admin/employee-salaries/{$salary->id}/approve")->assertForbidden();
        $this->actingAs($hrUser)->post("/admin/employee-salaries/{$salary->id}/reject")->assertForbidden();

        $salary->refresh();
        $this->assertSame('pending_approval', $salary->approval_status);
    }

    public function test_unapproved_salaries_are_excluded_from_payroll_generation(): void
    {
        $hrUser = User::factory()->create(['department' => 'hr', 'designation' => 'hr executive']);
        $hrUser->assignRbacRole('hr');

        $empApproved = User::factory()->create(['name' => 'Approved Emp', 'emp_id' => 'ICPG_13']);
        $empPending = User::factory()->create(['name' => 'Pending Emp', 'emp_id' => 'ICPG_14']);
        $empRejected = User::factory()->create(['name' => 'Rejected Emp', 'emp_id' => 'ICPG_15']);

        // Approved salary
        EmployeeSalary::create([
            'user_id' => $empApproved->id,
            'basic_salary' => 50000,
            'gross_salary' => 50000,
            'total_deductions' => 0,
            'net_salary' => 50000,
            'approval_status' => 'approved',
            'payment_method' => 'bank_transfer',
        ]);

        // Pending salary
        EmployeeSalary::create([
            'user_id' => $empPending->id,
            'basic_salary' => 45000,
            'gross_salary' => 45000,
            'total_deductions' => 0,
            'net_salary' => 45000,
            'approval_status' => 'pending_approval',
            'payment_method' => 'bank_transfer',
        ]);

        // Rejected salary
        EmployeeSalary::create([
            'user_id' => $empRejected->id,
            'basic_salary' => 40000,
            'gross_salary' => 40000,
            'total_deductions' => 0,
            'net_salary' => 40000,
            'approval_status' => 'rejected',
            'payment_method' => 'bank_transfer',
        ]);

        // HR attempts to generate payroll for all three
        $response = $this->actingAs($hrUser)->post('/payrolls', [
            'payroll_year' => 2026,
            'payroll_month' => 11,
            'user_ids' => [$empApproved->id, $empPending->id, $empRejected->id],
            'payment_status' => 'pending',
        ]);

        $response->assertRedirect();

        // Only the approved employee should have a payroll record created
        $this->assertDatabaseHas('payrolls', [
            'user_id' => $empApproved->id,
            'payroll_year' => 2026,
            'payroll_month' => 11,
        ]);

        $this->assertDatabaseMissing('payrolls', [
            'user_id' => $empPending->id,
            'payroll_year' => 2026,
            'payroll_month' => 11,
        ]);

        $this->assertDatabaseMissing('payrolls', [
            'user_id' => $empRejected->id,
            'payroll_year' => 2026,
            'payroll_month' => 11,
        ]);
    }

    public function test_salary_can_be_saved_with_empty_or_null_optional_allowances_and_deductions(): void
    {
        $hrUser = User::factory()->create(['department' => 'hr', 'designation' => 'hr executive']);
        $hrUser->assignRbacRole('hr');

        $emp = User::factory()->create(['name' => 'Akshata Sawant', 'emp_id' => 'ICPG_07']);

        // Submit form with only basic_salary and payment_method, leaving all other allowances/deductions null
        $response = $this->actingAs($hrUser)->put("/employee-salaries/{$emp->id}", [
            'basic_salary' => 20000,
            'hra' => null,
            'conveyance_allowance' => null,
            'special_allowance' => null,
            'other_allowances' => null,
            'provident_fund' => null,
            'professional_tax' => null,
            'tds' => null,
            'other_deductions' => null,
            'payment_method' => 'bank_transfer',
            'bank_name' => 'HDFC',
            'account_number' => '06156484158456',
            'ifsc_code' => 'HDFC0000001',
            'pan_number' => 'ENIUN7845J',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('employee-salaries.index'));

        $salary = EmployeeSalary::where('user_id', $emp->id)->firstOrFail();
        $this->assertEquals(20000, (float) $salary->basic_salary);
        $this->assertEquals(0, (float) $salary->conveyance_allowance);
        $this->assertEquals(0, (float) $salary->hra);
        $this->assertEquals(20000, (float) $salary->gross_salary);
        $this->assertEquals(0, (float) $salary->total_deductions);
        $this->assertEquals(20000, (float) $salary->net_salary);
        $this->assertSame('pending_approval', $salary->approval_status);
    }

    public function test_admin_can_download_payslip_pdf(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['name' => 'PDF Test Employee', 'emp_id' => 'ICPG_99']);
        $payroll = Payroll::create([
            'user_id' => $user->id,
            'payroll_month' => 10,
            'payroll_year' => 2026,
            'payroll_code' => 'PAY-202610-PDFTEST',
            'basic_salary' => 25000,
            'gross_salary' => 25000,
            'total_deductions' => 0,
            'net_salary' => 25000,
            'payment_status' => 'paid',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('payrolls.pdf', $payroll));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
