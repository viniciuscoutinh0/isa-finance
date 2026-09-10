<?php

declare(strict_types=1);

use App\Livewire\Transfers\Index;
use App\Models\Account;
use App\Models\Transfer;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->from = Account::factory()->ownedBy($this->user)->create(['name' => 'Nubank']);
    $this->to = Account::factory()->ownedBy($this->user)->create(['name' => 'Carteira']);
});

it('lists only the current user transfers', function (): void {
    Transfer::factory()->between($this->from, $this->to)->create(['notes' => 'Meu saque']);
    Transfer::factory()->create(['notes' => 'Saque alheio']);

    Livewire::test(Index::class)
        ->assertSee('Meu saque')
        ->assertDontSee('Saque alheio');
});

it('creates a transfer through the modal', function (): void {
    Livewire::test(Index::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('form.fromAccountId', (string) $this->from->id)
        ->set('form.toAccountId', (string) $this->to->id)
        ->set('form.date', '2026-03-10')
        ->set('form.amount', '200,00')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('transfers', [
        'user_id' => $this->user->id,
        'from_account_id' => $this->from->id,
        'to_account_id' => $this->to->id,
        'amount' => 20000,
    ]);
});

it('rejects a transfer to the same account', function (): void {
    Livewire::test(Index::class)
        ->call('create')
        ->set('form.fromAccountId', (string) $this->from->id)
        ->set('form.toAccountId', (string) $this->from->id)
        ->set('form.date', '2026-03-10')
        ->set('form.amount', '200,00')
        ->call('save')
        ->assertHasErrors('form.toAccountId');

    $this->assertDatabaseCount('transfers', 0);
});

it('rejects an account owned by someone else', function (): void {
    $foreign = Account::factory()->create();

    Livewire::test(Index::class)
        ->call('create')
        ->set('form.fromAccountId', (string) $this->from->id)
        ->set('form.toAccountId', (string) $foreign->id)
        ->set('form.date', '2026-03-10')
        ->set('form.amount', '50,00')
        ->call('save')
        ->assertHasErrors('form.toAccountId');
});

it('edits a transfer', function (): void {
    $transfer = Transfer::factory()->between($this->from, $this->to)->amountCents(10000)->create();

    Livewire::test(Index::class)
        ->call('edit', $transfer)
        ->assertSet('form.amount', '100,00')
        ->set('form.amount', '123,45')
        ->call('save')
        ->assertHasNoErrors();

    expect($transfer->fresh()->amount)->toBe(12345);
});

it('deletes a transfer', function (): void {
    $transfer = Transfer::factory()->between($this->from, $this->to)->create();

    Livewire::test(Index::class)
        ->call('delete', $transfer)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('transfers', ['id' => $transfer->id]);
});

it('cannot edit a transfer owned by someone else', function (): void {
    $transfer = Transfer::factory()->create();

    Livewire::test(Index::class)
        ->call('edit', $transfer)
        ->assertForbidden();
});
