<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\Category;
use App\Models\User;
use App\Rules\Categories\CategoryRules;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Form;

final class CategoryForm extends Form
{
    #[Locked]
    public ?Category $category = null;

    public string $name = '';

    public string $type = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = Auth::user();

        return CategoryRules::for($user, $this->type, $this->category?->id);
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return CategoryRules::attributes();
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;

        $this->name = $category->name;
        $this->type = $category->type->value;
    }
}
