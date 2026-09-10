<?php

declare(strict_types=1);

namespace App\Livewire\Accounts;

use App\Actions\Accounts\ArchiveAccount;
use App\Actions\Accounts\CreateAccount;
use App\Actions\Accounts\DeleteAccount;
use App\Actions\Accounts\UnarchiveAccount;
use App\Actions\Accounts\UpdateAccount;
use App\Data\Accounts\AccountBalanceData;
use App\Data\Money;
use App\Enums\AccountType;
use App\Exceptions\Accounts\AccountHasHistory;
use App\Livewire\Forms\AccountForm;
use App\Models\Account;
use App\Queries\Accounts\AccountsOverviewQuery;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::dashboard')]
#[Title('Contas')]
final class Index extends Component
{
    public AccountForm $form;

    public bool $showModal = false;

    public bool $showArchived = false;

    /**
     * Account type the tag filter is narrowed to, or null for all types.
     */
    public ?string $filterType = null;

    /**
     * @return Collection<int, AccountBalanceData>
     */
    #[Computed]
    public function accounts(): Collection
    {
        return app(AccountsOverviewQuery::class)->handle(auth()->user(), $this->showArchived);
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
        if ($this->filterType === null || $this->filterType === '') {
            return $this->accounts;
        }

        return $this->accounts
            ->filter(fn (AccountBalanceData $account): bool => $account->type->value === $this->filterType)
            ->values();
    }

    #[Computed]
    public function activeTotal(): Money
    {
        return Money::fromCents(
            $this->accounts
                ->reject(fn (AccountBalanceData $account) => $account->archived)
                ->sum(fn (AccountBalanceData $account) => $account->balance->cents),
        );
    }

    public function create(): void
    {
        $this->authorize('create', Account::class);

        $this->form->reset();
        $this->form->type = AccountType::Checking->value;
        $this->showModal = true;
    }

    public function edit(Account $account): void
    {
        $this->authorize('update', $account);

        $this->form->setAccount($account);
        $this->showModal = true;
    }

    public function save(CreateAccount $createAccount, UpdateAccount $updateAccount): void
    {
        $this->form->validate();

        if ($this->form->accountId === null) {
            $this->authorize('create', Account::class);
            $createAccount->handle(
                auth()->user(),
                $this->form->name,
                $this->form->type(),
                $this->form->initialBalanceMoney(),
            );
        } else {
            $account = auth()->user()->accounts()->findOrFail($this->form->accountId);
            $this->authorize('update', $account);
            $updateAccount->handle(
                $account,
                $this->form->name,
                $this->form->type(),
                $this->form->initialBalanceMoney(),
            );
        }

        unset($this->accounts);
        $this->showModal = false;
        $this->form->reset();

        Flux::toast('Conta salva.', variant: 'success');
    }

    public function archive(Account $account, ArchiveAccount $archiveAccount): void
    {
        $this->authorize('update', $account);

        $archiveAccount->handle($account);
        unset($this->accounts);

        Flux::toast('Conta arquivada.', variant: 'success');
    }

    public function unarchive(Account $account, UnarchiveAccount $unarchiveAccount): void
    {
        $this->authorize('update', $account);

        $unarchiveAccount->handle($account);
        unset($this->accounts);

        Flux::toast('Conta reativada.', variant: 'success');
    }

    public function delete(Account $account, DeleteAccount $deleteAccount): void
    {
        $this->authorize('delete', $account);

        try {
            $deleteAccount->handle($account);
        } catch (AccountHasHistory) {
            Flux::toast('Esta conta tem lançamentos. Arquive-a em vez de excluir.', variant: 'danger');

            return;
        }

        unset($this->accounts);

        Flux::toast('Conta excluída.', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.accounts.index');
    }
}
