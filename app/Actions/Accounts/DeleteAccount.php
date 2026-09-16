<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\Exceptions\Accounts\AccountHasHistory;
use App\Models\Account;

final readonly class DeleteAccount
{
    /**
     * @throws AccountHasHistory
     */
    public function handle(Account $account): void
    {
        if ($account->hasHistory()) {
            throw AccountHasHistory::for($account);
        }

        $account->delete();
    }
}
