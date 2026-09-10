<?php

declare(strict_types=1);

namespace App\Livewire\Transfers;

use App\Actions\Transfers\CreateTransfer;
use App\Actions\Transfers\DeleteTransfer;
use App\Actions\Transfers\UpdateTransfer;
use App\Exceptions\Transfers\SameAccountTransfer;
use App\Livewire\Forms\TransferForm;
use App\Models\Account;
use App\Models\Transfer;
use App\Queries\Accounts\AccountsQuery;
use App\Queries\Transfers\TransfersQuery;
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
#[Title('Transferências')]
final class Index extends Component
{
    use WithPagination;

    public TransferForm $form;

    public bool $showModal = false;

    #[Url]
    public string $filterAccount = '';

    /**
     * @return LengthAwarePaginator<int, Transfer>
     */
    #[Computed]
    public function transfers(): LengthAwarePaginator
    {
        return app(TransfersQuery::class)->handle(auth()->user(), [
            'account_id' => $this->filterAccount !== '' ? (int) $this->filterAccount : null,
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

    public function updatedFilterAccount(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('create', Transfer::class);

        $this->form->reset();
        $this->form->date = CarbonImmutable::now()->toDateString();
        $this->showModal = true;
    }

    public function edit(Transfer $transfer): void
    {
        $this->authorize('update', $transfer);

        $this->form->setTransfer($transfer);
        $this->showModal = true;
    }

    public function save(CreateTransfer $createTransfer, UpdateTransfer $updateTransfer): void
    {
        $this->form->validate();

        $user = auth()->user();
        $from = $user->accounts()->findOrFail($this->form->fromAccountId);
        $to = $user->accounts()->findOrFail($this->form->toAccountId);

        try {
            if ($this->form->transferId === null) {
                $this->authorize('create', Transfer::class);
                $createTransfer->handle(
                    $user,
                    $from,
                    $to,
                    $this->form->dateValue(),
                    $this->form->amountMoney(),
                    $this->form->notesValue(),
                );
            } else {
                $transfer = $user->transfers()->findOrFail($this->form->transferId);
                $this->authorize('update', $transfer);
                $updateTransfer->handle(
                    $transfer,
                    $from,
                    $to,
                    $this->form->dateValue(),
                    $this->form->amountMoney(),
                    $this->form->notesValue(),
                );
            }
        } catch (SameAccountTransfer) {
            $this->addError('form.toAccountId', 'A conta de destino deve ser diferente da conta de origem.');

            return;
        }

        unset($this->transfers);
        $this->showModal = false;
        $this->form->reset();

        Flux::toast('Transferência salva.', variant: 'success');
    }

    public function delete(Transfer $transfer, DeleteTransfer $deleteTransfer): void
    {
        $this->authorize('delete', $transfer);

        $deleteTransfer->handle($transfer);

        unset($this->transfers);

        Flux::toast('Transferência excluída.', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.transfers.index');
    }
}
