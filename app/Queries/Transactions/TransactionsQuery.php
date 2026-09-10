<?php

declare(strict_types=1);

namespace App\Queries\Transactions;

use App\Enums\CategoryType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

final readonly class TransactionsQuery
{
    /**
     * The user's transactions, newest first, eager-loaded with account and
     * category, filtered by any of the given criteria.
     *
     * @param  array{account_id?: int|null, category_id?: int|null, type?: CategoryType|null, from?: string|null, to?: string|null, search?: string|null}  $filters
     * @return LengthAwarePaginator<int, Transaction>
     */
    public function handle(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return Transaction::query()
            ->with(['account', 'category'])
            ->where('transactions.user_id', $user->id)
            ->when(
                $filters['account_id'] ?? null,
                fn ($query, $accountId) => $query->where('account_id', $accountId),
            )
            ->when(
                $filters['category_id'] ?? null,
                fn ($query, $categoryId) => $query->where('category_id', $categoryId),
            )
            ->when(
                $filters['type'] ?? null,
                fn ($query, CategoryType $type) => $query->whereHas(
                    'category',
                    fn ($categoryQuery) => $categoryQuery->where('type', $type),
                ),
            )
            ->when(
                $filters['from'] ?? null,
                fn ($query, $from) => $query->whereDate('date', '>=', $from),
            )
            ->when(
                $filters['to'] ?? null,
                fn ($query, $to) => $query->whereDate('date', '<=', $to),
            )
            ->when(
                filled($filters['search'] ?? null),
                fn ($query) => $query->whereRaw(
                    'lower(description) like ?',
                    ['%'.Str::lower($filters['search']).'%'],
                ),
            )
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
