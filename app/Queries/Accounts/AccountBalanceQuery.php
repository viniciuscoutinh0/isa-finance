<?php

declare(strict_types=1);

namespace App\Queries\Accounts;

use App\Data\Money;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

final readonly class AccountBalanceQuery
{
    /**
     * Derived balance of a single account (ADR 0003): initial balance, plus
     * income minus expense transactions, minus transfers out plus transfers in.
     */
    public function handle(Account $account): Money
    {
        $cents = DB::table('accounts')
            ->where('accounts.id', $account->id)
            ->selectRaw('('.AccountBalanceExpression::SQL.') AS balance')
            ->value('balance');

        return Money::fromCents((int) $cents);
    }
}
