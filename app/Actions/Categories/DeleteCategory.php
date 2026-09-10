<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Exceptions\Categories\CategoryInUse;
use App\Models\Category;

final readonly class DeleteCategory
{
    /**
     * Hard delete. A category that still has transactions is refused so the
     * user reassigns or deletes them first.
     *
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
