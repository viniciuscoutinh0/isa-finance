<?php

declare(strict_types=1);

use App\Actions\Categories\UpdateCategory;
use App\Enums\CategoryType;
use App\Models\Category;

it('renames a category and changes its type', function (): void {
    $category = Category::factory()->expense()->create(['name' => 'Mercado']);

    app(UpdateCategory::class)->handle($category, 'Supermercado', CategoryType::Expense);

    expect($category->fresh()->name)->toBe('Supermercado');
});

it('can flip a category from expense to income', function (): void {
    $category = Category::factory()->expense()->create();

    app(UpdateCategory::class)->handle($category, $category->name, CategoryType::Income);

    expect($category->fresh()->type)->toBe(CategoryType::Income);
});
