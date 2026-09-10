<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;

it('lets any user list and create categories', function (): void {
    $user = User::factory()->create();

    expect($user->can('viewAny', Category::class))->toBeTrue()
        ->and($user->can('create', Category::class))->toBeTrue();
});

it('lets the owner view, update and delete their category', function (string $ability): void {
    $user = User::factory()->create();
    $category = Category::factory()->ownedBy($user)->create();

    expect($user->can($ability, $category))->toBeTrue();
})->with(['view', 'update', 'delete']);

it('denies acting on someone else category', function (string $ability): void {
    $category = Category::factory()->create();
    $intruder = User::factory()->create();

    expect($intruder->can($ability, $category))->toBeFalse();
})->with(['view', 'update', 'delete']);
