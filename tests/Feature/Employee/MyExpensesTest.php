<?php

namespace Tests\Feature\Employee;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Rbac\Rbac;
use Database\Seeders\ExpenseCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Feature\Rbac\RbacTestCase;

class MyExpensesTest extends RbacTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
        $this->seed(ExpenseCategorySeeder::class);

        // Ensure permissions exist for the test
        Permission::firstOrCreate(['name' => 'my_expenses.view', 'guard_name' => Rbac::GUARD]);
        Permission::firstOrCreate(['name' => 'my_expenses.create', 'guard_name' => Rbac::GUARD]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createEmployee(string $roleName = 'development'): User
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => Rbac::GUARD]);
        $role->givePermissionTo(['my_expenses.view', 'my_expenses.create', 'dashboard.view']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->assignRbacRole($roleName);

        return $user->fresh();
    }

    private function sidebar(string $html): string
    {
        $start = strpos($html, '<aside');
        $end = $html === '' ? false : strpos($html, '</aside>');

        return ($start !== false && $end !== false) ? substr($html, $start, $end - $start) : '';
    }

    public function test_admin_is_redirected_away_from_my_expenses(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/my-expenses');
        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('info');
    }

    public function test_admin_submitting_my_expenses_is_redirected(): void
    {
        $admin = $this->admin();
        $cat = ExpenseCategory::first();

        $response = $this->actingAs($admin)->post('/my-expenses', [
            'category_id' => $cat->id,
            'amount' => 500,
            'expense_date' => now()->toDateString(),
            'description' => 'Admin test expense',
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseMissing('expenses', [
            'user_id' => $admin->id,
            'description' => 'Admin test expense',
        ]);
    }

    public function test_employee_can_view_my_expenses_page(): void
    {
        $employee = $this->createEmployee();

        $response = $this->actingAs($employee)->get('/my-expenses');

        $response->assertSuccessful();
        $response->assertSee('My Expense Claims');
        $response->assertSee('Request Reimbursement');
    }

    public function test_employee_sees_only_their_own_expenses(): void
    {
        $emp1 = $this->createEmployee('sales');
        $emp2 = $this->createEmployee('development');
        $cat = ExpenseCategory::first();

        $exp1 = Expense::create([
            'user_id' => $emp1->id,
            'category_id' => $cat->id,
            'amount' => 1250.00,
            'expense_date' => now()->toDateString(),
            'description' => 'Client dinner for Emp1',
            'status' => 'pending',
        ]);

        $exp2 = Expense::create([
            'user_id' => $emp2->id,
            'category_id' => $cat->id,
            'amount' => 999.00,
            'expense_date' => now()->toDateString(),
            'description' => 'Software book for Emp2',
            'status' => 'pending',
        ]);

        $resp1 = $this->actingAs($emp1)->get('/my-expenses');
        $resp1->assertSuccessful();
        $resp1->assertSee('Client dinner for Emp1');
        $resp1->assertDontSee('Software book for Emp2');

        $resp2 = $this->actingAs($emp2)->get('/my-expenses');
        $resp2->assertSuccessful();
        $resp2->assertSee('Software book for Emp2');
        $resp2->assertDontSee('Client dinner for Emp1');
    }

    public function test_employee_can_submit_expense_reimbursement_request(): void
    {
        Storage::fake('public');
        $employee = $this->createEmployee();
        $cat = ExpenseCategory::first();

        $file = UploadedFile::fake()->create('receipt.pdf', 500, 'application/pdf');

        $response = $this->actingAs($employee)->post('/my-expenses', [
            'category_id' => $cat->id,
            'amount' => 3450.50,
            'expense_date' => now()->toDateString(),
            'description' => 'Flight tickets to client HQ',
            'receipt' => $file,
        ]);

        $response->assertRedirect(route('my-expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'user_id' => $employee->id,
            'category_id' => $cat->id,
            'amount' => 3450.50,
            'description' => 'Flight tickets to client HQ',
            'status' => 'pending',
        ]);

        $expense = Expense::where('user_id', $employee->id)->first();
        $this->assertNotNull($expense->receipt);
        Storage::disk('public')->assertExists($expense->receipt);
    }

    public function test_admin_can_approve_employee_expense(): void
    {
        $admin = $this->admin();
        $employee = $this->createEmployee();
        $cat = ExpenseCategory::first();

        $expense = Expense::create([
            'user_id' => $employee->id,
            'category_id' => $cat->id,
            'amount' => 1500,
            'expense_date' => now()->toDateString(),
            'description' => 'Office supplies claim',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->put(route('expenses.approve', $expense), [
            'action' => 'approve',
        ]);

        $response->assertRedirect(route('expenses.show', $expense));
        $response->assertSessionHas('success');

        $expense->refresh();
        $this->assertTrue($expense->isApproved());
        $this->assertSame($admin->id, $expense->approved_by);
        $this->assertNotNull($expense->approved_at);

        // Employee now sees Approved on their portal
        $empView = $this->actingAs($employee)->get('/my-expenses');
        $empView->assertSuccessful();
        $empView->assertSee('Approved');
    }

    public function test_admin_can_reject_employee_expense_with_reason(): void
    {
        $admin = $this->admin();
        $employee = $this->createEmployee();
        $cat = ExpenseCategory::first();

        $expense = Expense::create([
            'user_id' => $employee->id,
            'category_id' => $cat->id,
            'amount' => 4500,
            'expense_date' => now()->toDateString(),
            'description' => 'Unapproved hotel stay',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->put(route('expenses.approve', $expense), [
            'action' => 'reject',
            'rejection_reason' => 'Hotel was not pre-authorized by department policy.',
        ]);

        $response->assertRedirect(route('expenses.show', $expense));
        $response->assertSessionHas('success');

        $expense->refresh();
        $this->assertTrue($expense->isRejected());
        $this->assertSame('Hotel was not pre-authorized by department policy.', $expense->rejection_reason);

        // Employee sees Rejected status and reason on their portal
        $empView = $this->actingAs($employee)->get('/my-expenses');
        $empView->assertSuccessful();
        $empView->assertSee('Rejected');
        $empView->assertSee('Hotel was not pre-authorized by department policy.');
    }

    public function test_employee_can_cancel_pending_expense(): void
    {
        $employee = $this->createEmployee();
        $cat = ExpenseCategory::first();

        $expense = Expense::create([
            'user_id' => $employee->id,
            'category_id' => $cat->id,
            'amount' => 200,
            'expense_date' => now()->toDateString(),
            'description' => 'Mistake claim',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($employee)->delete(route('my-expenses.destroy', $expense));

        $response->assertRedirect(route('my-expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_employee_cannot_cancel_approved_or_another_users_expense(): void
    {
        $emp1 = $this->createEmployee('sales');
        $emp2 = $this->createEmployee('marketing');
        $cat = ExpenseCategory::first();

        $approvedExpense = Expense::create([
            'user_id' => $emp1->id,
            'category_id' => $cat->id,
            'amount' => 600,
            'expense_date' => now()->toDateString(),
            'description' => 'Approved lunch',
            'status' => 'approved',
        ]);

        // Emp1 cannot delete approved
        $resp1 = $this->actingAs($emp1)->delete(route('my-expenses.destroy', $approvedExpense));
        $resp1->assertRedirect(route('my-expenses.index'));
        $resp1->assertSessionHas('error');
        $this->assertDatabaseHas('expenses', ['id' => $approvedExpense->id]);

        // Emp2 cannot delete Emp1's expense
        $resp2 = $this->actingAs($emp2)->delete(route('my-expenses.destroy', $approvedExpense));
        $resp2->assertForbidden();
    }

    public function test_sidebar_includes_my_expenses_for_employee_and_excludes_for_admin(): void
    {
        $admin = $this->admin();
        $emp = $this->createEmployee();

        // Admin sidebar
        $adminHtml = $this->actingAs($admin)->get('/dashboard')->getContent();
        $adminNav = $this->sidebar($adminHtml);
        $this->assertStringNotContainsString('My Expenses', $adminNav);
        $this->assertStringContainsString('Expenses', $adminNav); // Admin has organization expenses module

        // Employee sidebar
        $empHtml = $this->actingAs($emp)->get('/dashboard')->getContent();
        $empNav = $this->sidebar($empHtml);
        $this->assertStringContainsString('My Expenses', $empNav);
    }

    public function test_employee_can_submit_ai_tool_expense(): void
    {
        $employee = $this->createEmployee();
        $aiCat = ExpenseCategory::where('name', 'AI tools')->first();

        $response = $this->actingAs($employee)->post('/my-expenses', [
            'category_id' => $aiCat->id,
            'ai_tool' => 'Claude Pro',
            'amount' => 1999.00,
            'expense_date' => now()->toDateString(),
            'description' => 'Claude Pro monthly subscription for coding assistance',
        ]);

        $response->assertRedirect(route('my-expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'user_id' => $employee->id,
            'category_id' => $aiCat->id,
            'ai_tool' => 'Claude Pro',
            'amount' => 1999.00,
        ]);

        $resp = $this->actingAs($employee)->get('/my-expenses');
        $resp->assertSuccessful();
        $resp->assertSee('Claude Pro');
    }
}
