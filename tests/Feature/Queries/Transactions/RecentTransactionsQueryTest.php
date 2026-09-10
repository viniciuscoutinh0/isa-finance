<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Queries\Transactions\RecentTransactionsQuery;

it('returns the newest transactions limited to the given count', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create();
    $category = Category::factory()->ownedBy($user)->expense()->create();

    foreach (range(1, 15) as $day) {
        Transaction::factory()->forAccount($account)->forCategory($category)
            ->on(sprintf('2026-03-%02d', $day))
            ->create(['description' => "Dia {$day}"]);
    }

    $result = app(RecentTransactionsQuery::class)->handle($user, limit: 5);

    expect($result)->toHaveCount(5)
        ->and($result->first()->description)->toBe('Dia 15')
        ->and($result->last()->description)->toBe('Dia 11');
});

it('ignores other users', function (): void {
    $user = User::factory()->create();
    Transaction::factory()->ownedBy($user)->create(['description' => 'Minha']);
    Transaction::factory()->create(['description' => 'Alheia']);

    $result = app(RecentTransactionsQuery::class)->handle($user);

    expect($result->pluck('description')->all())->toBe(['Minha']);
});
