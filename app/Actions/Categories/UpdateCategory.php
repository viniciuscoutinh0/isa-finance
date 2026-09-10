<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Enums\CategoryType;
use App\Models\Category;

final readonly class UpdateCategory
{
    public function handle(Category $category, string $name, CategoryType $type): Category
    {
        $category->update([
            'name' => $name,
            'type' => $type,
        ]);

        return $category;
    }
}
