<?php

declare(strict_types=1);

namespace App\Livewire\Accounts;

use App\Actions\Accounts\CreateAccount;
use App\Data\Accounts\AccountData;
use App\Livewire\Forms\AccountForm;
use App\Models\Account;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

final class Create extends Component
{
    public AccountForm $form;

    #[On('account::create')]
    public function onShow(): void
    {
        $this->form->reset();

        Flux::modal('account-create')->show();
    }

    public function create(CreateAccount $action): void
    {
        $data = $this->form->validate();

        try {
            $this->authorize('create', Account::class);

            $action->handle(Auth::user(), AccountData::fromArray($data));

            $this->dispatch('account::changed');
        } catch (AuthorizationException) {
            Flux::toast('Você não tem permissão para isso.', variant: 'danger');

            return;
        } catch (Throwable $exception) {
            report($exception);

            Flux::toast('Falha ao criar a conta, tente novamente.', variant: 'danger');

            return;
        }

        $this->form->reset();

        Flux::toast('Conta criada com sucesso!', variant: 'success');

        Flux::modal('account-create')->close();
    }

    public function render(): View
    {
        return view('livewire.accounts.create');
    }
}
