<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Transfer;
use App\Models\User;

final class TransferPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transfer $transfer): bool
    {
        return $this->owns($user, $transfer);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Transfer $transfer): bool
    {
        return $this->owns($user, $transfer);
    }

    public function delete(User $user, Transfer $transfer): bool
    {
        return $this->owns($user, $transfer);
    }

    private function owns(User $user, Transfer $transfer): bool
    {
        return $user->id === $transfer->user_id;
    }
}
