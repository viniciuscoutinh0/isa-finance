<?php

declare(strict_types=1);

namespace App\Queries\Transactions;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class RecentTransactionsQuery
{
    /**
     * @return Collection<int, Transaction>
     */
    public function handle(User $user, int $limit = 10): Collection
    {
        return Transaction::query()
            ->with(['account', 'category'])
            ->where('user_id', $user->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
