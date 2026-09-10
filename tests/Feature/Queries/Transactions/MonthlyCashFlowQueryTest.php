<?php

declare(strict_types=1);

use App\Data\Money;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Queries\Transactions\MonthlyCashFlowQuery;

it('returns one zero-filled row per month in the window, current month last', function (): void {
    $this->travelTo('2026-09-15 12:00:00');

    $user = User::factory()->create();

    $result = app(MonthlyCashFlowQuery::class)->handle($user, months: 6);

    expect($result)->toHaveCount(6)
        ->and($result->first()->month->toDateString())->toBe('2026-04-01')
        ->and($result->last()->month->toDateString())->toBe('2026-09-01')
        ->and($result->last()->income->cents)->toBe(0)
        ->and($result->last()->expense->cents)->toBe(0);
});

it('sums income and expense transactions into their calendar month', function (): void {
    $this->travelTo('2026-09-15 12:00:00');

    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create();
    $income = Category::factory()->ownedBy($user)->income()->create();
    $expense = Category::factory()->ownedBy($user)->expense()->create();

    Transaction::factory()->forAccount($account)->forCategory($income)->amountCents(300_00)->on('2026-09-02')->create();
    Transaction::factory()->forAccount($account)->forCategory($income)->amountCents(150_00)->on('2026-09-20')->create();
    Transaction::factory()->forAccount($account)->forCategory($expense)->amountCents(80_00)->on('2026-09-10')->create();
    Transaction::factory()->forAccount($account)->forCategory($expense)->amountCents(20_00)->on('2026-08-28')->create();

    $result = app(MonthlyCashFlowQuery::class)->handle($user, months: 6);

    $september = $result->last();
    $august = $result->first(fn ($month) => $month->month->format('Y-m') === '2026-08');

    expect($september->income)->toEqual(Money::fromCents(450_00))
        ->and($september->expense)->toEqual(Money::fromCents(80_00))
        ->and($september->net())->toEqual(Money::fromCents(370_00))
        ->and($august->expense)->toEqual(Money::fromCents(20_00))
        ->and($august->income)->toEqual(Money::fromCents(0));
});

it('ignores transactions outside the window and from other users', function (): void {
    $this->travelTo('2026-09-15 12:00:00');

    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create();
    $income = Category::factory()->ownedBy($user)->income()->create();

    Transaction::factory()->forAccount($account)->forCategory($income)->amountCents(999_00)->on('2026-01-10')->create();
    Transaction::factory()->ownedBy(User::factory()->create())->amountCents(777_00)->on('2026-09-05')->create();

    $result = app(MonthlyCashFlowQuery::class)->handle($user, months: 6);

    expect($result->sum(fn ($month) => $month->income->cents))->toBe(0)
        ->and($result->sum(fn ($month) => $month->expense->cents))->toBe(0);
});
