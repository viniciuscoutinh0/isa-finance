<?php

declare(strict_types=1);

use App\Livewire\Accounts\Update;
use App\Models\Account;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    actingAs($this->user);
});

it('renders successfully', function (): void {
    Livewire::test('accounts.update')->assertStatus(200);
});

it('prefills the form and formats the balance for the input', function (): void {
    $account = Account::factory()
        ->ownedBy($this->user)
        ->withInitialBalance(123456)
        ->create(['name' => 'Itaú', 'type' => 'checking']);

    Livewire::test(Update::class)
        ->dispatch('account::edit', id: $account->id)
        ->assertSet('form.name', 'Itaú')
        ->assertSet('form.type', 'checking')
        ->assertSet('form.initial_balance', '1.234,56');
});

it('updates an account', function (): void {
    $account = Account::factory()->ownedBy($this->user)->create(['name' => 'Itaú']);

    Livewire::test(Update::class)
        ->dispatch('account::edit', id: $account->id)
        ->set('form.name', 'Itaú Corrente')
        ->call('update')
        ->assertHasNoErrors()
        ->assertDispatched('account::changed');

    expect($account->fresh()->name)->toBe('Itaú Corrente');
});

it('requires a name', function (): void {
    $account = Account::factory()->ownedBy($this->user)->create();

    Livewire::test(Update::class)
        ->dispatch('account::edit', id: $account->id)
        ->set('form.name', '')
        ->call('update')
        ->assertHasErrors(['form.name' => 'required']);
});

it('does not load an account owned by someone else', function (): void {
    $account = Account::factory()->create(['name' => 'Alheia']);

    Livewire::test(Update::class)
        ->dispatch('account::edit', id: $account->id)
        ->assertSet('form.account', null)
        ->assertSet('form.name', '');
});
