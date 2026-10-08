<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveExpenseRequest;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class ExpenseApprovalController extends Controller
{
    /**
     * Approve or reject an expense.
     */
    public function update(ApproveExpenseRequest $request, Expense $expense)
    {
        $user = Auth::user();
        
        // Only admin can approve/reject
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if ($expense->status !== 'pending') {
            return redirect()->route('expenses.show', $expense)
                ->with('error', 'This expense has already been processed.');
        }

        if ($request->action === 'approve') {
            $expense->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            return redirect()->route('expenses.show', $expense)
                ->with('success', 'Expense approved successfully.');
        } else {
            $request->validate([
                'rejection_reason' => 'required|string|max:500',
            ]);

            $expense->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            return redirect()->route('expenses.show', $expense)
                ->with('success', 'Expense rejected.');
        }
    }
}
