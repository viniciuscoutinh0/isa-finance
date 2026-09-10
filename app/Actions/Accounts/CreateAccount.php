<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\Data\Money;
use App\Enums\AccountType;
use App\Models\Account;
use App\Models\User;

final readonly class CreateAccount
{
    public function handle(User $user, string $name, AccountType $type, Money $initialBalance): Account
    {
        return $user->accounts()->create([
            'name' => $name,
            'type' => $type,
            'initial_balance' => $initialBalance->cents,
        ]);
    }
}
