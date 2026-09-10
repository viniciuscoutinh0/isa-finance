<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\User;
use App\Queries\Accounts\AccountsQuery;

it('returns the user active accounts ordered by name', function (): void {
    $user = User::factory()->create();
    Account::factory()->ownedBy($user)->create(['name' => 'Zaffari']);
    Account::factory()->ownedBy($user)->create(['name' => 'Ame']);
    Account::factory()->ownedBy($user)->archived()->create(['name' => 'Antiga']);
    Account::factory()->create(['name' => 'De outro usuário']);

    $result = app(AccountsQuery::class)->handle($user);

    expect($result->pluck('name')->all())->toBe(['Ame', 'Zaffari']);
});

it('includes archived accounts when asked', function (): void {
    $user = User::factory()->create();
    Account::factory()->ownedBy($user)->create(['name' => 'Ativa']);
    Account::factory()->ownedBy($user)->archived()->create(['name' => 'Arquivada']);

    $result = app(AccountsQuery::class)->handle($user, includeArchived: true);

    expect($result->pluck('name')->all())->toBe(['Arquivada', 'Ativa']);
});
