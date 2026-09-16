<?php

declare(strict_types=1);

use App\Livewire\Transactions\Index;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    actingAs($this->user);

    $this->account = Account::factory()
        ->ownedBy($this->user)
        ->create(['name' => 'Nubank']);

    $this->expense = Category::factory()
        ->ownedBy($this->user)
        ->expense()
        ->create(['name' => 'Mercado']);

    $this->income = Category::factory()
        ->ownedBy($this->user)
        ->income()
        ->create(['name' => 'Salário']);
});

it('renders successfully', function (): void {
    Livewire::test(Index::class)->assertStatus(200);
});

it('lists only the current user transactions', function (): void {
    Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->create(['description' => 'Minha compra']);

    Transaction::factory()->create(['description' => 'Compra alheia']);

    Livewire::test(Index::class)
        ->assertSee('Minha compra')
        ->assertDontSee('Compra alheia');
});

it('filters the list by type', function (): void {
    Transaction::factory()->forAccount($this->account)->forCategory($this->income)
        ->create(['description' => 'Pagamento salário']);
    Transaction::factory()->forAccount($this->account)->forCategory($this->expense)
        ->create(['description' => 'Compra no mercado']);

    Livewire::test(Index::class)
        ->set('filters.types', ['income'])
        ->assertSee('Pagamento salário')
        ->assertDontSee('Compra no mercado');
});

it('filters the list by account', function (): void {
    $itau = Account::factory()->ownedBy($this->user)->create(['name' => 'Itaú']);

    Transaction::factory()->forAccount($this->account)->forCategory($this->expense)
        ->create(['description' => 'Gasto no Nubank']);
    Transaction::factory()->forAccount($itau)->forCategory($this->expense)
        ->create(['description' => 'Gasto no Itaú']);

    Livewire::test(Index::class)
        ->set('filters.accounts', [$this->account->id])
        ->assertSee('Gasto no Nubank')
        ->assertDontSee('Gasto no Itaú');
});

it('filters the list by category', function (): void {
    Transaction::factory()->forAccount($this->account)->forCategory($this->expense)
        ->create(['description' => 'Feira da semana']);
    Transaction::factory()->forAccount($this->account)->forCategory($this->income)
        ->create(['description' => 'Salário do mês']);

    Livewire::test(Index::class)
        ->set('filters.categories', [$this->expense->id])
        ->assertSee('Feira da semana')
        ->assertDontSee('Salário do mês');
});

it('filters the list by description search', function (): void {
    Transaction::factory()->forAccount($this->account)->forCategory($this->expense)
        ->create(['description' => 'Padaria da esquina']);
    Transaction::factory()->forAccount($this->account)->forCategory($this->expense)
        ->create(['description' => 'Posto de gasolina']);

    Livewire::test(Index::class)
        ->set('filters.search', 'padaria')
        ->assertSee('Padaria da esquina')
        ->assertDontSee('Posto de gasolina');
});

it('goes back to the first page when a filter changes', function (): void {
    Transaction::factory()->count(30)
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->create();

    Livewire::test(Index::class)
        ->set('paginators.page', 2)
        ->set('filters.search', 'qualquer coisa')
        ->assertSet('paginators.page', 1);
});

it('refreshes the list when a sibling component writes a transaction', function (): void {
    $component = Livewire::test(Index::class)->assertDontSee('Recém criado');

    Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->create(['description' => 'Recém criado']);

    $component->dispatch('transaction::changed')->assertSee('Recém criado');
});

it('deletes a transaction', function (): void {
    $transaction = Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->create(['description' => 'Some daqui']);

    Livewire::test(Index::class)
        ->assertSee('Some daqui')
        ->call('delete', $transaction->id)
        ->assertHasNoErrors()
        ->assertDontSee('Some daqui');

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});

it('keeps a transaction owned by someone else', function (): void {
    $transaction = Transaction::factory()->create();

    Livewire::test(Index::class)->call('delete', $transaction->id);

    $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
});
