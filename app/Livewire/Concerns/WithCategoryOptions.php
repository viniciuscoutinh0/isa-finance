<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Category;
use App\Queries\Categories\CategoriesQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

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
