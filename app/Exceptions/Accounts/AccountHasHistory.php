<?php

declare(strict_types=1);

namespace App\Exceptions\Accounts;

use App\Models\Account;

final class AccountHasHistory extends AccountException
{
    public static function for(Account $account): self
    {
        return new self("Account [{$account->id}] has transactions or transfers and cannot be deleted; archive it instead.");
    }
}
