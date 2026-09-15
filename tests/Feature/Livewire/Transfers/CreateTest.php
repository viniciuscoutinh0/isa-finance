<?php

declare(strict_types=1);

use App\Livewire\Transfers\Create;
use App\Models\Account;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    actingAs($this->user);

    $this->nubank = Account::factory()
        ->ownedBy($this->user)
        ->create(['name' => 'Nubank']);

    $this->itau = Account::factory()
        ->ownedBy($this->user)
        ->create(['name' => 'Itaú']);
});

it('renders successfully', function (): void {
    Livewire::test('transfers.create')->assertStatus(200);
});

it('defaults the date to today', function (): void {
    Livewire::test(Create::class)
        ->assertSet('form.date', today()->toDateString());
});

it('requires the transfer fields', function (): void {
    Livewire::test(Create::class)
        ->set('form.from_account_id', '')
        ->set('form.to_account_id', '')
        ->set('form.date', '')
        ->set('form.amount', '')
        ->call('create')
        ->assertHasErrors([
            'form.from_account_id' => 'required',
            'form.to_account_id' => 'required',
            'form.date' => 'required',
            'form.amount' => 'required',
        ]);
});

it('creates a transfer', function (): void {
    Livewire::test(Create::class)
        ->set('form.from_account_id', $this->nubank->id)
        ->set('form.to_account_id', $this->itau->id)
        ->set('form.date', '2026-03-10')
        ->set('form.amount', '250,00')
        ->set('form.notes', 'Reserva')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('transfer::changed');

    $this->assertDatabaseHas('transfers', [
        'user_id' => $this->user->id,
        'from_account_id' => $this->nubank->id,
        'to_account_id' => $this->itau->id,
        'amount' => 25000,
        'notes' => 'Reserva',
    ]);
});

it('rejects a transfer to the same account', function (): void {
    Livewire::test(Create::class)
        ->set('form.from_account_id', $this->nubank->id)
        ->set('form.to_account_id', $this->nubank->id)
        ->set('form.date', '2026-03-10')
        ->set('form.amount', '250,00')
        ->call('create')
        ->assertHasErrors('form.to_account_id');

    $this->assertDatabaseCount('transfers', 0);
});

it('rejects a zero or negative amount', function (string $amount): void {
    Livewire::test(Create::class)
        ->set('form.from_account_id', $this->nubank->id)
        ->set('form.to_account_id', $this->itau->id)
        ->set('form.date', '2026-03-10')
        ->set('form.amount', $amount)
        ->call('create')
        ->assertHasErrors('form.amount');
})->with([
    '0,00',
    '-10,00',
]);

it('rejects an account that belongs to someone else', function (): void {
    $strangersAccount = Account::factory()->create();

    Livewire::test(Create::class)
        ->set('form.from_account_id', $strangersAccount->id)
        ->set('form.to_account_id', $this->itau->id)
        ->set('form.date', '2026-03-10')
        ->set('form.amount', '250,00')
        ->call('create')
        ->assertHasErrors('form.from_account_id');

    $this->assertDatabaseCount('transfers', 0);
});

it('accepts an archived account on either side', function (): void {
    $archived = Account::factory()->ownedBy($this->user)->archived()->create(['name' => 'Cartão antigo']);

    Livewire::test(Create::class)
        ->set('form.from_account_id', $this->nubank->id)
        ->set('form.to_account_id', $archived->id)
        ->set('form.date', '2026-03-10')
        ->set('form.amount', '300,00')
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('transfers', [
        'from_account_id' => $this->nubank->id,
        'to_account_id' => $archived->id,
        'amount' => 30000,
    ]);
});
