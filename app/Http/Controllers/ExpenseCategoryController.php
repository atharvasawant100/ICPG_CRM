<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of expense categories.
     */
    public function index()
    {
        $categories = ExpenseCategory::with('creator')
            ->orderBy('is_custom')
            ->orderBy('name')
            ->paginate(20);

        return view('expenses.categories', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        ExpenseCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_custom' => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('expense-categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id,
            'description' => 'nullable|string|max:500',
        ]);

        // Only allow editing custom categories
        if (!$expenseCategory->is_custom) {
            return redirect()->route('expense-categories.index')
                ->with('error', 'Predefined categories cannot be edited.');
        }

        $expenseCategory->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('expense-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(ExpenseCategory $expenseCategory)
    {
        // Only allow deleting custom categories
        if (!$expenseCategory->is_custom) {
            return redirect()->route('expense-categories.index')
                ->with('error', 'Predefined categories cannot be deleted.');
        }

        // Check if category has expenses
        if ($expenseCategory->expenses()->count() > 0) {
            return redirect()->route('expense-categories.index')
                ->with('error', 'Cannot delete category with existing expenses.');
        }

        $expenseCategory->delete();

        return redirect()->route('expense-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
