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
        ->set('filters.account_id', $this->nubank->id)
        ->assertSee('Nubank para Itaú')
        ->assertSee('Itaú para Nubank')
        ->assertDontSee('Fora do filtro');
});

it('goes back to the first page when a filter changes', function (): void {
    Transfer::factory()->count(30)->between($this->nubank, $this->itau)->create();

    Livewire::test(Index::class)
        ->set('paginators.page', 2)
        ->set('filters.account_id', $this->nubank->id)
        ->assertSet('paginators.page', 1);
});

it('refreshes the list when a sibling component writes a transfer', function (): void {
    $component = Livewire::test(Index::class)->assertDontSee('Recém criada');

    Transfer::factory()
        ->between($this->nubank, $this->itau)
        ->create(['notes' => 'Recém criada']);

    $component->dispatch('transfer::changed')->assertSee('Recém criada');
});

it('deletes a transfer', function (): void {
    $transfer = Transfer::factory()
        ->between($this->nubank, $this->itau)
        ->create(['notes' => 'Some daqui']);

    Livewire::test(Index::class)
        ->assertSee('Some daqui')
        ->call('delete', $transfer->id)
        ->assertHasNoErrors()
        ->assertDontSee('Some daqui');

    $this->assertDatabaseMissing('transfers', ['id' => $transfer->id]);
});

it('keeps a transfer owned by someone else', function (): void {
    $transfer = Transfer::factory()->create();

    Livewire::test(Index::class)->call('delete', $transfer->id);

    $this->assertDatabaseHas('transfers', ['id' => $transfer->id]);
});
