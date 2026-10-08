<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'user']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $expenses = $query->latest('expense_date')->paginate(15);
        $categories = ExpenseCategory::orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'categories'));
    }

    /**
     * Display pending expenses.
     */
    public function pending(Request $request)
    {
        $query = Expense::where('status', 'pending')->with(['category', 'user']);

        // Apply filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $expenses = $query->latest('expense_date')->paginate(15);
        $categories = ExpenseCategory::orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'categories'))->with('title', 'Pending Expenses');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        $data = $request->validated();

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            $data['receipt'] = $request->file('receipt')->store('expense-receipts', 'public');
        }

        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';

        Expense::create($data);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense submitted successfully. Waiting for approval.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        $expense->load(['category', 'user', 'approver']);

        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        if (!$expense->isPending()) {
            return redirect()->route('expenses.show', $expense)
                ->with('error', 'Only pending expenses can be edited.');
        }

        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        if (!$expense->isPending()) {
            return redirect()->route('expenses.show', $expense)
                ->with('error', 'Only pending expenses can be edited.');
        }

        $data = $request->validated();

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            // Delete old receipt if exists
            if ($expense->receipt) {
                Storage::disk('public')->delete($expense->receipt);
            }
            $data['receipt'] = $request->file('receipt')->store('expense-receipts', 'public');
        }

        $expense->update($data);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        if (!$expense->isPending()) {
            return redirect()->route('expenses.index')
                ->with('error', 'Only pending expenses can be deleted.');
        }

        // Delete receipt if exists
        if ($expense->receipt) {
            Storage::disk('public')->delete($expense->receipt);
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    /**
     * Display expense reports with charts (Admin only).
     */
    public function reports(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Get date range filters
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->endOfMonth()->toDateString());

        // Expenses by category
        $expensesByCategory = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get();

        // Expenses by status
        $expensesByStatus = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->selectRaw('status, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Monthly trends
        $monthlyTrends = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->selectRaw('DATE_FORMAT(expense_date, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Total statistics
        $totalExpenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount');
        $totalPending = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->where('status', 'pending')->sum('amount');
        $totalApproved = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->where('status', 'approved')->sum('amount');
        $totalRejected = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->where('status', 'rejected')->sum('amount');

        return view('expenses.reports', compact(
            'expensesByCategory',
            'expensesByStatus',
            'monthlyTrends',
            'totalExpenses',
            'totalPending',
            'totalApproved',
            'totalRejected',
            'dateFrom',
            'dateTo'
        ));
    }
}
