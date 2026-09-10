<?php

declare(strict_types=1);

use App\Actions\Accounts\DeleteAccount;
use App\Exceptions\Accounts\AccountHasHistory;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;

it('hard deletes an account with no history', function (): void {
    $account = Account::factory()->create();

    app(DeleteAccount::class)->handle($account);

    $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
});

it('refuses to delete an account that still has transactions', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create();
    Transaction::factory()->ownedBy($user)->forAccount($account)->create();

    expect(fn () => app(DeleteAccount::class)->handle($account))
        ->toThrow(AccountHasHistory::class);

    $this->assertDatabaseHas('accounts', ['id' => $account->id]);
});

it('refuses to delete an account referenced by a transfer', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create();
    $other = Account::factory()->ownedBy($user)->create();
    Transfer::factory()->between($other, $account)->create();

    expect(fn () => app(DeleteAccount::class)->handle($account))
        ->toThrow(AccountHasHistory::class);
});
