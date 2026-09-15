<?php

declare(strict_types=1);

namespace App\Livewire\Transactions\Concerns;

use App\Queries\Accounts\AccountsQuery;
use App\Queries\Categories\CategoriesQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

trait WithFormOptions
{
    #[Computed]
    public function accounts(): Collection
    {
        return app(AccountsQuery::class)->handle(Auth::user());
    }

    #[Computed]
    public function categories(): Collection
    {
        return app(CategoriesQuery::class)->handle(Auth::user())->groupBy('type');
    }
}
