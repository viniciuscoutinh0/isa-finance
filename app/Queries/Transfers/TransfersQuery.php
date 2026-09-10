<?php

declare(strict_types=1);

namespace App\Queries\Transfers;

use App\Models\Transfer;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class TransfersQuery
{
    /**
     * The user's transfers, newest first, eager-loaded with both accounts.
     * An account filter matches transfers where it is either side.
     *
     * @param  array{account_id?: int|null, from?: string|null, to?: string|null}  $filters
     * @return LengthAwarePaginator<int, Transfer>
     */
    public function handle(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return Transfer::query()
            ->with(['fromAccount', 'toAccount'])
            ->where('user_id', $user->id)
            ->when(
                $filters['account_id'] ?? null,
                fn ($query, $accountId) => $query->where(
                    fn ($sub) => $sub->where('from_account_id', $accountId)
                        ->orWhere('to_account_id', $accountId),
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
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
