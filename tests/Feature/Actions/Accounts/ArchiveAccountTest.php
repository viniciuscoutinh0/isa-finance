<?php

declare(strict_types=1);

use App\Actions\Accounts\ArchiveAccount;
use App\Actions\Accounts\UnarchiveAccount;
use App\Models\Account;

it('archives an active account', function (): void {
    $account = Account::factory()->create();

    app(ArchiveAccount::class)->handle($account);

    expect($account->fresh()->archived_at)->not->toBeNull();
});

it('is a no-op when the account is already archived', function (): void {
    $account = Account::factory()->archived()->create();
    $archivedAt = $account->archived_at;

    app(ArchiveAccount::class)->handle($account);

    expect($account->fresh()->archived_at->equalTo($archivedAt))->toBeTrue();
});

it('reactivates an archived account', function (): void {
    $account = Account::factory()->archived()->create();

    app(UnarchiveAccount::class)->handle($account);

    expect($account->fresh()->archived_at)->toBeNull();
});
