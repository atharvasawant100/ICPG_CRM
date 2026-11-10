<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use App\Policies\Concerns\BasePolicy;

class LeadPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->permittedForModule($user, 'leads.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $this->permittedFor($user, 'leads.view', $lead, 'leads');
    }

    public function create(User $user): bool
    {
        return $this->permittedForModule($user, 'leads.create');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->permittedFor($user, 'leads.edit', $lead, 'leads');
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $this->permittedFor($user, 'leads.delete', $lead, 'leads');
    }

    public function export(User $user): bool
    {
        return $this->permittedForModule($user, 'leads.export');
    }

    /** Bulk CSV/XLSX import. */
    public function import(User $user): bool
    {
        return $this->permittedForModule($user, 'leads.import');
    }

    /** Lead -> customer + invoice conversion. */
    public function convert(User $user, Lead $lead): bool
    {
        return $this->permittedFor($user, 'leads.convert', $lead, 'leads');
    }

    public function addNote(User $user, Lead $lead): bool
    {
        return $this->permittedFor($user, 'leads.notes.create', $lead, 'leads');
    }

    public function deleteNote(User $user, Lead $lead): bool
    {
        return $this->permittedFor($user, 'leads.notes.delete', $lead, 'leads');
    }

    public function reassign(User $user, ?Lead $lead = null): bool
    {
        if ($lead === null) {
            return $this->permittedForModule($user, 'leads.reassign');
        }

        return $this->permittedFor($user, 'leads.reassign', $lead, 'leads');
    }

    public function updateStatus(User $user, Lead $lead): bool
    {
        return $this->permittedFor($user, 'leads.edit', $lead, 'leads');
    }
}