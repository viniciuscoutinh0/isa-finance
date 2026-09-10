<?php

declare(strict_types=1);

use App\Actions\Categories\CreateCategory;
use App\Enums\CategoryType;
use App\Models\User;

it('creates a category owned by the user', function (): void {
    $user = User::factory()->create();

    $category = app(CreateCategory::class)->handle($user, 'Mercado', CategoryType::Expense);

    expect($category->name)->toBe('Mercado')
        ->and($category->type)->toBe(CategoryType::Expense)
        ->and($category->user_id)->toBe($user->id);

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name' => 'Mercado',
        'type' => 'expense',
    ]);
});
