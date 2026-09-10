<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

final class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $this->owns($user, $transaction);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $this->owns($user, $transaction);
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $this->owns($user, $transaction);
    }

    private function owns(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }
}
