<?php

declare(strict_types=1);

namespace App\Livewire\Transactions;

use App\Actions\Transactions\CreateTransaction;
use App\Actions\Transactions\DeleteTransaction;
use App\Actions\Transactions\UpdateTransaction;
use App\Enums\CategoryType;
use App\Livewire\Forms\TransactionForm;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Queries\Accounts\AccountsQuery;
use App\Queries\Categories\CategoriesQuery;
use App\Queries\Transactions\TransactionsQuery;
use Carbon\CarbonImmutable;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::dashboard')]
#[Title('Lançamentos')]
final class Index extends Component
{
    use WithPagination;

    public TransactionForm $form;

    public bool $showModal = false;

    #[Url]
    public string $filterAccount = '';

    #[Url]
    public string $filterCategory = '';

    #[Url]
    public string $filterType = '';

    #[Url]
    public string $search = '';

    /**
     * @return LengthAwarePaginator<int, Transaction>
     */
    #[Computed]
    public function transactions(): LengthAwarePaginator
    {
        return app(TransactionsQuery::class)->handle(auth()->user(), [
            'account_id' => $this->filterAccount !== '' ? (int) $this->filterAccount : null,
            'category_id' => $this->filterCategory !== '' ? (int) $this->filterCategory : null,
            'type' => $this->filterType !== '' ? CategoryType::from($this->filterType) : null,
            'search' => $this->search,
        ]);
    }

    /**
     * @return Collection<int, Account>
     */
    #[Computed]
    public function accounts(): Collection
    {
        return app(AccountsQuery::class)->handle(auth()->user());
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return app(CategoriesQuery::class)->handle(auth()->user());
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'filter') || $property === 'search') {
            $this->resetPage();
        }
    }

    public function create(): void
    {
        $this->authorize('create', Transaction::class);

        $this->form->reset();
        $this->form->date = CarbonImmutable::now()->toDateString();
        $this->showModal = true;
    }

    public function edit(Transaction $transaction): void
    {
        $this->authorize('update', $transaction);

        $this->form->setTransaction($transaction);
        $this->showModal = true;
    }

    public function save(CreateTransaction $createTransaction, UpdateTransaction $updateTransaction): void
    {
        $this->form->validate();

        $user = auth()->user();
        $account = $user->accounts()->findOrFail($this->form->accountId);
        $category = $user->categories()->findOrFail($this->form->categoryId);

        if ($this->form->transactionId === null) {
            $this->authorize('create', Transaction::class);
            $createTransaction->handle(
                $user,
                $account,
                $category,
                $this->form->dateValue(),
                $this->form->description,
                $this->form->amountMoney(),
                $this->form->notesValue(),
            );
        } else {
            $transaction = $user->transactions()->findOrFail($this->form->transactionId);
            $this->authorize('update', $transaction);
            $updateTransaction->handle(
                $transaction,
                $account,
                $category,
                $this->form->dateValue(),
                $this->form->description,
                $this->form->amountMoney(),
                $this->form->notesValue(),
            );
        }

        unset($this->transactions);
        $this->showModal = false;
        $this->form->reset();

        Flux::toast('Lançamento salvo.', variant: 'success');
    }

    public function delete(Transaction $transaction, DeleteTransaction $deleteTransaction): void
    {
        $this->authorize('delete', $transaction);

        $deleteTransaction->handle($transaction);

        unset($this->transactions);

        Flux::toast('Lançamento excluído.', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.transactions.index');
    }
}
