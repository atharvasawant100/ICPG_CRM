<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use App\Policies\Concerns\BasePolicy;

class ExpensePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->permittedForModule($user, 'expenses.view');
    }

    public function view(User $user, Expense $expense): bool
    {
        return $this->permittedFor($user, 'expenses.view', $expense, 'expenses');
    }

    public function create(User $user): bool
    {
        return $this->permittedForModule($user, 'expenses.create');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $this->permittedFor($user, 'expenses.edit', $expense, 'expenses');
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $this->permittedFor($user, 'expenses.delete', $expense, 'expenses');
    }

    public function export(User $user): bool
    {
        return $this->permittedForModule($user, 'expenses.export');
    }

    /** Approving someone else's expense requires the approve permission. */
    /**
     * Approve / reject a specific expense (record-level: permission + scope).
     */
    public function approve(User $user, Expense $expense): bool
    {
        return $this->permittedFor($user, 'expenses.approve', $expense, 'expenses');
    }

    /**
     * Approve capability, with no specific record (e.g. the pending list).
     */
    public function approveAny(User $user): bool
    {
        return $this->permittedForModule($user, 'expenses.approve');
    }
}