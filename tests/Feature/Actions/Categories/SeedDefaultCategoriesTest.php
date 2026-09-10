<?php

declare(strict_types=1);

use App\Actions\Categories\SeedDefaultCategories;
use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;

it('gives a new user the full starter set', function (): void {
    $user = User::factory()->create();

    app(SeedDefaultCategories::class)->handle($user);

    $categories = Category::where('user_id', $user->id)->get();

    expect($categories)->toHaveCount(15)
        ->and($categories->where('type', CategoryType::Income)->pluck('name'))
        ->toContain('Salário')
        ->and($categories->where('type', CategoryType::Expense)->pluck('name'))
        ->toContain('Mercado', 'Moradia', 'Outros');
});

it('only seeds the given user', function (): void {
    $mine = User::factory()->create();
    $other = User::factory()->create();

    app(SeedDefaultCategories::class)->handle($mine);

    expect(Category::where('user_id', $other->id)->count())->toBe(0);
});
