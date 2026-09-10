<?php

declare(strict_types=1);

use App\Actions\Transactions\UpdateTransaction;
use App\Data\Money;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;

it('moves a transaction to another account and category and changes the amount', function (): void {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->ownedBy($user)->create();

    $newAccount = Account::factory()->ownedBy($user)->create();
    $newCategory = Category::factory()->ownedBy($user)->income()->create();

    app(UpdateTransaction::class)->handle(
        $transaction,
        $newAccount,
        $newCategory,
        CarbonImmutable::parse('2026-01-02'),
        'Reembolso',
        Money::parse('80,00'),
        null,
    );

    $transaction->refresh();

    expect($transaction->account_id)->toBe($newAccount->id)
        ->and($transaction->category_id)->toBe($newCategory->id)
        ->and($transaction->amount)->toBe(8000)
        ->and($transaction->date->toDateString())->toBe('2026-01-02');
});
