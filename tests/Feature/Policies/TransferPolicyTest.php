<?php

declare(strict_types=1);

use App\Models\Transfer;
use App\Models\User;

it('lets any user list and create transfers', function (): void {
    $user = User::factory()->create();

    expect($user->can('viewAny', Transfer::class))->toBeTrue()
        ->and($user->can('create', Transfer::class))->toBeTrue();
});

it('lets the owner view, update and delete their transfer', function (string $ability): void {
    $user = User::factory()->create();
    $transfer = Transfer::factory()->ownedBy($user)->create();

    expect($user->can($ability, $transfer))->toBeTrue();
})->with(['view', 'update', 'delete']);

it('denies acting on someone else transfer', function (string $ability): void {
    $transfer = Transfer::factory()->create();
    $intruder = User::factory()->create();

    expect($intruder->can($ability, $transfer))->toBeFalse();
})->with(['view', 'update', 'delete']);
