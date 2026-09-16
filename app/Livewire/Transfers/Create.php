<?php

declare(strict_types=1);

namespace App\Livewire\Transfers;

use App\Actions\Transfers\CreateTransfer;
use App\Data\Transfers\TransferData;
use App\Exceptions\Transfers\TransferException;
use App\Livewire\Concerns\WithAccountOptions;
use App\Livewire\Forms\TransferForm;
use App\Models\Transfer;
use Carbon\CarbonImmutable;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

final class Create extends Component
{
    use WithAccountOptions;

    public TransferForm $form;

    public function mount(): void
    {
        $this->form->date = CarbonImmutable::now()->toDateString();
    }

    #[On('transfer::create')]
    public function onShow(): void
    {
        $this->form->reset();
        $this->form->date = CarbonImmutable::now()->toDateString();

        Flux::modal('transfer-create')->show();
    }

    public function create(CreateTransfer $action): void
    {
        $data = $this->form->validate();

        try {
            $this->authorize('create', Transfer::class);

            $action->handle(Auth::user(), TransferData::fromArray($data));

            $this->dispatch('transfer::changed');
        } catch (AuthorizationException) {
            Flux::toast('Você não tem permissão para isso.', variant: 'danger');

            return;
        } catch (TransferException $exception) {
            Flux::toast($exception->getMessage(), variant: 'danger');

            return;
        } catch (Throwable $exception) {
            report($exception);

            Flux::toast('Falha ao criar a transferência, tente novamente.', variant: 'danger');

            return;
        }

        $this->form->reset();
        $this->form->date = CarbonImmutable::now()->toDateString();

        Flux::toast('Transferência criada com sucesso!', variant: 'success');

        Flux::modal('transfer-create')->close();
    }

    public function render(): View
    {
        return view('livewire.transfers.create');
    }
}
