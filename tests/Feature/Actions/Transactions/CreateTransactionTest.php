<?php

declare(strict_types=1);

use App\Actions\Transactions\CreateTransaction;
use App\Data\Money;
use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use Carbon\CarbonImmutable;

it('creates a transaction linked to the account, category and user', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create();
    $category = Category::factory()->ownedBy($user)->expense()->create();

    $transaction = app(CreateTransaction::class)->handle(
        $user,
        $account,
        $category,
        CarbonImmutable::parse('2026-03-15'),
        'Mercado do mês',
        Money::parse('345,90'),
        'compra grande',
    );

    expect($transaction->user_id)->toBe($user->id)
        ->and($transaction->account_id)->toBe($account->id)
        ->and($transaction->category_id)->toBe($category->id)
        ->and($transaction->amount)->toBe(34590)
        ->and($transaction->date->toDateString())->toBe('2026-03-15')
        ->and($transaction->notes)->toBe('compra grande');
});

it('allows a null note', function (): void {
    $user = User::factory()->create();

    $transaction = app(CreateTransaction::class)->handle(
        $user,
        Account::factory()->ownedBy($user)->create(),
        Category::factory()->ownedBy($user)->income()->create(),
        CarbonImmutable::now(),
        'Salário',
        Money::parse('5.000,00'),
        null,
    );

    expect($transaction->notes)->toBeNull();
});
