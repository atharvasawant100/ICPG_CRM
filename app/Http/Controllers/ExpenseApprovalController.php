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
        // CHANGED from: if (!$user->isAdmin()) abort(403)
        // Now expressed as the expenses.approve permission plus the record's
        // data scope, so a Manager granted approve can use it without being an
        // admin. Admin behaviour is unchanged via Gate::before.
        $this->authorize('approve', $expense);

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
