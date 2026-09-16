<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Data\Categories\CategoryData;
use App\Models\Category;
use App\Models\User;

final readonly class CreateCategory
{
    public function handle(User $user, CategoryData $data): Category
    {
        return $user->categories()->create($data->toArray());
    }
}
