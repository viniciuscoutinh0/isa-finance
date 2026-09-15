<?php

declare(strict_types=1);

use App\Actions\Transactions\CreateTransaction;
use App\Data\Transactions\TransactionData;
use App\Models\Account;
use App\Models\Category;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->ownedBy($this->user)->create();
    $this->category = Category::factory()->ownedBy($this->user)->expense()->create();
});

it('creates a transaction linked to the account, category and user', function (): void {
    $data = TransactionData::fromArray([
        'accountId' => $this->account->id,
        'categoryId' => $this->category->id,
        'date' => '2026-03-15',
        'description' => 'Mercado do mês',
        'amount' => '345,90',
        'notes' => 'compra grande',
    ]);

    $record = app(CreateTransaction::class)->handle($this->user, $data);

    expect($record->user_id)
        ->toBe($this->user->id)
        ->and($record->account_id)
        ->toBe($this->account->id)
        ->and($record->category_id)
        ->toBe($this->category->id)
        ->and($record->amount)
        ->toBe(34590)
        ->and($record->date->toDateString())
        ->toBe('2026-03-15')
        ->and($record->notes)
        ->toBe('compra grande');
});

it('allows a null note', function (): void {
    $data = TransactionData::fromArray([
        'accountId' => $this->account->id,
        'categoryId' => $this->category->id,
        'date' => '2026-03-15',
        'description' => 'Mercado do mês',
        'amount' => '345,90',
        'notes' => null,
    ]);

    $record = app(CreateTransaction::class)->handle($this->user, $data);

    expect($record->notes)->toBeNull();
});
