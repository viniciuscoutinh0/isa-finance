<?php

declare(strict_types=1);

namespace App\Filters\Concerns;

use App\Filters\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

trait HasFilter
{
    public function scopeFilter(Builder $query, Filter $filter): Builder
    {
        return $filter->apply($query);
    }
}
