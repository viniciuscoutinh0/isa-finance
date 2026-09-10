<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\Models\Account;

final readonly class UnarchiveAccount
{
    public function handle(Account $account): Account
    {
        if ($account->isArchived()) {
            $account->update(['archived_at' => null]);
        }

        return $account;
    }
}
