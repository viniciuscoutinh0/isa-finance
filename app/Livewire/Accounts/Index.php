<?php

declare(strict_types=1);

namespace App\Livewire\Accounts;

use App\Actions\Accounts\ArchiveAccount;
use App\Actions\Accounts\DeleteAccount;
use App\Actions\Accounts\UnarchiveAccount;
use App\Data\Accounts\AccountBalanceData;
use App\Data\Money;
use App\Enums\AccountType;
use App\Exceptions\Accounts\AccountHasHistory;
use App\Models\Account;
use App\Queries\Accounts\AccountsOverviewQuery;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::dashboard')]
#[Title('Contas')]
final class Index extends Component
{
    /**
     * @var array<string, mixed>
     */
    #[Url]
    public array $filters = [
        'type' => null,
        'archived' => false,
    ];

    /**
     * @return Collection<int, AccountBalanceData>
     */
    #[Computed]
    public function accounts(): Collection
    {
        return app(AccountsOverviewQuery::class)->handle(Auth::user(), (bool) $this->filters['archived']);
    }

    /**
     * Types present among the current accounts, sorted by label — one filter tag each.
     *
     * @return Collection<int, AccountType>
     */
    #[Computed]
    public function availableTypes(): Collection
    {
        return $this->accounts
            ->map(fn (AccountBalanceData $account): AccountType => $account->type)
            ->unique()
            ->sortBy(fn (AccountType $type): string => $type->label())
            ->values();
    }

    /**
     * Accounts left visible after the tag filter is applied.
     *
     * @return Collection<int, AccountBalanceData>
     */
    #[Computed]
    public function visibleAccounts(): Collection
    {
        $type = $this->filters['type'] ?? null;

        if ($type === null || $type === '') {
            return $this->accounts;
        }

        return $this->accounts
            ->filter(fn (AccountBalanceData $account): bool => $account->type->value === $type)
            ->values();
    }

    /**
     * The total never narrows with the tag filter: it answers "how much do I have".
     */
    #[Computed]
    public function activeTotal(): Money
    {
        return Money::fromCents(
            $this->accounts
                ->reject(fn (AccountBalanceData $account) => $account->archived)
                ->sum(fn (AccountBalanceData $account) => $account->balance->cents),
        );
    }

    /**
     * Drop the memoized overview after a sibling component writes an account.
     */
    #[On('account::changed')]
    public function refreshList(): void
    {
        unset($this->accounts, $this->availableTypes, $this->visibleAccounts, $this->activeTotal);
    }

    public function archive(Account $account, ArchiveAccount $action): void
    {
        if (! $this->allows('update', $account)) {
            return;
        }

        $action->handle($account);

        $this->refreshList();

        Flux::toast('Conta arquivada.', variant: 'success');
    }

    public function unarchive(Account $account, UnarchiveAccount $action): void
    {
        if (! $this->allows('update', $account)) {
            return;
        }

        $action->handle($account);

        $this->refreshList();

        Flux::toast('Conta reativada.', variant: 'success');
    }

    public function delete(Account $account, DeleteAccount $action): void
    {
        if (! $this->allows('delete', $account)) {
            return;
        }

        try {
            $action->handle($account);
        } catch (AccountHasHistory) {
            Flux::toast('Esta conta tem lançamentos. Arquive-a em vez de excluir.', variant: 'danger');

            return;
        }

        $this->refreshList();

        Flux::toast('Conta excluída.', variant: 'success');
    }

    private function allows(string $ability, Account $account): bool
    {
        try {
            $this->authorize($ability, $account);
        } catch (AuthorizationException) {
            Flux::toast('Você não tem permissão para isso.', variant: 'danger');

            return false;
        }

        return true;
    }

    public function render(): View
    {
        return view('livewire.accounts.index');
    }
}
