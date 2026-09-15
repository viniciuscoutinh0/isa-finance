<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\Data\Accounts\AccountData;
use App\Models\Account;
use App\Models\User;

final readonly class CreateAccount
{
    public function handle(User $user, AccountData $data): Account
    {
        return $user->accounts()->create($data->toArray());
    }
}
