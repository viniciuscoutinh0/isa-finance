<?php

declare(strict_types=1);

use App\Livewire\Transactions\Update;
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
});

it('renders successfully', function (): void {
    Livewire::test('transactions.update')->assertStatus(200);
});

it('prefills the form and formats the amount for the input', function (): void {
    $transaction = Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->amountCents(123456)
        ->on('2026-03-10')
        ->create(['description' => 'Antigo']);

    Livewire::test(Update::class)
        ->call('onShow', $transaction->id)
        ->assertSet('form.amount', '1.234,56')
        ->assertSet('form.description', 'Antigo')
        ->assertSet('form.date', '2026-03-10')
        ->assertSet('form.account_id', $this->account->id)
        ->assertSet('form.category_id', $this->expense->id);
});

it('updates a transaction', function (): void {
    $transaction = Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->create(['description' => 'Antigo']);

    Livewire::test(Update::class)
        ->call('onShow', $transaction->id)
        ->set('form.description', 'Novo')
        ->set('form.amount', '42,00')
        ->call('update')
        ->assertHasNoErrors()
        ->assertDispatched('transaction::updated');

    expect($transaction->fresh()->description)->toBe('Novo')
        ->and($transaction->fresh()->amount)->toBe(4200);
});

it('rejects a zero or negative amount', function (string $amount): void {
    $transaction = Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->create();

    Livewire::test(Update::class)
        ->call('onShow', $transaction->id)
        ->set('form.amount', $amount)
        ->call('update')
        ->assertHasErrors('form.amount');
})->with([
    '0,00',
    '-10,00',
]);

it('rejects an account that belongs to someone else', function (): void {
    $transaction = Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->create();

    $foreignAccount = Account::factory()->create();

    Livewire::test(Update::class)
        ->call('onShow', $transaction->id)
        ->set('form.account_id', $foreignAccount->id)
        ->call('update')
        ->assertHasErrors('form.account_id');
});

it('does not load a transaction owned by someone else', function (): void {
    $transaction = Transaction::factory()->create(['description' => 'Alheio']);

    Livewire::test(Update::class)
        ->call('onShow', $transaction->id)
        ->assertSet('form.transaction', null)
        ->assertSet('form.description', '');
});
