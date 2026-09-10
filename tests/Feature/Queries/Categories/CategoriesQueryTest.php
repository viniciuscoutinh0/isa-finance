<?php

declare(strict_types=1);

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use App\Queries\Categories\CategoriesQuery;

it('returns only the user categories, ordered by name', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Category::factory()->ownedBy($user)->create(['name' => 'Zeladoria']);
    Category::factory()->ownedBy($user)->create(['name' => 'Aluguel']);
    Category::factory()->ownedBy($other)->create(['name' => 'Alheia']);

    $result = app(CategoriesQuery::class)->handle($user);

    expect($result->pluck('name')->all())->toBe(['Aluguel', 'Zeladoria']);
});

it('can narrow to a single type', function (): void {
    $user = User::factory()->create();
    Category::factory()->ownedBy($user)->income()->create(['name' => 'Salário']);
    Category::factory()->ownedBy($user)->expense()->create(['name' => 'Mercado']);

    $result = app(CategoriesQuery::class)->handle($user, CategoryType::Income);

    expect($result->pluck('name')->all())->toBe(['Salário']);
});
