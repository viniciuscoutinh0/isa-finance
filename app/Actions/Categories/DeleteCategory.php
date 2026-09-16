<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Exceptions\Categories\CategoryInUse;
use App\Models\Category;

final readonly class DeleteCategory
{
    /**
     * @throws CategoryInUse
     */
    public function handle(Category $category): void
    {
        if ($category->transactions()->exists()) {
            throw CategoryInUse::for($category);
        }

        $category->delete();
    }
}
