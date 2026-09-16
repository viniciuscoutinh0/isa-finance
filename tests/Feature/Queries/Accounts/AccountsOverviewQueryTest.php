<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Queries\Accounts\AccountsOverviewQuery;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->income = Category::factory()->ownedBy($this->user)->income()->create();
    $this->expense = Category::factory()->ownedBy($this->user)->expense()->create();
});

it('derives the balance from initial balance plus income minus expense', function (): void {
    $account = Account::factory()->ownedBy($this->user)->withInitialBalance(100_00)->create(['name' => 'Nubank']);

    Transaction::factory()->forAccount($account)->forCategory($this->income)->amountCents(300_00)->create();
    Transaction::factory()->forAccount($account)->forCategory($this->income)->amountCents(50_00)->create();
    Transaction::factory()->forAccount($account)->forCategory($this->expense)->amountCents(120_00)->create();

    $overview = app(AccountsOverviewQuery::class)->handle($this->user);

    expect($overview)->toHaveCount(1)
        ->and($overview->first()->balance->cents)->toBe(330_00)
        ->and($overview->first()->initialBalance->cents)->toBe(100_00);
});

it('returns the initial balance when there are no transactions', function (): void {
    Account::factory()->ownedBy($this->user)->withInitialBalance(-1_290_45)->create(['name' => 'Cartão']);

    $overview = app(AccountsOverviewQuery::class)->handle($this->user);

    expect($overview->first()->balance->cents)->toBe(-1_290_45);
});

it('keeps each account balance independent', function (): void {
    $a = Account::factory()->ownedBy($this->user)->withInitialBalance(0)->create(['name' => 'A']);
    $b = Account::factory()->ownedBy($this->user)->withInitialBalance(0)->create(['name' => 'B']);

    Transaction::factory()->forAccount($a)->forCategory($this->income)->amountCents(200_00)->create();
    Transaction::factory()->forAccount($b)->forCategory($this->expense)->amountCents(75_00)->create();

    $overview = app(AccountsOverviewQuery::class)->handle($this->user)->keyBy('name');

    expect($overview['A']->balance->cents)->toBe(200_00)
        ->and($overview['B']->balance->cents)->toBe(-75_00);
});

it('moves the transfer amount out of one balance and into the other', function (): void {
    $from = Account::factory()->ownedBy($this->user)->withInitialBalance(1_000_00)->create(['name' => 'From']);
    $to = Account::factory()->ownedBy($this->user)->withInitialBalance(200_00)->create(['name' => 'To']);

    Transfer::factory()->between($from, $to)->amountCents(300_00)->create();

    $overview = app(AccountsOverviewQuery::class)->handle($this->user)->keyBy('name');

    expect($overview['From']->balance->cents)->toBe(700_00)
        ->and($overview['To']->balance->cents)->toBe(500_00);
});

it('combines transactions and transfers in one balance', function (): void {
    $account = Account::factory()->ownedBy($this->user)->withInitialBalance(0)->create(['name' => 'Mix']);
    $other = Account::factory()->ownedBy($this->user)->create(['name' => 'Other']);

    Transaction::factory()->forAccount($account)->forCategory($this->income)->amountCents(500_00)->create();
    Transaction::factory()->forAccount($account)->forCategory($this->expense)->amountCents(120_00)->create();
    Transfer::factory()->between($account, $other)->amountCents(80_00)->create();
    Transfer::factory()->between($other, $account)->amountCents(30_00)->create();

    expect(app(AccountsOverviewQuery::class)->handle($this->user)->keyBy('name')['Mix']->balance->cents)
        ->toBe(330_00);
});

it('excludes archived accounts unless asked', function (): void {
    Account::factory()->ownedBy($this->user)->create(['name' => 'Ativa']);
    Account::factory()->ownedBy($this->user)->archived()->create(['name' => 'Arquivada']);

    expect(app(AccountsOverviewQuery::class)->handle($this->user)->pluck('name')->all())
        ->toBe(['Ativa'])
        ->and(app(AccountsOverviewQuery::class)->handle($this->user, includeArchived: true)->pluck('name')->all())
        ->toBe(['Arquivada', 'Ativa']);
});

it('ignores another user accounts and transactions', function (): void {
    $mine = Account::factory()->ownedBy($this->user)->withInitialBalance(500_00)->create(['name' => 'Minha']);
    Transaction::factory()->forAccount($mine)->forCategory($this->income)->amountCents(100_00)->create();

    $other = User::factory()->create();
    $otherAccount = Account::factory()->ownedBy($other)->withInitialBalance(999_00)->create();
    Transaction::factory()->forAccount($otherAccount)
        ->forCategory(Category::factory()->ownedBy($other)->income()->create())
        ->amountCents(999_00)->create();

    $overview = app(AccountsOverviewQuery::class)->handle($this->user);

    expect($overview)->toHaveCount(1)
        ->and($overview->first()->balance->cents)->toBe(600_00);
});
