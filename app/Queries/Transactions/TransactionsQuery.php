<?php

declare(strict_types=1);

namespace App\Queries\Transactions;

use App\Filters\TransactionFilter;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class TransactionsQuery
{
    public function handle(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return Transaction::query()
            ->with([
                'account',
                'category',
            ])
            ->where('transactions.user_id', $user->id)
            ->filter(new TransactionFilter($filters))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
