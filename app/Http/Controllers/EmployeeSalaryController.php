<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\Concerns\AuthorizesByPermission;
use App\Models\EmployeeSalary;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeSalaryController extends Controller
{
    use AuthorizesByPermission;

    /**
     * List all employees and their salary structure configuration status.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('payroll.view');

        $query = User::query()
            ->with(['salary'])
            ->latest();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('emp_id', 'like', "%{$search}%");
            });
        }

        if ($department = $request->string('department')->trim()->value()) {
            $query->where('department', $department);
        }

        if ($configured = $request->string('configured')->trim()->value()) {
            if ($configured === 'yes') {
                $query->whereHas('salary');
            } elseif ($configured === 'no') {
                $query->whereDoesntHave('salary');
            }
        }

        if ($approvalStatus = $request->string('approval_status')->trim()->value()) {
            $query->whereHas('salary', fn ($q) => $q->where('approval_status', $approvalStatus));
        }

        $users = $query->paginate(15)->withQueryString();

        $configuredCount = EmployeeSalary::count();
        $approvedCount = EmployeeSalary::where('approval_status', 'approved')->count();
        $pendingApprovalCount = EmployeeSalary::where('approval_status', 'pending_approval')->count();
        $totalUsers = User::count();

        return view('payroll.salaries.index', [
            'users' => $users,
            'configuredCount' => $configuredCount,
            'approvedCount' => $approvedCount,
            'pendingApprovalCount' => $pendingApprovalCount,
            'totalUsers' => $totalUsers,
            'departments' => \App\Models\Department::names(activeOnly: false),
        ]);
    }

    /**
     * Show form to configure or edit an employee's salary structure.
     */
    public function edit(User $user)
    {
        $this->authorizePermission('payroll.edit');

        $salary = $user->salary ?? new EmployeeSalary(['user_id' => $user->id]);

        return view('payroll.salaries.edit', [
            'user' => $user,
            'salary' => $salary,
        ]);
    }

    /**
     * Store or update an employee's salary structure.
     */
    public function update(Request $request, User $user)
    {
        $this->authorizePermission('payroll.edit');

        $validated = $request->validate([
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'hra' => ['nullable', 'numeric', 'min:0'],
            'conveyance_allowance' => ['nullable', 'numeric', 'min:0'],
            'special_allowance' => ['nullable', 'numeric', 'min:0'],
            'other_allowances' => ['nullable', 'numeric', 'min:0'],
            'provident_fund' => ['nullable', 'numeric', 'min:0'],
            'professional_tax' => ['nullable', 'numeric', 'min:0'],
            'tds' => ['nullable', 'numeric', 'min:0'],
            'other_deductions' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:bank_transfer,cheque,cash,upi'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'ifsc_code' => ['nullable', 'string', 'max:20'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $numericFields = [
            'basic_salary',
            'hra',
            'conveyance_allowance',
            'special_allowance',
            'other_allowances',
            'provident_fund',
            'professional_tax',
            'tds',
            'other_deductions',
        ];

        foreach ($numericFields as $field) {
            $validated[$field] = isset($validated[$field]) && $validated[$field] !== '' && $validated[$field] !== null
                ? (float) $validated[$field]
                : 0.0;
        }

        $salary = $user->salary ?? new EmployeeSalary(['user_id' => $user->id]);
        $salary->fill($validated);
        $salary->computeTotals();

        $currentUser = $request->user();
        if ($currentUser && $currentUser->isAdmin()) {
            // Admin setting up salary is auto-approved
            $salary->approval_status = 'approved';
            $salary->approved_by = $currentUser->id;
            $salary->approved_at = now();
            $salary->rejection_reason = null;
            $message = "Salary structure updated and approved for {$user->name}.";
        } else {
            // HR setting up salary requires Admin approval
            $salary->approval_status = 'pending_approval';
            $salary->submitted_by = $currentUser?->id;
            $salary->approved_by = null;
            $salary->approved_at = null;
            $salary->rejection_reason = null;
            $message = "Salary structure for {$user->name} has been submitted for Admin approval.";
        }

        $salary->save();

        return redirect()->route('employee-salaries.index')
            ->with('success', $message);
    }
}
