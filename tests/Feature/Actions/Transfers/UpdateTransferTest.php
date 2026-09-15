<?php

declare(strict_types=1);

use App\Actions\Transfers\UpdateTransfer;
use App\Data\Transfers\TransferData;
use App\Exceptions\Transfers\SameAccountTransfer;
use App\Exceptions\Transfers\UnknownTransferAccount;
use App\Models\Account;
use App\Models\Transfer;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->transfer = Transfer::factory()->ownedBy($this->user)->create();
});

it('updates the accounts, amount and date', function (): void {
    $newFrom = Account::factory()->ownedBy($this->user)->create();
    $newTo = Account::factory()->ownedBy($this->user)->create();

    app(UpdateTransfer::class)->handle($this->user, $this->transfer, TransferData::fromArray([
        'from_account_id' => $newFrom->id,
        'to_account_id' => $newTo->id,
        'date' => '2026-06-01',
        'amount' => '1.000,00',
        'notes' => null,
    ]));

    $this->transfer->refresh();

    expect($this->transfer->from_account_id)->toBe($newFrom->id)
        ->and($this->transfer->to_account_id)->toBe($newTo->id)
        ->and($this->transfer->amount)->toBe(100000)
        ->and($this->transfer->date->toDateString())->toBe('2026-06-01');
});

it('refuses to collapse a transfer onto one account', function (): void {
    $account = Account::factory()->ownedBy($this->user)->create();

    $call = fn () => app(UpdateTransfer::class)->handle($this->user, $this->transfer, TransferData::fromArray([
        'from_account_id' => $account->id,
        'to_account_id' => $account->id,
        'date' => '2026-06-01',
        'amount' => '10,00',
        'notes' => null,
    ]));

    expect($call)->toThrow(SameAccountTransfer::class);
});

it('refuses an account that belongs to another user', function (): void {
    $account = Account::factory()->ownedBy($this->user)->create();
    $strangersAccount = Account::factory()->create();

    $call = fn () => app(UpdateTransfer::class)->handle($this->user, $this->transfer, TransferData::fromArray([
        'from_account_id' => $account->id,
        'to_account_id' => $strangersAccount->id,
        'date' => '2026-06-01',
        'amount' => '10,00',
        'notes' => null,
    ]));

    expect($call)->toThrow(UnknownTransferAccount::class);
});
