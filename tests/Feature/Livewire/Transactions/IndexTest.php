<?php

declare(strict_types=1);

use App\Livewire\Transactions\Index;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->account = Account::factory()->ownedBy($this->user)->create(['name' => 'Nubank']);
    $this->expense = Category::factory()->ownedBy($this->user)->expense()->create(['name' => 'Mercado']);
    $this->income = Category::factory()->ownedBy($this->user)->income()->create(['name' => 'Salário']);
});

it('lists only the current user transactions', function (): void {
    Transaction::factory()->forAccount($this->account)->forCategory($this->expense)->create(['description' => 'Minha compra']);
    Transaction::factory()->create(['description' => 'Compra alheia']);

    Livewire::test(Index::class)
        ->assertSee('Minha compra')
        ->assertDontSee('Compra alheia');
});

it('creates a transaction through the modal', function (): void {
    Livewire::test(Index::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('form.accountId', (string) $this->account->id)
        ->set('form.categoryId', (string) $this->expense->id)
        ->set('form.date', '2026-03-10')
        ->set('form.description', 'Feira')
        ->set('form.amount', '89,90')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->expense->id,
        'description' => 'Feira',
        'amount' => 8990,
    ]);
});

it('rejects a zero or negative amount', function (string $amount): void {
    Livewire::test(Index::class)
        ->call('create')
        ->set('form.accountId', (string) $this->account->id)
        ->set('form.categoryId', (string) $this->expense->id)
        ->set('form.date', '2026-03-10')
        ->set('form.description', 'Feira')
        ->set('form.amount', $amount)
        ->call('save')
        ->assertHasErrors('form.amount');
})->with(['0,00', '-10,00']);

it('rejects an account that belongs to someone else', function (): void {
    $foreignAccount = Account::factory()->create();

    Livewire::test(Index::class)
        ->call('create')
        ->set('form.accountId', (string) $foreignAccount->id)
        ->set('form.categoryId', (string) $this->expense->id)
        ->set('form.date', '2026-03-10')
        ->set('form.description', 'Feira')
        ->set('form.amount', '10,00')
        ->call('save')
        ->assertHasErrors('form.accountId');
});

it('edits a transaction and prefills the amount for the input', function (): void {
    $transaction = Transaction::factory()->forAccount($this->account)->forCategory($this->expense)
        ->amountCents(123456)->create(['description' => 'Antigo']);

    Livewire::test(Index::class)
        ->call('edit', $transaction)
        ->assertSet('form.amount', '1.234,56')
        ->assertSet('form.description', 'Antigo')
        ->set('form.description', 'Novo')
        ->call('save')
        ->assertHasNoErrors();

    expect($transaction->fresh()->description)->toBe('Novo');
});

it('deletes a transaction', function (): void {
    $transaction = Transaction::factory()->forAccount($this->account)->forCategory($this->expense)->create();

    Livewire::test(Index::class)
        ->call('delete', $transaction)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});

it('filters the list by type', function (): void {
    Transaction::factory()->forAccount($this->account)->forCategory($this->income)->create(['description' => 'Pagamento salário']);
    Transaction::factory()->forAccount($this->account)->forCategory($this->expense)->create(['description' => 'Compra no mercado']);

    Livewire::test(Index::class)
        ->set('filterType', 'income')
        ->assertSee('Pagamento salário')
        ->assertDontSee('Compra no mercado');
});

it('cannot edit a transaction owned by someone else', function (): void {
    $transaction = Transaction::factory()->create();

    Livewire::test(Index::class)
        ->call('edit', $transaction)
        ->assertForbidden();
});
