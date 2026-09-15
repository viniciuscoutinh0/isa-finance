<?php

declare(strict_types=1);

namespace App\Livewire\Transactions;

use App\Actions\Transactions\UpdateTransaction;
use App\Data\Transactions\TransactionData;
use App\Exceptions\Transactions\TransactionException;
use App\Livewire\Forms\TransactionForm;
use App\Livewire\Transactions\Concerns\WithFormOptions;
use App\Models\Transaction;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

final class Update extends Component
{
    use WithFormOptions;

    public TransactionForm $form;

    #[On('open-transaction-update')]
    public function onShow(int $id): void
    {
        $transaction = Transaction::query()->ownedBy(Auth::user())->find($id);

        if ($transaction === null) {
            return;
        }

        $this->form->setTransaction($transaction);

        Flux::modal('transaction-update')->show();
    }

    public function update(UpdateTransaction $action): void
    {
        $data = $this->form->validate();

        try {
            $this->authorize('update', $this->form->transaction);

            $action->handle(
                Auth::user(),
                $this->form->transaction,
                TransactionData::fromArray($data),
            );

            $this->dispatch('transaction::updated');
        } catch (AuthorizationException) {
            Flux::toast('Você não tem permissão para isso.', variant: 'danger');

            return;
        } catch (TransactionException $exception) {
            Flux::toast($exception->getMessage(), variant: 'danger');

            return;
        } catch (Throwable $exception) {
            report($exception);

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
