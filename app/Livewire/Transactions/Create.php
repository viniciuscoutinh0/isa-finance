<?php

declare(strict_types=1);

namespace App\Livewire\Transactions;

use App\Actions\Transactions\CreateTransaction;
use App\Data\Transactions\TransactionData;
use App\Exceptions\Transactions\TransactionException;
use App\Livewire\Concerns\WithAccountOptions;
use App\Livewire\Concerns\WithCategoryOptions;
use App\Livewire\Forms\TransactionForm;
use App\Models\Transaction;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Throwable;

final class Create extends Component
{
    use WithAccountOptions;
    use WithCategoryOptions;

    public TransactionForm $form;

    public function create(CreateTransaction $action): void
    {
        $data = $this->form->validate();

        try {
            $this->authorize('create', Transaction::class);

            $action->handle(Auth::user(), TransactionData::fromArray($data));

            $this->dispatch('transaction::changed');
        } catch (AuthorizationException) {
            Flux::toast('Você não tem permissão para isso.', variant: 'danger');

            return;
        } catch (TransactionException $exception) {
            Flux::toast($exception->getMessage(), variant: 'danger');

            return;
        } catch (Throwable $exception) {
            report($exception);

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
