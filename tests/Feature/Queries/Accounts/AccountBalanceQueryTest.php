<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Queries\Accounts\AccountBalanceQuery;

it('computes a single account balance', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->withInitialBalance(1_000_00)->create();
    $income = Category::factory()->ownedBy($user)->income()->create();
    $expense = Category::factory()->ownedBy($user)->expense()->create();

    Transaction::factory()->forAccount($account)->forCategory($income)->amountCents(250_00)->create();
    Transaction::factory()->forAccount($account)->forCategory($expense)->amountCents(400_00)->create();

    // 1000 + 250 - 400 = 850
    expect(app(AccountBalanceQuery::class)->handle($account)->cents)->toBe(850_00);
});

it('returns the initial balance for an untouched account', function (): void {
    $account = Account::factory()->withInitialBalance(42_00)->create();

    expect(app(AccountBalanceQuery::class)->handle($account)->cents)->toBe(42_00);
});

it('applies transfers in and out', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->withInitialBalance(500_00)->create();
    $other = Account::factory()->ownedBy($user)->create();

    Transfer::factory()->between($account, $other)->amountCents(200_00)->create();
    Transfer::factory()->between($other, $account)->amountCents(50_00)->create();

    // 500 - 200 + 50 = 350
    expect(app(AccountBalanceQuery::class)->handle($account)->cents)->toBe(350_00);
});
