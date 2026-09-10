<?php

declare(strict_types=1);

namespace App\Exceptions\Categories;

use App\Models\Category;

final class CategoryInUse extends CategoryException
{
    public static function for(Category $category): self
    {
        return new self("Category [{$category->id}] still has transactions and cannot be deleted.");
    }
}
