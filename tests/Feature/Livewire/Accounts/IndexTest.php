<?php

declare(strict_types=1);

use App\Livewire\Accounts\Index;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('lists only the current user active accounts', function (): void {
    Account::factory()->ownedBy($this->user)->create(['name' => 'Minha Conta']);
    Account::factory()->ownedBy($this->user)->archived()->create(['name' => 'Conta Arquivada']);
    Account::factory()->create(['name' => 'Conta Alheia']);

    Livewire::test(Index::class)
        ->assertSee('Minha Conta')
        ->assertDontSee('Conta Arquivada')
        ->assertDontSee('Conta Alheia')
        ->set('showArchived', true)
        ->assertSee('Conta Arquivada');
});

it('filters the visible accounts by type when a tag is selected', function (): void {
    Account::factory()->ownedBy($this->user)->create(['name' => 'Banco Roxo', 'type' => 'checking']);
    Account::factory()->ownedBy($this->user)->create(['name' => 'Cofre Azul', 'type' => 'savings']);

    Livewire::test(Index::class)
        ->assertSee('Banco Roxo')
        ->assertSee('Cofre Azul')
        ->set('filterType', 'savings')
        ->assertSee('Cofre Azul')
        ->assertDontSee('Banco Roxo')
        ->set('filterType', null)
        ->assertSee('Banco Roxo')
        ->assertSee('Cofre Azul');
});

it('keeps the active total unchanged while a type filter is applied', function (): void {
    Account::factory()->ownedBy($this->user)->withInitialBalance(100_00)->create(['type' => 'checking']);
    Account::factory()->ownedBy($this->user)->withInitialBalance(50_00)->create(['type' => 'cash']);

    Livewire::test(Index::class)
        ->assertSee('R$ 150,00')
        ->set('filterType', 'cash')
        ->assertSee('R$ 150,00');
});

it('creates an account through the modal', function (): void {
    Livewire::test(Index::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('form.name', 'Nubank')
        ->set('form.type', 'checking')
        ->set('form.initialBalance', '1.500,00')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('accounts', [
        'user_id' => $this->user->id,
        'name' => 'Nubank',
        'type' => 'checking',
        'initial_balance' => 150000,
    ]);
});

it('rejects an invalid initial balance', function (): void {
    Livewire::test(Index::class)
        ->call('create')
        ->set('form.name', 'Nubank')
        ->set('form.type', 'checking')
        ->set('form.initialBalance', 'abc')
        ->call('save')
        ->assertHasErrors('form.initialBalance');
});

it('edits an account and prefills the balance for the input', function (): void {
    $account = Account::factory()->ownedBy($this->user)->withInitialBalance(123456)->create(['name' => 'Itaú']);

    Livewire::test(Index::class)
        ->call('edit', $account)
        ->assertSet('form.name', 'Itaú')
        ->assertSet('form.initialBalance', '1.234,56')
        ->set('form.name', 'Itaú Corrente')
        ->call('save')
        ->assertHasNoErrors();

    expect($account->fresh()->name)->toBe('Itaú Corrente');
});

it('archives and reactivates an account', function (): void {
    $account = Account::factory()->ownedBy($this->user)->create();

    Livewire::test(Index::class)
        ->call('archive', $account)
        ->assertHasNoErrors();

    expect($account->fresh()->archived_at)->not->toBeNull();

    Livewire::test(Index::class)
        ->call('unarchive', $account);

    expect($account->fresh()->archived_at)->toBeNull();
});

it('deletes an account', function (): void {
    $account = Account::factory()->ownedBy($this->user)->create();

    Livewire::test(Index::class)
        ->call('delete', $account)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
});

it('requires a name', function (): void {
    Livewire::test(Index::class)
        ->call('create')
        ->set('form.name', '')
        ->set('form.type', 'checking')
        ->call('save')
        ->assertHasErrors(['form.name' => 'required']);
});

it('refuses to delete an account that has transactions', function (): void {
    $account = Account::factory()->ownedBy($this->user)->create();
    $category = Category::factory()->ownedBy($this->user)->expense()->create();

    Transaction::factory()->forAccount($account)->forCategory($category)->create();

    Livewire::test(Index::class)
        ->call('delete', $account)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('accounts', ['id' => $account->id]);
});

it('keeps an account owned by someone else', function (): void {
    $account = Account::factory()->create();

    Livewire::test(Index::class)
        ->call('delete', $account)
        ->assertForbidden();

    $this->assertDatabaseHas('accounts', ['id' => $account->id]);
});

it('cannot edit an account owned by someone else', function (): void {
    $account = Account::factory()->create();

    Livewire::test(Index::class)
        ->call('edit', $account)
        ->assertForbidden();
});
