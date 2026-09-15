<?php

declare(strict_types=1);

namespace App\Livewire\Transactions;

use App\Actions\Transactions\UpdateTransaction;
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
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

final class Update extends Component
{
    public TransactionForm $form;

    #[On('open-transaction-update')]
    public function onShow(int $id): void
    {
        $transaction = Auth::user()->transactions()->find($id);

        if ($transaction === null) {
            return;
        }

        $this->form->setTransaction($transaction);

        Flux::modal('transaction-update')->show();
    }

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

    public function update(UpdateTransaction $action): void
    {
        $data = $this->form->validate();

        try {
            $this->authorize('update', $this->form->transaction);

            $action->handle(
                $this->user,
                $this->form->transaction,
                TransactionData::fromArray($data),
            );

            $this->dispatch('transaction::updated');
        } catch (AuthorizationException) {
            Flux::toast('Você não tem permissão para isso.', variant: 'danger');

            return;
        } catch (TransactionException $expection) {
            Flux::toast($expection->getMessage(), variant: 'danger');

            return;
        } catch (Throwable $expection) {
            report($expection);

            Flux::toast('Falha ao atualizar o lançamento tente novamente.', variant: 'danger');

            return;
        }

        Flux::toast('Lançamento atualizado com sucesso!', variant: 'success');

        Flux::modal('transaction-update')->close();
    }

    public function render(): View
    {
        return view('livewire.transactions.update');
    }
}
