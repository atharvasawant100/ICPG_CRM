<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Policies\Concerns\BasePolicy;

class InvoicePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->permittedForModule($user, 'invoices.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->permittedFor($user, 'invoices.view', $invoice, 'invoices');
    }

    public function create(User $user): bool
    {
        return $this->permittedForModule($user, 'invoices.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->permittedFor($user, 'invoices.edit', $invoice, 'invoices');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->permittedFor($user, 'invoices.delete', $invoice, 'invoices');
    }

    /** Bulk export and the single-invoice PDF download. */
    public function export(User $user): bool
    {
        return $this->permittedForModule($user, 'invoices.export');
    }

    public function pdf(User $user, Invoice $invoice): bool
    {
        return $this->permittedFor($user, 'invoices.export', $invoice, 'invoices');
    }

    /** Email the invoice to the customer. Irreversible external side effect. */
    public function send(User $user, Invoice $invoice): bool
    {
        return $this->permittedFor($user, 'invoices.send', $invoice, 'invoices');
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        return $this->permittedFor($user, 'invoices.cancel', $invoice, 'invoices');
    }

    public function recordPayment(User $user, Invoice $invoice): bool
    {
        return $this->permittedFor($user, 'invoices.payments.create', $invoice, 'invoices');
    }

    public function deletePayment(User $user, mixed $payment): bool
    {
        $invoice = $payment->invoice ?? null;

        if ($invoice === null) {
            return $this->permittedForModule($user, 'invoices.payments.delete');
        }

        return $this->permittedFor($user, 'invoices.payments.delete', $invoice, 'invoices');
    }
}