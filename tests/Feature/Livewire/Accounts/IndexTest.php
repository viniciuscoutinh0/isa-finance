<?php

declare(strict_types=1);

use App\Livewire\Accounts\Index;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    actingAs($this->user);
});

it('renders successfully', function (): void {
    Livewire::test(Index::class)->assertStatus(200);
});

it('lists only the current user active accounts', function (): void {
    Account::factory()->ownedBy($this->user)->create(['name' => 'Minha Conta']);
    Account::factory()->ownedBy($this->user)->archived()->create(['name' => 'Conta Arquivada']);
    Account::factory()->create(['name' => 'Conta Alheia']);

    Livewire::test(Index::class)
        ->assertSee('Minha Conta')
        ->assertDontSee('Conta Arquivada')
        ->assertDontSee('Conta Alheia')
        ->set('filters.archived', true)
        ->assertSee('Conta Arquivada');
});

it('filters the visible accounts by type when a tag is selected', function (): void {
    Account::factory()->ownedBy($this->user)->create(['name' => 'Banco Roxo', 'type' => 'checking']);
    Account::factory()->ownedBy($this->user)->create(['name' => 'Cofre Azul', 'type' => 'savings']);

    Livewire::test(Index::class)
        ->assertSee('Banco Roxo')
        ->assertSee('Cofre Azul')
        ->set('filters.type', 'savings')
        ->assertSee('Cofre Azul')
        ->assertDontSee('Banco Roxo')
        ->set('filters.type', null)
        ->assertSee('Banco Roxo')
        ->assertSee('Cofre Azul');
});

it('keeps the active total unchanged while a type filter is applied', function (): void {
    Account::factory()->ownedBy($this->user)->withInitialBalance(100_00)->create(['type' => 'checking']);
    Account::factory()->ownedBy($this->user)->withInitialBalance(50_00)->create(['type' => 'cash']);

    Livewire::test(Index::class)
        ->assertSee('R$ 150,00')
        ->set('filters.type', 'cash')
        ->assertSee('R$ 150,00');
});

it('refreshes the list when a sibling component writes an account', function (): void {
    $component = Livewire::test(Index::class)->assertDontSee('Recém criada');

    Account::factory()->ownedBy($this->user)->create(['name' => 'Recém criada']);

    $component->dispatch('account::changed')->assertSee('Recém criada');
});

it('archives and reactivates an account', function (): void {
    $account = Account::factory()->ownedBy($this->user)->create();

    Livewire::test(Index::class)
        ->call('archive', $account->id)
        ->assertHasNoErrors();

    expect($account->fresh()->archived_at)->not->toBeNull();

    Livewire::test(Index::class)
        ->call('unarchive', $account->id);

    expect($account->fresh()->archived_at)->toBeNull();
});

it('deletes an account', function (): void {
    $account = Account::factory()->ownedBy($this->user)->create();

    Livewire::test(Index::class)
        ->call('delete', $account->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
});

it('refuses to delete an account that has transactions', function (): void {
    $account = Account::factory()->ownedBy($this->user)->create();
    $category = Category::factory()->ownedBy($this->user)->expense()->create();

    Transaction::factory()->forAccount($account)->forCategory($category)->create();

    Livewire::test(Index::class)
        ->call('delete', $account->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('accounts', ['id' => $account->id]);
});

it('keeps an account owned by someone else', function (): void {
    $account = Account::factory()->create();

    Livewire::test(Index::class)->call('delete', $account->id);

    $this->assertDatabaseHas('accounts', ['id' => $account->id]);
});

it('does not archive an account owned by someone else', function (): void {
    $account = Account::factory()->create();

    Livewire::test(Index::class)->call('archive', $account->id);

    expect($account->fresh()->archived_at)->toBeNull();
});
