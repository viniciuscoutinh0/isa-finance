<?php

declare(strict_types=1);

use App\Actions\Accounts\UpdateAccount;
use App\Data\Money;
use App\Enums\AccountType;
use App\Models\Account;

it('updates name, type and initial balance', function (): void {
    $account = Account::factory()->ofType(AccountType::Checking)->withInitialBalance(1000)->create();

    app(UpdateAccount::class)->handle($account, 'Itaú', AccountType::Savings, Money::fromCents(250000));

    $account->refresh();

    expect($account->name)->toBe('Itaú')
        ->and($account->type)->toBe(AccountType::Savings)
        ->and($account->initial_balance)->toBe(250000);
});
