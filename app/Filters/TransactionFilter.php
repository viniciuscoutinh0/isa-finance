<?php

declare(strict_types=1);

namespace App\Filters;

use App\Filters\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;
use Override;

final readonly class TransactionFilter implements Filter
{
    public function __construct(
        public array $filters,
    ) {}

    #[Override]
    public function apply(Builder $builder): Builder
    {
        return $builder
            ->when(
                $this->filters['accounts'] ?? [],
                fn (Builder $query, array $accounts): Builder => $query->whereIn('account_id', $accounts),
            )
            ->when(
                $this->filters['categories'] ?? [],
                fn (Builder $query, array $categories): Builder => $query->whereIn('category_id', $categories),
            )
            ->when(
                $this->filters['types'] ?? [],
                fn (Builder $query, array $types): Builder => $query->whereHas(
                    'category',
                    fn (Builder $q) => $q->whereIn('type', $types),
                ),
            )
            ->when(
                $this->filters['from'] ?? null,
                fn ($query, $from) => $query->whereDate('date', '>=', $from),
            )
            ->when(
                $this->filters['to'] ?? null,
                fn ($query, $to) => $query->whereDate('date', '<=', $to),
            )
            ->when(
                $this->filters['search'] ?? null,
                fn (Builder $query, string $term): Builder => $query->whereLike('description', '%'.trim($term).'%'),
            );
    }
}
