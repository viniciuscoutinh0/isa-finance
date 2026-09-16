<?php

declare(strict_types=1);

namespace App\Queries\Accounts;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class AccountsQuery
{
    /**
     * @return Collection<int, Account>
     */
    public function handle(User $user, bool $includeArchived = false): Collection
    {
        return Account::query()
            ->where('user_id', $user->id)
            ->when(! $includeArchived, fn ($query) => $query->whereNull('archived_at'))
            ->orderBy('name')
            ->get();
    }
}
