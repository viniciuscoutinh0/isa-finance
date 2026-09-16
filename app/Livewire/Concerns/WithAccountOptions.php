<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Account;
use App\Queries\Accounts\AccountsQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

trait WithAccountOptions
{
    /**
     * @return Collection<int, Account>
     */
    #[Computed]
    public function accounts(): Collection
    {
        return app(AccountsQuery::class)->handle(Auth::user());
    }
}
