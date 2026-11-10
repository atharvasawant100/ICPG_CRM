<?php

namespace App\Policies;

use App\Models\SocialAccount;
use App\Models\User;
use App\Policies\Concerns\BasePolicy;

class SocialAccountPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->permitted($user, 'content.view') || $this->permitted($user, 'content.accounts') || $this->permitted($user, 'content.brands');
    }

    public function view(User $user, SocialAccount $account): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->permitted($user, 'content.accounts') || $this->permitted($user, 'content.brands') || $user->isManager();
    }

    public function update(User $user, SocialAccount $account): bool
    {
        return $this->permitted($user, 'content.accounts') || $this->permitted($user, 'content.brands') || $user->isManager() || (int) $account->created_by === (int) $user->id;
    }

    public function delete(User $user, SocialAccount $account): bool
    {
        return $this->permitted($user, 'content.accounts') || $this->permitted($user, 'content.brands') || $user->isManager();
    }
}

