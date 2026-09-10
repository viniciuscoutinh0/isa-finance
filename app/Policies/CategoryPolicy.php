<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

final class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Category $category): bool
    {
        return $this->owns($user, $category);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Category $category): bool
    {
        return $this->owns($user, $category);
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->owns($user, $category);
    }

    private function owns(User $user, Category $category): bool
    {
        return $user->id === $category->user_id;
    }
}
