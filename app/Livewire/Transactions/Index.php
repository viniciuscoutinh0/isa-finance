<?php

declare(strict_types=1);

namespace App\Livewire\Transactions;

use App\Queries\Accounts\AccountsQuery;
use App\Queries\Categories\CategoriesQuery;
use App\Queries\Transactions\TransactionsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::dashboard')]
#[Title('Lançamentos')]
final class Index extends Component
{
    use WithPagination;

    #[Url]
    public array $filters = [
        'accounts' => [],
        'categories' => [],
        'types' => [],
        'search' => null,
    ];

    #[Computed]
    #[On('transaction::created')]
    #[On('transaction::updated')]
    #[On('transaction::deleted')]
    public function transactions(): LengthAwarePaginator
    {
        return app(TransactionsQuery::class)->handle(Auth::user(), $this->filters);
    }

    #[Computed]
    public function accounts(): Collection
    {
        return app(AccountsQuery::class)->handle(Auth::user());
    }

    #[Computed]
    public function categories(): Collection
    {
        return app(CategoriesQuery::class)->handle(Auth::user());
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'filters')) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        return view('livewire.transactions.index');
    }
}
