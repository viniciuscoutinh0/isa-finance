<?php

declare(strict_types=1);

namespace App\Queries\Transactions;

use App\Data\Money;
use App\Data\Transactions\MonthlyCashFlowData;
use App\Enums\CategoryType;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class MonthlyCashFlowQuery
{
    /**
     * Income and expense totals per calendar month for the last $months months
     * (current month last), zero-filled for months with no activity.
     *
     * Rows are grouped in PHP rather than SQL so the same code runs on sqlsrv
     * and sqlite — no driver-specific date functions (see .ai/rules/database.md).
     * The window is a handful of months, so the row count stays small.
     *
     * @return Collection<int, MonthlyCashFlowData>
     */
    public function handle(User $user, int $months = 6): Collection
    {
        $start = CarbonImmutable::now()->startOfMonth()->subMonths($months - 1);

        $transactions = Transaction::query()
            ->with('category:id,type')
            ->where('user_id', $user->id)
            ->where('date', '>=', $start->toDateString())
            ->get(['id', 'category_id', 'amount', 'date']);

        return Collection::times($months, function (int $position) use ($start, $transactions): MonthlyCashFlowData {
            $month = $start->addMonths($position - 1);

            $inMonth = $transactions->filter(
                fn (Transaction $transaction): bool => $transaction->date->isSameMonth($month),
            );

            return new MonthlyCashFlowData(
                month: $month,
                income: $this->sumOfType($inMonth, CategoryType::Income),
                expense: $this->sumOfType($inMonth, CategoryType::Expense),
            );
        });
    }

    /**
     * @param  Collection<int, Transaction>  $transactions
     */
    private function sumOfType(Collection $transactions, CategoryType $type): Money
    {
        $cents = $transactions
            ->filter(fn (Transaction $transaction): bool => $transaction->category->type === $type)
            ->sum('amount');

        return Money::fromCents((int) $cents);
    }
}
