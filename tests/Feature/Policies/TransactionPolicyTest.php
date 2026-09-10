<?php

declare(strict_types=1);

use App\Models\Transaction;
use App\Models\User;

it('lets any user list and create transactions', function (): void {
    $user = User::factory()->create();

    expect($user->can('viewAny', Transaction::class))->toBeTrue()
        ->and($user->can('create', Transaction::class))->toBeTrue();
});

it('lets the owner view, update and delete their transaction', function (string $ability): void {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->ownedBy($user)->create();

    expect($user->can($ability, $transaction))->toBeTrue();
})->with(['view', 'update', 'delete']);

it('denies acting on someone else transaction', function (string $ability): void {
    $transaction = Transaction::factory()->create();
    $intruder = User::factory()->create();

    expect($intruder->can($ability, $transaction))->toBeFalse();
})->with(['view', 'update', 'delete']);
