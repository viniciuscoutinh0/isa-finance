<?php

declare(strict_types=1);

namespace App\Livewire\Transfers;

use App\Actions\Transfers\DeleteTransfer;
use App\Livewire\Concerns\WithAccountOptions;
use App\Models\Transfer;
use App\Queries\Transfers\TransfersQuery;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::dashboard')]
#[Title('Transferências')]
final class Index extends Component
{
    use WithAccountOptions;
    use WithPagination;

    /**
     * @var array<string, mixed>
     */
    #[Url]
    public array $filters = [
        'account_id' => null,
    ];

    /**
     * @return LengthAwarePaginator<int, Transfer>
     */
    #[Computed]
    public function transfers(): LengthAwarePaginator
    {
        return app(TransfersQuery::class)->handle(Auth::user(), $this->filters);
    }

    /**
     * Drop the memoized list after a sibling component writes a transfer.
     */
    #[On('transfer::changed')]
    public function refreshList(): void
    {
        unset($this->transfers);
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'filters')) {
            $this->resetPage();
        }
    }

    public function delete(Transfer $transfer, DeleteTransfer $action): void
    {
        try {
            $this->authorize('delete', $transfer);
        } catch (AuthorizationException) {
            Flux::toast('Você não tem permissão para isso.', variant: 'danger');

            return;
        }

        $action->handle($transfer);

        $this->refreshList();

        Flux::toast('Transferência excluída.', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.transfers.index');
    }
}
