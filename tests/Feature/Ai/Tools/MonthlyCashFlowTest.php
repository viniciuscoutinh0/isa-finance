<?php

declare(strict_types=1);

use App\Ai\Tools\MonthlyCashFlow;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Laravel\Ai\Tools\Request;

it('returns income and expense totals per month for the user, current month last', function (): void {
    CarbonImmutable::setTestNow('2026-09-15');

    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create();
    $income = Category::factory()->ownedBy($user)->income()->create();
    $expense = Category::factory()->ownedBy($user)->expense()->create();

    Transaction::factory()->forAccount($account)->forCategory($income)->on('2026-09-05')->amountCents(300_00)->create();
    Transaction::factory()->forAccount($account)->forCategory($expense)->on('2026-09-09')->amountCents(120_00)->create();
    Transaction::factory()->create(); // another user, another month

    $result = json_decode((new MonthlyCashFlow($user))->handle(new Request(['months' => 3])), true, flags: JSON_THROW_ON_ERROR);

    expect($result['months'])->toHaveCount(3);

    $current = end($result['months']);

    expect($current['month'])->toBe('2026-09')
        ->and($current['income_cents'])->toBe(30000)
        ->and($current['expense_cents'])->toBe(12000)
        ->and($current['net'])->toBe('R$ 180,00');
});

it('clamps the months argument to a sane range', function (): void {
    $result = json_decode(
        (new MonthlyCashFlow(User::factory()->create()))->handle(new Request(['months' => 999])),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($result['months'])->toHaveCount(12);
});
