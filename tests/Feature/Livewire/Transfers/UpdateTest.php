<?php

declare(strict_types=1);

use App\Livewire\Transfers\Update;
use App\Models\Account;
use App\Models\Transfer;
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
    Livewire::test('transfers.update')->assertStatus(200);
});

it('prefills the form and formats the amount for the input', function (): void {
    $transfer = Transfer::factory()
        ->between($this->nubank, $this->itau)
        ->amountCents(123456)
        ->on('2026-03-10')
        ->create(['notes' => 'Antiga']);

    Livewire::test(Update::class)
        ->dispatch('transfer::edit', id: $transfer->id)
        ->assertSet('form.amount', '1.234,56')
        ->assertSet('form.notes', 'Antiga')
        ->assertSet('form.date', '2026-03-10')
        ->assertSet('form.from_account_id', $this->nubank->id)
        ->assertSet('form.to_account_id', $this->itau->id);
});

it('updates a transfer', function (): void {
    $transfer = Transfer::factory()
        ->between($this->nubank, $this->itau)
        ->create(['notes' => 'Antiga']);

    Livewire::test(Update::class)
        ->dispatch('transfer::edit', id: $transfer->id)
        ->set('form.notes', 'Nova')
        ->set('form.amount', '42,00')
        ->call('update')
        ->assertHasNoErrors()
        ->assertDispatched('transfer::changed');

    expect($transfer->fresh()->notes)->toBe('Nova')
        ->and($transfer->fresh()->amount)->toBe(4200);
});

it('rejects a transfer to the same account', function (): void {
    $transfer = Transfer::factory()->between($this->nubank, $this->itau)->create();

    Livewire::test(Update::class)
        ->dispatch('transfer::edit', id: $transfer->id)
        ->set('form.to_account_id', $this->nubank->id)
        ->call('update')
        ->assertHasErrors('form.to_account_id');
});

it('rejects a zero or negative amount', function (string $amount): void {
    $transfer = Transfer::factory()->between($this->nubank, $this->itau)->create();

    Livewire::test(Update::class)
        ->dispatch('transfer::edit', id: $transfer->id)
        ->set('form.amount', $amount)
        ->call('update')
        ->assertHasErrors('form.amount');
})->with([
    '0,00',
    '-10,00',
]);

it('does not load a transfer owned by someone else', function (): void {
    $transfer = Transfer::factory()->create();

    Livewire::test(Update::class)
        ->dispatch('transfer::edit', id: $transfer->id)
        ->assertSet('form.transfer', null)
        ->assertSet('form.amount', '');
});
