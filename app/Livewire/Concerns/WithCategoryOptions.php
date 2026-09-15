<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Category;
use App\Queries\Categories\CategoriesQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

/**
 * Categories the current user can pick from. Always flat: grouping by type is a
 * presentation choice and belongs to the view that needs it.
 */
trait WithCategoryOptions
{
    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return app(CategoriesQuery::class)->handle(Auth::user());
    }
}
