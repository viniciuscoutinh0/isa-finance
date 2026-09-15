<?php

declare(strict_types=1);

use App\Actions\Transactions\UpdateTransaction;
use App\Data\Transactions\TransactionData;
use App\Exceptions\Transactions\TransactionException;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

it('moves a transaction to another account and category and changes the amount', function (): void {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->ownedBy($user)->create();

    $newAccount = Account::factory()->ownedBy($user)->create();
    $newCategory = Category::factory()->ownedBy($user)->income()->create();

    app(UpdateTransaction::class)->handle($user, $transaction, TransactionData::fromArray([
        'account_id' => $newAccount->id,
        'category_id' => $newCategory->id,
        'date' => '2026-01-02',
        'description' => 'Reembolso',
        'amount' => '80,00',
        'notes' => null,
    ]));

    $transaction->refresh();

    expect($transaction->account_id)->toBe($newAccount->id)
        ->and($transaction->category_id)->toBe($newCategory->id)
        ->and($transaction->amount)->toBe(8000)
        ->and($transaction->description)->toBe('Reembolso')
        ->and($transaction->date->toDateString())->toBe('2026-01-02');
});

it('refuses an account that belongs to another user', function (): void {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->ownedBy($user)->create();
    $strangersAccount = Account::factory()->create();
    $category = Category::factory()->ownedBy($user)->expense()->create();

    $call = fn () => app(UpdateTransaction::class)->handle($user, $transaction, TransactionData::fromArray([
        'account_id' => $strangersAccount->id,
        'category_id' => $category->id,
        'date' => '2026-01-02',
        'description' => 'Tentativa',
        'amount' => '80,00',
        'notes' => null,
    ]));

    expect($call)->toThrow(TransactionException::class);

    expect($transaction->refresh()->description)->not->toBe('Tentativa');
});

it('refuses a category that belongs to another user', function (): void {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->ownedBy($user)->create();
    $account = Account::factory()->ownedBy($user)->create();
    $strangersCategory = Category::factory()->expense()->create();

    $call = fn () => app(UpdateTransaction::class)->handle($user, $transaction, TransactionData::fromArray([
        'account_id' => $account->id,
        'category_id' => $strangersCategory->id,
        'date' => '2026-01-02',
        'description' => 'Tentativa',
        'amount' => '80,00',
        'notes' => null,
    ]));

    expect($call)->toThrow(TransactionException::class);

    expect($transaction->refresh()->description)->not->toBe('Tentativa');
});
