<?php

declare(strict_types=1);

use App\Actions\Transfers\UpdateTransfer;
use App\Data\Money;
use App\Exceptions\Transfers\SameAccountTransfer;
use App\Models\Account;
use App\Models\Transfer;
use App\Models\User;
use Carbon\CarbonImmutable;

it('updates the accounts, amount and date', function (): void {
    $user = User::factory()->create();
    $transfer = Transfer::factory()->ownedBy($user)->create();

    $newFrom = Account::factory()->ownedBy($user)->create();
    $newTo = Account::factory()->ownedBy($user)->create();

    app(UpdateTransfer::class)->handle(
        $transfer,
        $newFrom,
        $newTo,
        CarbonImmutable::parse('2026-06-01'),
        Money::parse('1.000,00'),
        null,
    );

    $transfer->refresh();

    expect($transfer->from_account_id)->toBe($newFrom->id)
        ->and($transfer->to_account_id)->toBe($newTo->id)
        ->and($transfer->amount)->toBe(100000)
        ->and($transfer->date->toDateString())->toBe('2026-06-01');
});

it('refuses to collapse a transfer onto one account', function (): void {
    $user = User::factory()->create();
    $transfer = Transfer::factory()->ownedBy($user)->create();
    $account = Account::factory()->ownedBy($user)->create();

    expect(fn () => app(UpdateTransfer::class)->handle(
        $transfer,
        $account,
        $account,
        CarbonImmutable::now(),
        Money::parse('10,00'),
        null,
    ))->toThrow(SameAccountTransfer::class);
});
