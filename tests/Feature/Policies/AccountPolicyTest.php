<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\User;

it('lets any user list and create accounts', function (): void {
    $user = User::factory()->create();

    expect($user->can('viewAny', Account::class))->toBeTrue()
        ->and($user->can('create', Account::class))->toBeTrue();
});

it('lets the owner view, update and delete their account', function (string $ability): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create();

    expect($user->can($ability, $account))->toBeTrue();
})->with(['view', 'update', 'delete']);

it('denies acting on someone else account', function (string $ability): void {
    $account = Account::factory()->create();
    $intruder = User::factory()->create();

    expect($intruder->can($ability, $account))->toBeFalse();
})->with(['view', 'update', 'delete']);
