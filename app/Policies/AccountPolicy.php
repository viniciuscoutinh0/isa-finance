<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

final class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Account $account): bool
    {
        return $this->owns($user, $account);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Account $account): bool
    {
        return $this->owns($user, $account);
    }

    public function delete(User $user, Account $account): bool
    {
        return $this->owns($user, $account);
    }

    private function owns(User $user, Account $account): bool
    {
        return $user->id === $account->user_id;
    }
}
