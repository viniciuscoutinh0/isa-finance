<?php

declare(strict_types=1);

use App\Actions\Accounts\CreateAccount;
use App\Data\Money;
use App\Enums\AccountType;
use App\Models\User;

it('creates an account with an initial balance in centavos', function (): void {
    $user = User::factory()->create();

    $account = app(CreateAccount::class)->handle(
        $user,
        'Nubank',
        AccountType::Checking,
        Money::parse('1.234,56'),
    );

    expect($account->name)->toBe('Nubank')
        ->and($account->type)->toBe(AccountType::Checking)
        ->and($account->initial_balance)->toBe(123456)
        ->and($account->user_id)->toBe($user->id)
        ->and($account->archived_at)->toBeNull();
});

it('accepts a negative initial balance for a credit card', function (): void {
    $account = app(CreateAccount::class)->handle(
        User::factory()->create(),
        'Cartão Inter',
        AccountType::CreditCard,
        Money::parse('-500,00'),
    );

    expect($account->initial_balance)->toBe(-50000);
});
