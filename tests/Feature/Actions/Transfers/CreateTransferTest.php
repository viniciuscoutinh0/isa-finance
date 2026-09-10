<?php

declare(strict_types=1);

use App\Actions\Transfers\CreateTransfer;
use App\Data\Money;
use App\Exceptions\Transfers\SameAccountTransfer;
use App\Models\Account;
use App\Models\User;
use Carbon\CarbonImmutable;

it('creates a transfer between two accounts of the user', function (): void {
    $user = User::factory()->create();
    $from = Account::factory()->ownedBy($user)->create();
    $to = Account::factory()->ownedBy($user)->create();

    $transfer = app(CreateTransfer::class)->handle(
        $user,
        $from,
        $to,
        CarbonImmutable::parse('2026-02-20'),
        Money::parse('750,00'),
        'pagamento da fatura',
    );

    expect($transfer->user_id)->toBe($user->id)
        ->and($transfer->from_account_id)->toBe($from->id)
        ->and($transfer->to_account_id)->toBe($to->id)
        ->and($transfer->amount)->toBe(75000)
        ->and($transfer->date->toDateString())->toBe('2026-02-20')
        ->and($transfer->notes)->toBe('pagamento da fatura');
});

it('refuses a transfer to the same account', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create();

    expect(fn () => app(CreateTransfer::class)->handle(
        $user,
        $account,
        $account,
        CarbonImmutable::now(),
        Money::parse('10,00'),
        null,
    ))->toThrow(SameAccountTransfer::class);

    $this->assertDatabaseCount('transfers', 0);
});
