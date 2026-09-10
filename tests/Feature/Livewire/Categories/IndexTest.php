<?php

declare(strict_types=1);

use App\Livewire\Categories\Index;
use App\Models\Category;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('lists only the current user categories', function (): void {
    Category::factory()->ownedBy($this->user)->create(['name' => 'Minha Categoria']);
    Category::factory()->create(['name' => 'Categoria Alheia']);

    Livewire::test(Index::class)
        ->assertSee('Minha Categoria')
        ->assertDontSee('Categoria Alheia');
});

it('creates a category through the modal', function (): void {
    Livewire::test(Index::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('form.name', 'Pets')
        ->set('form.type', 'expense')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('categories', [
        'user_id' => $this->user->id,
        'name' => 'Pets',
        'type' => 'expense',
    ]);
});

it('rejects a duplicate name within the same type', function (): void {
    Category::factory()->ownedBy($this->user)->expense()->create(['name' => 'Mercado']);

    Livewire::test(Index::class)
        ->call('create')
        ->set('form.name', 'Mercado')
        ->set('form.type', 'expense')
        ->call('save')
        ->assertHasErrors(['form.name' => 'unique']);
});

it('allows the same name across different types', function (): void {
    Category::factory()->ownedBy($this->user)->expense()->create(['name' => 'Outros']);

    Livewire::test(Index::class)
        ->call('create')
        ->set('form.name', 'Outros')
        ->set('form.type', 'income')
        ->call('save')
        ->assertHasNoErrors();
});

it('edits an existing category', function (): void {
    $category = Category::factory()->ownedBy($this->user)->expense()->create(['name' => 'Mercado']);

    Livewire::test(Index::class)
        ->call('edit', $category)
        ->assertSet('form.name', 'Mercado')
        ->set('form.name', 'Supermercado')
        ->call('save')
        ->assertHasNoErrors();

    expect($category->fresh()->name)->toBe('Supermercado');
});

it('deletes a category', function (): void {
    $category = Category::factory()->ownedBy($this->user)->create();

    Livewire::test(Index::class)
        ->call('delete', $category)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

it('cannot edit a category owned by someone else', function (): void {
    $category = Category::factory()->create();

    Livewire::test(Index::class)
        ->call('edit', $category)
        ->assertForbidden();
});
