<?php

declare(strict_types=1);

namespace App\Data\Transactions;

use App\Data\Money;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Income and expense totals for a single calendar month,
 * as produced by MonthlyCashFlowQuery.
 */
final readonly class MonthlyCashFlowData
{
    public function __construct(
        public CarbonImmutable $month,
        public Money $income,
        public Money $expense,
    ) {}

    /**
     * Income minus expense for the month; negative when spending outran earning.
     */
    public function net(): Money
    {
        return $this->income->subtract($this->expense);
    }

    /**
     * pt-BR month label shown to the user, e.g. "Setembro de 2026".
     */
    public function monthLabel(): string
    {
        return Str::ucfirst($this->month->translatedFormat('F \d\e Y'));
    }

    /**
     * Short pt-BR month label for a chart axis, e.g. "set/26".
     */
    public function shortMonthLabel(): string
    {
        return $this->month->translatedFormat('M/y');
    }
}
