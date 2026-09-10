<?php

declare(strict_types=1);

use App\Ai\Tools\ListCategories;
use App\Models\Category;
use App\Models\User;
use Laravel\Ai\Tools\Request;

it('returns only the given user categories with their type label', function (): void {
    $user = User::factory()->create();
    Category::factory()->ownedBy($user)->expense()->create(['name' => 'Mercado']);
    Category::factory()->ownedBy($user)->income()->create(['name' => 'Salário']);
    Category::factory()->create(['name' => 'Categoria alheia']);

    $result = json_decode((new ListCategories($user))->handle(new Request), true, flags: JSON_THROW_ON_ERROR);

    expect($result['categories'])->toHaveCount(2)
        ->and(collect($result['categories'])->pluck('name')->all())->toEqualCanonicalizing(['Mercado', 'Salário'])
        ->and(collect($result['categories'])->firstWhere('name', 'Mercado')['type'])->toBe('Saída');
});
