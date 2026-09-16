<?php

declare(strict_types=1);

namespace App\Queries\Categories;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final readonly class CategoriesQuery
{
    public function handle(User $user, ?CategoryType $type = null): Collection
    {
        return Category::query()
            ->where('user_id', $user->id)
            ->when($type, fn (Builder $query): Builder => $query->where('type', $type))
            ->orderBy('name')
            ->get();
    }
}
