<?php

declare(strict_types=1);

namespace App\Queries\Accounts;

use App\Data\Accounts\AccountBalanceData;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class AccountsOverviewQuery
{
    /**
     * @return Collection<int, AccountBalanceData>
     */
    public function handle(User $user, bool $includeArchived = false): Collection
    {
        return DB::table('accounts')
            ->where('accounts.user_id', $user->id)
            ->when(! $includeArchived, fn ($query) => $query->whereNull('accounts.archived_at'))
            ->orderBy('accounts.name')
            ->selectRaw(
                'accounts.id, accounts.name, accounts.type, '
                .'accounts.initial_balance, accounts.archived_at, '
                .'('.AccountBalanceExpression::SQL.') AS balance'
            )
            ->get()
            ->map(AccountBalanceData::fromRow(...));
    }
}
