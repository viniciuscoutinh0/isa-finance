<?php

declare(strict_types=1);

use App\Livewire\Transfers\Index;
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
    Livewire::test(Index::class)->assertStatus(200);
});

it('lists only the current user transfers', function (): void {
    Transfer::factory()
        ->between($this->nubank, $this->itau)
        ->create(['notes' => 'Minha transferência']);

    Transfer::factory()->create(['notes' => 'Transferência alheia']);

    Livewire::test(Index::class)
        ->assertSee('Minha transferência')
        ->assertDontSee('Transferência alheia');
});

it('filters by an account on either side of the transfer', function (): void {
    $caixa = Account::factory()->ownedBy($this->user)->create(['name' => 'Caixa']);

    Transfer::factory()->between($this->nubank, $this->itau)->create(['notes' => 'Nubank para Itaú']);
    Transfer::factory()->between($this->itau, $this->nubank)->create(['notes' => 'Itaú para Nubank']);
    Transfer::factory()->between($this->itau, $caixa)->create(['notes' => 'Fora do filtro']);

    Livewire::test(Index::class)
        ->set('filterAccount', (string) $this->nubank->id)
        ->assertSee('Nubank para Itaú')
        ->assertSee('Itaú para Nubank')
        ->assertDontSee('Fora do filtro');
});

it('goes back to the first page when the account filter changes', function (): void {
    Transfer::factory()->count(30)->between($this->nubank, $this->itau)->create();

    Livewire::test(Index::class)
        ->set('paginators.page', 2)
        ->set('filterAccount', (string) $this->nubank->id)
        ->assertSet('paginators.page', 1);
});

it('creates a transfer through the modal', function (): void {
    Livewire::test(Index::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('form.fromAccountId', (string) $this->nubank->id)
        ->set('form.toAccountId', (string) $this->itau->id)
        ->set('form.date', '2026-03-10')
        ->set('form.amount', '250,00')
        ->set('form.notes', 'Reserva')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('transfers', [
        'user_id' => $this->user->id,
        'from_account_id' => $this->nubank->id,
        'to_account_id' => $this->itau->id,
        'amount' => 25000,
        'notes' => 'Reserva',
    ]);
});

it('defaults the date to today when the modal opens', function (): void {
    Livewire::test(Index::class)
        ->call('create')
        ->assertSet('form.date', today()->toDateString());
});

it('rejects a transfer to the same account', function (): void {
    Livewire::test(Index::class)
        ->call('create')
        ->set('form.fromAccountId', (string) $this->nubank->id)
        ->set('form.toAccountId', (string) $this->nubank->id)
        ->set('form.date', '2026-03-10')
        ->set('form.amount', '250,00')
        ->call('save')
        ->assertHasErrors('form.toAccountId');

    $this->assertDatabaseCount('transfers', 0);
});

it('rejects a zero or negative amount', function (string $amount): void {
    Livewire::test(Index::class)
        ->call('create')
        ->set('form.fromAccountId', (string) $this->nubank->id)
        ->set('form.toAccountId', (string) $this->itau->id)
        ->set('form.date', '2026-03-10')
        ->set('form.amount', $amount)
        ->call('save')
        ->assertHasErrors('form.amount');
})->with([
    '0,00',
    '-10,00',
]);

it('rejects an account that belongs to someone else', function (): void {
    $strangersAccount = Account::factory()->create();

    Livewire::test(Index::class)
        ->call('create')
        ->set('form.fromAccountId', (string) $strangersAccount->id)
        ->set('form.toAccountId', (string) $this->itau->id)
        ->set('form.date', '2026-03-10')
        ->set('form.amount', '250,00')
        ->call('save')
        ->assertHasErrors('form.fromAccountId');
});

it('edits a transfer and prefills the amount for the input', function (): void {
    $transfer = Transfer::factory()
        ->between($this->nubank, $this->itau)
        ->amountCents(123456)
        ->create(['notes' => 'Antiga']);

    Livewire::test(Index::class)
        ->call('edit', $transfer->id)
        ->assertSet('showModal', true)
        ->assertSet('form.amount', '1.234,56')
        ->assertSet('form.notes', 'Antiga')
        ->set('form.notes', 'Nova')
        ->call('save')
        ->assertHasNoErrors();

    expect($transfer->fresh()->notes)->toBe('Nova');
});

it('deletes a transfer', function (): void {
    $transfer = Transfer::factory()->between($this->nubank, $this->itau)->create();

    Livewire::test(Index::class)
        ->call('delete', $transfer->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('transfers', ['id' => $transfer->id]);
});

it('keeps a transfer owned by someone else', function (): void {
    $transfer = Transfer::factory()->create();

    Livewire::test(Index::class)
        ->call('delete', $transfer->id)
        ->assertForbidden();

    $this->assertDatabaseHas('transfers', ['id' => $transfer->id]);
});
