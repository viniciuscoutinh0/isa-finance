<?php

declare(strict_types=1);

use App\Livewire\Accounts\Create;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    actingAs($this->user);
});

it('renders successfully', function (): void {
    Livewire::test('accounts.create')->assertStatus(200);
});

it('requires a name', function (): void {
    Livewire::test(Create::class)
        ->set('form.name', '')
        ->set('form.type', 'checking')
        ->call('create')
        ->assertHasErrors(['form.name' => 'required']);
});

it('rejects an invalid initial balance', function (): void {
    Livewire::test(Create::class)
        ->set('form.name', 'Nubank')
        ->set('form.type', 'checking')
        ->set('form.initial_balance', 'abc')
        ->call('create')
        ->assertHasErrors('form.initial_balance');
});

it('creates an account', function (): void {
    Livewire::test(Create::class)
        ->set('form.name', 'Nubank')
        ->set('form.type', 'checking')
        ->set('form.initial_balance', '1.500,00')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('account::changed');

    $this->assertDatabaseHas('accounts', [
        'user_id' => $this->user->id,
        'name' => 'Nubank',
        'type' => 'checking',
        'initial_balance' => 150000,
    ]);
});

it('accepts a negative initial balance for a credit card debt', function (): void {
    Livewire::test(Create::class)
        ->set('form.name', 'Cartão')
        ->set('form.type', 'credit_card')
        ->set('form.initial_balance', '-1.200,00')
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('accounts', [
        'name' => 'Cartão',
        'initial_balance' => -120000,
    ]);
});
