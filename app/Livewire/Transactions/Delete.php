<?php

declare(strict_types=1);

namespace App\Livewire\Transactions;

use App\Actions\Transactions\DeleteTransaction;
use App\Models\Transaction;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class Delete extends Component
{
    #[Locked]
    public Transaction $transaction;

    public function delete(DeleteTransaction $action): void
    {
        try {
            $this->authorize('delete', $this->transaction);

            $action->handle($this->transaction);

            $this->dispatch('transaction::deleted');
        } catch (AuthorizationException) {
            Flux::toast('Você não tem permissão para isso.', variant: 'danger');

            return;
        }

        Flux::toast('Lançamento excluído.', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.transactions.delete');
    }
}
