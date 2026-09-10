<?php

declare(strict_types=1);

use App\Ai\Tools\ListTransfers;
use App\Models\Account;
use App\Models\Transfer;
use App\Models\User;
use Laravel\Ai\Tools\Request;

it('returns only the user transfers with both account names and formatted amount', function (): void {
    $user = User::factory()->create();
    $from = Account::factory()->ownedBy($user)->create(['name' => 'Nubank']);
    $to = Account::factory()->ownedBy($user)->create(['name' => 'Carteira']);

    Transfer::factory()->between($from, $to)->on('2026-03-10')->amountCents(200_00)->create();
    Transfer::factory()->create(); // another user

    $result = json_decode((new ListTransfers($user))->handle(new Request), true, flags: JSON_THROW_ON_ERROR);

    expect($result['total_count'])->toBe(1)
        ->and($result['transfers'][0]['from'])->toBe('Nubank')
        ->and($result['transfers'][0]['to'])->toBe('Carteira')
        ->and($result['transfers'][0]['amount'])->toBe('R$ 200,00');
});

it('matches an account filter on either side of the transfer', function (): void {
    $user = User::factory()->create();
    $a = Account::factory()->ownedBy($user)->create(['name' => 'Conta A']);
    $b = Account::factory()->ownedBy($user)->create(['name' => 'Conta B']);
    $c = Account::factory()->ownedBy($user)->create(['name' => 'Conta C']);

    Transfer::factory()->between($a, $b)->create();
    Transfer::factory()->between($b, $c)->create();

    $result = json_decode((new ListTransfers($user))->handle(new Request(['account' => 'conta b'])), true, flags: JSON_THROW_ON_ERROR);

    expect($result['total_count'])->toBe(2)
        ->and($result['notes'])->toBe([]);
});
