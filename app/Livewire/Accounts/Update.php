<?php

declare(strict_types=1);

namespace App\Livewire\Accounts;

use App\Actions\Accounts\UpdateAccount;
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

final class Update extends Component
{
    public AccountForm $form;

    #[On('account::edit')]
    public function onShow(int $id): void
    {
        $account = Account::query()->ownedBy(Auth::user())->find($id);

        if ($account === null) {
            return;
        }

        $this->form->setAccount($account);

        Flux::modal('account-update')->show();
    }

    public function update(UpdateAccount $action): void
    {
        $data = $this->form->validate();

        try {
            $this->authorize('update', $this->form->account);

            $action->handle($this->form->account, AccountData::fromArray($data));

            $this->dispatch('account::changed');
        } catch (AuthorizationException) {
            Flux::toast('Você não tem permissão para isso.', variant: 'danger');

            return;
        } catch (Throwable $exception) {
            report($exception);

            Flux::toast('Falha ao atualizar a conta, tente novamente.', variant: 'danger');

            return;
        }

        Flux::toast('Conta atualizada com sucesso!', variant: 'success');

        Flux::modal('account-update')->close();
    }

    public function render(): View
    {
        return view('livewire.accounts.update');
    }
}
