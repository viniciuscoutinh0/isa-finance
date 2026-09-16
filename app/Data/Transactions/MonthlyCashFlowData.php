<?php

declare(strict_types=1);

namespace App\Data\Transactions;

use App\Data\Money;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

final readonly class MonthlyCashFlowData
{
    public function __construct(
        public CarbonImmutable $month,
        public Money $income,
        public Money $expense,
    ) {}

    public function net(): Money
    {
        return $this->income->subtract($this->expense);
    }

    public function monthLabel(): string
    {
        return Str::ucfirst($this->month->translatedFormat('F \d\e Y'));
    }

    public function shortMonthLabel(): string
    {
        return $this->month->translatedFormat('M/y');
    }
}
