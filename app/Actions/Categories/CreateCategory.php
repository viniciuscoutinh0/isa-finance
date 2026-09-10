<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;

final readonly class CreateCategory
{
    public function handle(User $user, string $name, CategoryType $type): Category
    {
        return $user->categories()->create([
            'name' => $name,
            'type' => $type,
        ]);
    }
}
