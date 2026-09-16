<?php

declare(strict_types=1);

namespace App\Livewire\Transfers;

use App\Actions\Transfers\UpdateTransfer;
use App\Data\Transfers\TransferData;
use App\Exceptions\Transfers\TransferException;
use App\Livewire\Concerns\WithAccountOptions;
use App\Livewire\Forms\TransferForm;
use App\Models\Transfer;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

final class Update extends Component
{
    use WithAccountOptions;

    public TransferForm $form;

    #[On('transfer::edit')]
    public function onShow(int $id): void
    {
        $transfer = Transfer::query()->ownedBy(Auth::user())->find($id);

        if ($transfer === null) {
            return;
        }

        $this->form->setTransfer($transfer);

        Flux::modal('transfer-update')->show();
    }

    public function update(UpdateTransfer $action): void
    {
        $data = $this->form->validate();

        try {
            $this->authorize('update', $this->form->transfer);

            $action->handle(Auth::user(), $this->form->transfer, TransferData::fromArray($data));

            $this->dispatch('transfer::changed');
        } catch (AuthorizationException) {
            Flux::toast('Você não tem permissão para isso.', variant: 'danger');

            return;
        } catch (TransferException $exception) {
            Flux::toast($exception->getMessage(), variant: 'danger');

            return;
        } catch (Throwable $exception) {
            report($exception);

            Flux::toast('Falha ao atualizar a transferência, tente novamente.', variant: 'danger');

            return;
        }

        Flux::toast('Transferência atualizada com sucesso!', variant: 'success');

        Flux::modal('transfer-update')->close();
    }

    public function render(): View
    {
        return view('livewire.transfers.update');
    }
}
