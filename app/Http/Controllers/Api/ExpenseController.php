<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Expense::class);
        $query = Expense::with(['category', 'user']);

        // Data scope. No-op for the admin.
        $query->applyDataScope($request->user(), 'expenses');

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

        return $this->sendResponse(
            ExpenseResource::collection($expenses)->response()->getData(true),
            'Expenses retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        $this->authorize('create', Expense::class);
        $data = $request->validated();

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            $data['receipt'] = $request->file('receipt')->store('expense-receipts', 'public');
        }

        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';

        $expense = Expense::create($data);

        return $this->sendResponse(new ExpenseResource($expense), 'Expense submitted successfully. Waiting for approval.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->authorize('view', $expense);
        $expense = Expense::find($id);

        if (!$expense) {
            return $this->sendError('Expense not found.');
        }

        $expense->load(['category', 'user', 'approver']);

        return $this->sendResponse(new ExpenseResource($expense), 'Expense retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, $id)
    {
        $this->authorize('update', $expense);
        $expense = Expense::find($id);

        if (!$expense) {
            return $this->sendError('Expense not found.');
        }

        if (!$expense->isPending()) {
            return $this->sendError('Only pending expenses can be edited.', [], 400);
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

        return $this->sendResponse(new ExpenseResource($expense), 'Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorize('delete', $expense);
        $expense = Expense::find($id);
        if (!$expense) {
            return $this->sendError('Expense not found.');
        }

        if (!$expense->isPending()) {
            return $this->sendError('Only pending expenses can be deleted.', [], 400);
        }

        // Delete receipt if exists
        if ($expense->receipt) {
            Storage::disk('public')->delete($expense->receipt);
        }

        $expense->delete();

        return $this->sendResponse([], 'Expense deleted successfully.');
    }

    /**
     * Display pending expenses.
     */
    public function pending(Request $request)
    {
        $this->authorize('approveAny', Expense::class);
        $query = Expense::where('status', 'pending')->with(['category', 'user']);

        // An approver still only sees what their scope allows.
        $query->applyDataScope($request->user(), 'expenses');

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

        return $this->sendResponse(
            ExpenseResource::collection($expenses)->response()->getData(true),
            'Pending expenses retrieved successfully.'
        );
    }

    /**
     * Approve or reject an expense (Admin only).
     */
    public function approve(Request $request, $id)
    {
        $this->authorize('approve', $expense);
        $expense = Expense::find($id);
        if (!$expense) {
            return $this->sendError('Expense not found.');
        }

        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->sendError('Unauthorized action.', [], 403);
        }

        if ($expense->status !== 'pending') {
            return $this->sendError('This expense has already been processed.', [], 400);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|string|max:500',
        ]);

        if ($validated['action'] === 'approve') {
            $expense->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            return $this->sendResponse(new ExpenseResource($expense), 'Expense approved successfully.');
        } else {
            $expense->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            return $this->sendResponse(new ExpenseResource($expense), 'Expense rejected.');
        }
    }
}
