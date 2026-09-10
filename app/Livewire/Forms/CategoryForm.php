<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class CategoryForm extends Form
{
    public ?int $categoryId = null;

    public string $name = '';

    public string $type = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')
                    ->where('user_id', Auth::id())
                    ->where('type', $this->type)
                    ->ignore($this->categoryId),
            ],
            'type' => ['required', Rule::enum(CategoryType::class)],
        ];
    }

    public function setCategory(Category $category): void
    {
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->type = $category->type->value;
    }

    public function type(): CategoryType
    {
        return CategoryType::from($this->type);
    }
}
