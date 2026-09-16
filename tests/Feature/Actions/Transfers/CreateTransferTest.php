<?php

declare(strict_types=1);

use App\Actions\Transfers\CreateTransfer;
use App\Data\Transfers\TransferData;
use App\Exceptions\Transfers\SameAccountTransfer;
use App\Exceptions\Transfers\UnknownTransferAccount;
use App\Models\Account;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->from = Account::factory()->ownedBy($this->user)->create();
    $this->to = Account::factory()->ownedBy($this->user)->create();
});

it('creates a transfer between two accounts of the user', function (): void {
    $transfer = app(CreateTransfer::class)->handle($this->user, TransferData::fromArray([
        'from_account_id' => $this->from->id,
        'to_account_id' => $this->to->id,
        'date' => '2026-02-20',
        'amount' => '750,00',
        'notes' => 'pagamento da fatura',
    ]));

    expect($transfer->user_id)->toBe($this->user->id)
        ->and($transfer->from_account_id)->toBe($this->from->id)
        ->and($transfer->to_account_id)->toBe($this->to->id)
        ->and($transfer->amount)->toBe(75000)
        ->and($transfer->date->toDateString())->toBe('2026-02-20')
        ->and($transfer->notes)->toBe('pagamento da fatura');
});

it('refuses a transfer to the same account', function (): void {
    $call = fn () => app(CreateTransfer::class)->handle($this->user, TransferData::fromArray([
        'from_account_id' => $this->from->id,
        'to_account_id' => $this->from->id,
        'date' => '2026-02-20',
        'amount' => '10,00',
        'notes' => null,
    ]));

    expect($call)->toThrow(SameAccountTransfer::class);

    $this->assertDatabaseCount('transfers', 0);
});

it('refuses an account that belongs to another user', function (): void {
    $strangersAccount = Account::factory()->create();

    $call = fn () => app(CreateTransfer::class)->handle($this->user, TransferData::fromArray([
        'from_account_id' => $strangersAccount->id,
        'to_account_id' => $this->to->id,
        'date' => '2026-02-20',
        'amount' => '10,00',
        'notes' => null,
    ]));

    expect($call)->toThrow(UnknownTransferAccount::class);

    $this->assertDatabaseCount('transfers', 0);
});
