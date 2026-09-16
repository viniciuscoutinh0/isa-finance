<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Data\Categories\CategoryData;
use App\Models\Category;

final readonly class UpdateCategory
{
    public function handle(Category $category, CategoryData $data): Category
    {
        $category->update($data->toArray());

        return $category;
    }
}
