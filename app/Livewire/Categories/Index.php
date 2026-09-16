<?php

declare(strict_types=1);

namespace App\Livewire\Categories;

use App\Actions\Categories\CreateCategory;
use App\Actions\Categories\DeleteCategory;
use App\Actions\Categories\UpdateCategory;
use App\Data\Categories\CategoryData;
use App\Enums\CategoryType;
use App\Exceptions\Categories\CategoryInUse;
use App\Livewire\Concerns\WithCategoryOptions;
use App\Livewire\Forms\CategoryForm;
use App\Models\Category;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::dashboard')]
#[Title('Categorias')]
final class Index extends Component
{
    use WithCategoryOptions;

    public CategoryForm $form;

    public bool $showModal = false;

    public function create(): void
    {
        if (! $this->allows('create', Category::class)) {
            return;
        }

        $this->form->reset();
        $this->form->type = CategoryType::Expense->value;

        $this->showModal = true;
    }

    public function edit(Category $category): void
    {
        if (! $this->allows('update', $category)) {
            return;
        }

        $this->form->setCategory($category);

        $this->showModal = true;
    }

    public function save(CreateCategory $createCategory, UpdateCategory $updateCategory): void
    {
        $validated = $this->form->validate();
        $data = CategoryData::fromArray($validated);
        $category = $this->form->category;

        if ($category === null) {
            if (! $this->allows('create', Category::class)) {
                return;
            }

            $createCategory->handle(Auth::user(), $data);
        } else {
            if (! $this->allows('update', $category)) {
                return;
            }

            $updateCategory->handle($category, $data);
        }

        unset($this->categories);

        $this->showModal = false;
        $this->form->reset();

        Flux::toast('Categoria salva.', variant: 'success');
    }

    public function delete(Category $category, DeleteCategory $action): void
    {
        if (! $this->allows('delete', $category)) {
            return;
        }

        try {
            $action->handle($category);
        } catch (CategoryInUse) {
            Flux::toast('Esta categoria tem lançamentos e não pode ser excluída.', variant: 'danger');

            return;
        }

        unset($this->categories);

        Flux::toast('Categoria excluída.', variant: 'success');
    }

    /**
     * @param  Category|class-string<Category>  $target
     */
    private function allows(string $ability, Category|string $target): bool
    {
        try {
            $this->authorize($ability, $target);
        } catch (AuthorizationException) {
            Flux::toast('Você não tem permissão para isso.', variant: 'danger');

            return false;
        }

        return true;
    }

    public function render(): View
    {
        return view('livewire.categories.index');
    }
}
