<?php

namespace App\Policies;

use App\Models\ExpenseCategory;
use App\Models\User;
use App\Policies\Concerns\BasePolicy;

class ExpenseCategoryPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->permittedForModule($user, 'expense_categories.view');
    }

    public function view(User $user, ExpenseCategory $category): bool
    {
        return $this->permittedFor($user, 'expense_categories.view', $category, 'expense_categories');
    }

    public function create(User $user): bool
    {
        return $this->permittedForModule($user, 'expense_categories.create');
    }

    public function update(User $user, ExpenseCategory $category): bool
    {
        return $this->permittedFor($user, 'expense_categories.edit', $category, 'expense_categories');
    }

    public function delete(User $user, ExpenseCategory $category): bool
    {
        return $this->permittedFor($user, 'expense_categories.delete', $category, 'expense_categories');
    }
}