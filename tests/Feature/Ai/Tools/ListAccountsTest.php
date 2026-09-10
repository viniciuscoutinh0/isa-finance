<?php

declare(strict_types=1);

use App\Ai\Tools\ListAccounts;
use App\Models\Account;
use App\Models\User;
use Laravel\Ai\Tools\Request;

it('returns only the given user accounts with their balance', function (): void {
    $user = User::factory()->create();
    Account::factory()->ownedBy($user)->withInitialBalance(50_00)->create(['name' => 'Nubank']);
    Account::factory()->create(['name' => 'Conta alheia']);

    $result = json_decode((new ListAccounts($user))->handle(new Request), true, flags: JSON_THROW_ON_ERROR);

    expect($result['accounts'])->toHaveCount(1)
        ->and($result['accounts'][0]['name'])->toBe('Nubank')
        ->and($result['accounts'][0]['balance_cents'])->toBe(5000)
        ->and($result['accounts'][0]['balance'])->toBe('R$ 50,00');
});

it('reports when the user has no accounts', function (): void {
    $result = json_decode((new ListAccounts(User::factory()->create()))->handle(new Request), true, flags: JSON_THROW_ON_ERROR);

    expect($result['accounts'])->toBe([])
        ->and($result['note'])->toContain('no accounts');
});
