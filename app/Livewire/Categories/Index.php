<?php

declare(strict_types=1);

namespace App\Livewire\Categories;

use App\Actions\Categories\CreateCategory;
use App\Actions\Categories\DeleteCategory;
use App\Actions\Categories\UpdateCategory;
use App\Enums\CategoryType;
use App\Exceptions\Categories\CategoryInUse;
use App\Livewire\Forms\CategoryForm;
use App\Models\Category;
use App\Queries\Categories\CategoriesQuery;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::dashboard')]
#[Title('Categorias')]
final class Index extends Component
{
    public CategoryForm $form;

    public bool $showModal = false;

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return app(CategoriesQuery::class)->handle(auth()->user());
    }

    public function create(): void
    {
        $this->authorize('create', Category::class);

        $this->form->reset();
        $this->form->type = CategoryType::Expense->value;
        $this->showModal = true;
    }

    public function edit(Category $category): void
    {
        $this->authorize('update', $category);

        $this->form->setCategory($category);
        $this->showModal = true;
    }

    public function save(CreateCategory $createCategory, UpdateCategory $updateCategory): void
    {
        $this->form->validate();

        if ($this->form->categoryId === null) {
            $this->authorize('create', Category::class);
            $createCategory->handle(auth()->user(), $this->form->name, $this->form->type());
        } else {
            $category = auth()->user()->categories()->findOrFail($this->form->categoryId);
            $this->authorize('update', $category);
            $updateCategory->handle($category, $this->form->name, $this->form->type());
        }

        unset($this->categories);
        $this->showModal = false;
        $this->form->reset();

        Flux::toast('Categoria salva.', variant: 'success');
    }

    public function delete(Category $category, DeleteCategory $deleteCategory): void
    {
        $this->authorize('delete', $category);

        try {
            $deleteCategory->handle($category);
        } catch (CategoryInUse) {
            Flux::toast('Esta categoria tem lançamentos e não pode ser excluída.', variant: 'danger');

            return;
        }

        unset($this->categories);

        Flux::toast('Categoria excluída.', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.categories.index');
    }
}
