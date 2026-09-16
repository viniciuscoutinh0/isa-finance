<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\Data\Accounts\AccountData;
use App\Models\Account;

final readonly class UpdateAccount
{
    public function handle(Account $account, AccountData $data): Account
    {
        $account->update($data->toArray());

        return $account;
    }
}
