<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\Models\Account;

final readonly class ArchiveAccount
{
    public function handle(Account $account): Account
    {
        if (! $account->isArchived()) {
            $account->update(['archived_at' => now()]);
        }

        return $account;
    }
}
