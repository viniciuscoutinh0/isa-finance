<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Data\Accounts\AccountBalanceData;
use App\Data\Money;
use App\Data\Transactions\MonthlyCashFlowData;
use App\Models\Transaction;
use App\Queries\Accounts\AccountsOverviewQuery;
use App\Queries\Transactions\MonthlyCashFlowQuery;
use App\Queries\Transactions\RecentTransactionsQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::dashboard')]
#[Title('Painel')]
final class Dashboard extends Component
{
    /**
     * @return Collection<int, AccountBalanceData>
     */
    #[Computed]
    public function accounts(): Collection
    {
        return app(AccountsOverviewQuery::class)->handle(auth()->user());
    }

    #[Computed]
    public function totalBalance(): Money
    {
        return Money::fromCents(
            $this->accounts->sum(fn ($account) => $account->balance->cents),
        );
    }

    /**
     * @return Collection<int, Transaction>
     */
    #[Computed]
    public function recentTransactions(): Collection
    {
        return app(RecentTransactionsQuery::class)->handle(auth()->user());
    }

    /**
     * @return Collection<int, MonthlyCashFlowData>
     */
    #[Computed]
    public function monthlyCashFlow(): Collection
    {
        return app(MonthlyCashFlowQuery::class)->handle(auth()->user());
    }

    #[Computed]
    public function currentMonth(): MonthlyCashFlowData
    {
        return $this->monthlyCashFlow->last();
    }

    /**
     * @return list<array{month: string, income: float, expense: float}>
     */
    #[Computed]
    public function cashFlowSeries(): array
    {
        return $this->monthlyCashFlow
            ->map(fn (MonthlyCashFlowData $month): array => [
                'month' => $month->shortMonthLabel(),
                'income' => $month->income->cents / 100,
                'expense' => $month->expense->cents / 100,
            ])
            ->all();
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
