<?php

declare(strict_types=1);

use App\Actions\Accounts\UpdateAccount;
use App\Data\Accounts\AccountData;
use App\Enums\AccountType;
use App\Models\Account;

it('updates name, type and initial balance', function (): void {
    $account = Account::factory()->ofType(AccountType::Checking)->withInitialBalance(1000)->create();

    app(UpdateAccount::class)->handle($account, AccountData::fromArray([
        'name' => 'Itaú',
        'type' => 'savings',
        'initial_balance' => '2.500,00',
    ]));

    $account->refresh();

    expect($account->name)->toBe('Itaú')
        ->and($account->type)->toBe(AccountType::Savings)
        ->and($account->initial_balance)->toBe(250000);
});
