<?php

declare(strict_types=1);

namespace App\Queries\Categories;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class CategoriesQuery
{
    /**
     * Every category owned by the user, ordered by name, optionally
     * narrowed to a single type.
     *
     * @return Collection<int, Category>
     */
    public function handle(User $user, ?CategoryType $type = null): Collection
    {
        return Category::query()
            ->where('user_id', $user->id)
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderBy('name')
            ->get();
    }
}
