<?php

declare(strict_types=1);

use App\Actions\Categories\DeleteCategory;
use App\Exceptions\Categories\CategoryInUse;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

it('hard deletes a category with no transactions', function (): void {
    $category = Category::factory()->create();

    app(DeleteCategory::class)->handle($category);

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

it('refuses to delete a category that still has transactions', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->ownedBy($user)->expense()->create();
    Transaction::factory()->ownedBy($user)->forCategory($category)->create();

    expect(fn () => app(DeleteCategory::class)->handle($category))
        ->toThrow(CategoryInUse::class);

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});
