<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MyExpenseController extends Controller
{
    /**
     * Display personal expense reimbursement requests for the logged-in user.
     */
    public function index(Request $request)
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('expenses.index')->with('info', 'Admin accounts review and manage organization expenses.');
        }

        abort_unless(Gate::allows('my_expenses.view'), 403);

        $user = $request->user();

        $query = Expense::where('user_id', $user->id)
            ->with(['category', 'approver']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by dates
        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        // Search in description or category name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($catQuery) use ($search) {
                        $catQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Summary statistics for this employee
        $allUserExpenses = Expense::where('user_id', $user->id)->get();
        $totalClaimed = $allUserExpenses->sum('amount');
        $totalPending = $allUserExpenses->where('status', 'pending')->sum('amount');
        $totalApproved = $allUserExpenses->where('status', 'approved')->sum('amount');
        $totalRejected = $allUserExpenses->where('status', 'rejected')->sum('amount');

        $countPending = $allUserExpenses->where('status', 'pending')->count();
        $countApproved = $allUserExpenses->where('status', 'approved')->count();
        $countRejected = $allUserExpenses->where('status', 'rejected')->count();

        $expenses = $query->latest('expense_date')->latest('id')->paginate(15);
        $categories = ExpenseCategory::orderBy('name')->get();

        return view('employee.expenses.index', compact(
            'expenses',
            'categories',
            'totalClaimed',
            'totalPending',
            'totalApproved',
            'totalRejected',
            'countPending',
            'countApproved',
            'countRejected'
        ));
    }

    /**
     * Submit a new expense reimbursement request.
     */
    public function store(Request $request)
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('expenses.index')->with('info', 'Admin accounts do not submit personal reimbursement requests.');
        }

        abort_unless(Gate::allows('my_expenses.create'), 403);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:expense_categories,id'],
            'ai_tool' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:1000'],
            'receipt' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf,webp', 'max:10240'],
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('expense-receipts', 'public');
        }

        Expense::create([
            'user_id' => $request->user()->id,
            'category_id' => $validated['category_id'],
            'ai_tool' => $validated['ai_tool'] ?? null,
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'description' => $validated['description'],
            'receipt' => $receiptPath,
            'status' => 'pending',
        ]);

        return redirect()->route('my-expenses.index')
            ->with('success', 'Expense reimbursement request submitted successfully. Waiting for admin approval.');
    }

    /**
     * Cancel an expense reimbursement request (only if still pending).
     */
    public function destroy(Expense $expense, Request $request)
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('expenses.index');
        }

        abort_unless(Gate::allows('my_expenses.create') || Gate::allows('my_expenses.view'), 403);

        if ((int) $expense->user_id !== (int) $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        if (! $expense->isPending()) {
            return redirect()->route('my-expenses.index')
                ->with('error', 'Only pending expense claims can be cancelled.');
        }

        if ($expense->receipt) {
            Storage::disk('public')->delete($expense->receipt);
        }

        $expense->delete();

        return redirect()->route('my-expenses.index')
            ->with('success', 'Expense reimbursement request cancelled successfully.');
    }
}
