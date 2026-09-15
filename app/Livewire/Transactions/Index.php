<?php

declare(strict_types=1);

namespace App\Livewire\Transactions;

use App\Actions\Transactions\DeleteTransaction;
use App\Livewire\Concerns\WithAccountOptions;
use App\Livewire\Concerns\WithCategoryOptions;
use App\Models\Transaction;
use App\Queries\Transactions\TransactionsQuery;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
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
    use WithAccountOptions;
    use WithCategoryOptions;
    use WithPagination;

    /**
     * @var array<string, mixed>
     */
    #[Url]
    public array $filters = [
        'accounts' => [],
        'categories' => [],
        'types' => [],
        'search' => null,
    ];

    /**
     * @return LengthAwarePaginator<int, Transaction>
     */
    #[Computed]
    public function transactions(): LengthAwarePaginator
    {
        return app(TransactionsQuery::class)->handle(Auth::user(), $this->filters);
    }

    /**
     * Drop the memoized list after a sibling component writes a transaction.
     */
    #[On('transaction::changed')]
    public function refreshList(): void
    {
        unset($this->transactions);
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'filters')) {
            $this->resetPage();
        }
    }

    public function delete(Transaction $transaction, DeleteTransaction $action): void
    {
        try {
            $this->authorize('delete', $transaction);
        } catch (AuthorizationException) {
            Flux::toast('Você não tem permissão para isso.', variant: 'danger');

            return;
        }

        $action->handle($transaction);

        $this->refreshList();

        Flux::toast('Lançamento excluído.', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.transactions.index');
    }
}
