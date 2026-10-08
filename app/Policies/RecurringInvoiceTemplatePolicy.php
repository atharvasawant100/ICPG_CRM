<?php

namespace App\Policies;

use App\Models\RecurringInvoiceTemplate;
use App\Models\User;
use App\Policies\Concerns\BasePolicy;

class RecurringInvoiceTemplatePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->permittedForModule($user, 'recurring_invoices.view');
    }

    public function view(User $user, RecurringInvoiceTemplate $template): bool
    {
        return $this->permittedFor($user, 'recurring_invoices.view', $template, 'recurring_invoices');
    }

    public function create(User $user): bool
    {
        return $this->permittedForModule($user, 'recurring_invoices.create');
    }

    public function update(User $user, RecurringInvoiceTemplate $template): bool
    {
        return $this->permittedFor($user, 'recurring_invoices.edit', $template, 'recurring_invoices');
    }

    public function delete(User $user, RecurringInvoiceTemplate $template): bool
    {
        return $this->permittedFor($user, 'recurring_invoices.delete', $template, 'recurring_invoices');
    }

    public function export(User $user): bool
    {
        return $this->permittedForModule($user, 'recurring_invoices.export');
    }

    /** Enable / disable a template. */
    public function toggle(User $user, RecurringInvoiceTemplate $template): bool
    {
        return $this->permittedFor($user, 'recurring_invoices.toggle', $template, 'recurring_invoices');
    }
}