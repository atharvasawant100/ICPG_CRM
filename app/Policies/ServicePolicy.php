<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Policies\Concerns\BasePolicy;

class ServicePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->permittedForModule($user, 'services.view');
    }

    public function view(User $user, Service $service): bool
    {
        return $this->permittedFor($user, 'services.view', $service, 'services');
    }

    public function create(User $user): bool
    {
        return $this->permittedForModule($user, 'services.create');
    }

    public function update(User $user, Service $service): bool
    {
        return $this->permittedFor($user, 'services.edit', $service, 'services');
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->permittedFor($user, 'services.delete', $service, 'services');
    }

    public function export(User $user): bool
    {
        return $this->permittedForModule($user, 'services.export');
    }
}