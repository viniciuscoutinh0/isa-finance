<?php

declare(strict_types=1);

use App\Ai\Tools\CreateTransferTool;
use App\Models\Account;
use App\Models\User;
use Laravel\Ai\Tools\Request;

it('records the transfer once both picked accounts are supplied', function (): void {
    $user = User::factory()->create();
    $from = Account::factory()->ownedBy($user)->create(['name' => 'Nubank']);
    $to = Account::factory()->ownedBy($user)->create(['name' => 'Carteira']);

    $message = (new CreateTransferTool($user))->handle(new Request([
        'amount' => '200,00',
        'date' => '2026-03-10',
        'from_account_id' => $from->id,
        'to_account_id' => $to->id,
    ]));

    expect($message)->toContain('R$ 200,00')->toContain('Nubank')->toContain('Carteira');

    $this->assertDatabaseHas('transfers', [
        'user_id' => $user->id,
        'from_account_id' => $from->id,
        'to_account_id' => $to->id,
        'amount' => 20000,
    ]);
});

it('refuses a transfer to the same account without creating anything', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create();

    $message = (new CreateTransferTool($user))->handle(new Request([
        'amount' => '50,00',
        'from_account_id' => $account->id,
        'to_account_id' => $account->id,
    ]));

    expect($message)->toContain('Could not record the transfer');
    $this->assertDatabaseCount('transfers', 0);
});

it('refuses an account that belongs to another user', function (): void {
    $user = User::factory()->create();
    $mine = Account::factory()->ownedBy($user)->create();
    $strangers = Account::factory()->create();

    $message = (new CreateTransferTool($user))->handle(new Request([
        'amount' => '50,00',
        'from_account_id' => $mine->id,
        'to_account_id' => $strangers->id,
    ]));

    expect($message)->toContain('Could not record the transfer');
    $this->assertDatabaseCount('transfers', 0);
});
