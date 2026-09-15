<?php

use App\Livewire\Transactions\Create;
use App\Models\Account;
use App\Models\Category;
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
    Livewire::test('transactions.create')->assertStatus(200);
});

it('should requireds transactions fields', function (): void {
    Livewire::test(Create::class)
        ->set('form.accountId', '')
        ->set('form.categoryId', '')
        ->set('form.date', '')
        ->set('form.description', '')
        ->set('form.amount', '')
        ->call('create')
        ->assertHasErrors([
            'form.accountId' => 'required',
            'form.categoryId' => 'required',
            'form.date' => 'required',
            'form.description' => 'required',
            'form.amount' => 'required',
        ]);
});

it('rejects a zero or negative amount', function (string $amount): void {
    Livewire::test(Create::class)
        ->set('form.accountId', (string) $this->account->id)
        ->set('form.categoryId', (string) $this->expense->id)
        ->set('form.date', '2026-03-10')
        ->set('form.description', 'Feira')
        ->set('form.amount', $amount)
        ->call('create')
        ->assertHasErrors('form.amount');
})->with([
    '0,00',
    '-10,00',
]);

it('rejects an account that belongs to someone else', function (): void {
    $foreignAccount = Account::factory()->create();

    Livewire::test(Create::class)
        ->set('form.accountId', $foreignAccount->id)
        ->set('form.categoryId', $this->expense->id)
        ->set('form.date', '2026-03-10')
        ->set('form.description', 'Feira')
        ->set('form.amount', '10,00')
        ->call('create')
        ->assertHasErrors('form.accountId');
});

it('creates a new transaction', function (): void {
    Livewire::test(Create::class)
        ->set('form.accountId', (string) $this->account->id)
        ->set('form.categoryId', (string) $this->expense->id)
        ->set('form.date', '2026-03-10')
        ->set('form.description', 'Feira')
        ->set('form.amount', '89,90')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('transaction::created');

    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->expense->id,
        'description' => 'Feira',
        'amount' => 8990,
    ]);
});
