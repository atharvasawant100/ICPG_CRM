<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Policies\Concerns\BasePolicy;

class CustomerPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->permittedForModule($user, 'customers.view');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->permittedFor($user, 'customers.view', $customer, 'customers');
    }

    public function create(User $user): bool
    {
        return $this->permittedForModule($user, 'customers.create');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->permittedFor($user, 'customers.edit', $customer, 'customers');
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->permittedFor($user, 'customers.delete', $customer, 'customers');
    }

    public function export(User $user): bool
    {
        return $this->permittedForModule($user, 'customers.export');
    }
}