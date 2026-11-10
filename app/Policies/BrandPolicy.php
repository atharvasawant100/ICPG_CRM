<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;
use App\Policies\Concerns\BasePolicy;

class BrandPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->permitted($user, 'content.view') || $this->permitted($user, 'content.brands');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $this->permitted($user, 'content.view') || $this->permitted($user, 'content.brands');
    }

    public function create(User $user): bool
    {
        return $this->permitted($user, 'content.brands') || $user->isManager();
    }

    public function update(User $user, Brand $brand): bool
    {
        return $this->permitted($user, 'content.brands') || $user->isManager() || (int) $brand->created_by === (int) $user->id;
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $this->permitted($user, 'content.brands') || $user->isManager();
    }
}

