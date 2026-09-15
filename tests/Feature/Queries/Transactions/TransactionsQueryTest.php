<?php

declare(strict_types=1);

use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Queries\Transactions\TransactionsQuery;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->ownedBy($this->user)->create();
    $this->otherAccount = Account::factory()->ownedBy($this->user)->create();
    $this->income = Category::factory()->ownedBy($this->user)->income()->create();
    $this->expense = Category::factory()->ownedBy($this->user)->expense()->create();
});

it('returns only the user transactions newest first', function (): void {
    Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->on('2026-01-10')
        ->create(['description' => 'Antiga']);

    Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->on('2026-03-01')
        ->create(['description' => 'Recente']);

    Transaction::factory()->create(['description' => 'De outro usuário']);

    $results = collect(app(TransactionsQuery::class)->handle($this->user)->items());

    expect($results->pluck('description')->all())->toBe([
        'Recente',
        'Antiga',
    ]);
});

it('filters by account', function (): void {
    Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->create(['description' => 'Nesta conta']);

    Transaction::factory()
        ->forAccount($this->otherAccount)
        ->forCategory($this->expense)
        ->create(['description' => 'Na outra']);

    $results = app(TransactionsQuery::class)->handle($this->user, [
        'accounts' => [$this->account->id],
    ]);

    expect($results)->pluck('description')->all()->toBe(['Nesta conta']);
});

it('filters by type through the category', function (): void {
    Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->income)
        ->create(['description' => 'Entrada']);
    Transaction::factory()->forAccount($this->account)->forCategory($this->expense)->create(['description' => 'Saída']);

    $results = app(TransactionsQuery::class)->handle(
        $this->user,
        ['types' => [CategoryType::Income]],
    );

    expect($results->pluck('description')->all())->toBe(['Entrada']);
});

it('filters by a case-insensitive description search', function (): void {
    Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->create(['description' => 'Farmácia São João']);
    Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->create(['description' => 'Posto Shell']);

    $results = app(TransactionsQuery::class)->handle($this->user, ['search' => 'farmácia']);

    expect($results->pluck('description')->all())->toBe(['Farmácia São João']);
});

it('filters by a date range', function (): void {
    Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->on('2026-02-01')
        ->create(['description' => 'Fevereiro']);
    Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->on('2026-04-01')
        ->create(['description' => 'Abril']);

    $results = app(TransactionsQuery::class)->handle($this->user, ['from' => '2026-03-01', 'to' => '2026-12-31']);

    expect($results->pluck('description')->all())->toBe(['Abril']);
});

it('paginates', function (): void {
    Transaction::factory()->count(25)->forAccount($this->account)->forCategory($this->expense)->create();

    $result = app(TransactionsQuery::class)->handle($this->user, [], perPage: 10);

    expect($result->perPage())
        ->toBe(10)
        ->and($result->total())
        ->toBe(25)
        ->and($result->count())
        ->toBe(10);
});
