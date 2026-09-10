<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\Data\Money;
use App\Enums\AccountType;
use App\Models\Account;

final readonly class UpdateAccount
{
    public function handle(Account $account, string $name, AccountType $type, Money $initialBalance): Account
    {
        $account->update([
            'name' => $name,
            'type' => $type,
            'initial_balance' => $initialBalance->cents,
        ]);

        return $account;
    }
}
