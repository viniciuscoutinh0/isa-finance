<?php

declare(strict_types=1);

namespace App\Livewire\Transactions;

use App\Actions\Transactions\CreateTransaction;
use App\Data\Transactions\TransactionData;
use App\Exceptions\Transactions\TransactionException;
use App\Livewire\Forms\TransactionForm;
use App\Models\Transaction;
use App\Models\User;
use App\Queries\Accounts\AccountsQuery;
use App\Queries\Categories\CategoriesQuery;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Throwable;

final class Create extends Component
{
    public TransactionForm $form;

    #[Computed]
    public function user(): ?User
    {
        return Auth::user();
    }

    #[Computed]
    public function accounts(): Collection
    {
        if ($this->user === null) {
            return collect();
        }

        return app(AccountsQuery::class)->handle($this->user);
    }

    #[Computed]
    public function categories(): Collection
    {
        if ($this->user === null) {
            return collect();
        }

        return app(CategoriesQuery::class)
            ->handle($this->user)
            ->groupBy('type');
    }

    public function create(CreateTransaction $action): void
    {
        $data = $this->form->validate();

        try {
            $this->authorize('create', Transaction::class);

            $action->handle($this->user, TransactionData::fromArray($data));

            $this->dispatch('transaction::created');
        } catch (AuthorizationException) {
            Flux::toast('Você não tem permissão para isso.', variant: 'danger');

            return;
        } catch (TransactionException $expection) {
            Flux::toast($expection->getMessage(), variant: 'danger');

            return;
        } catch (Throwable $expection) {
            report($expection);

            Flux::toast('Falha ao criar um lançamento tente novamente.', variant: 'danger');

            return;
        }

        $this->form->reset();

        Flux::toast('Lançamento criado com sucesso!', variant: 'success');

        Flux::modal('transaction-create')->close();
    }

    public function render(): View
    {
        return view('livewire.transactions.create');
    }
}
