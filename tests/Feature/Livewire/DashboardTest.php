<?php

declare(strict_types=1);

use App\Livewire\Dashboard;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

it('renders for an authenticated user', function (): void {
    $this->actingAs(User::factory()->create(['name' => 'Ana Prado']));

    Livewire::test(Dashboard::class)
        ->assertOk()
        ->assertSee('Ana Prado');
});

it('shows each account balance and the grand total', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $account = Account::factory()->ownedBy($user)->withInitialBalance(100_00)->create(['name' => 'Nubank']);
    $income = Category::factory()->ownedBy($user)->income()->create();
    Transaction::factory()->forAccount($account)->forCategory($income)->amountCents(400_00)->create();

    Account::factory()->ownedBy($user)->withInitialBalance(50_00)->create(['name' => 'Carteira']);

    Livewire::test(Dashboard::class)
        ->assertSee('Nubank')
        ->assertSee('R$ 500,00')  // Nubank: 100 + 400
        ->assertSee('R$ 550,00'); // total: 500 + 50
});

it('excludes archived accounts from the total', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    Account::factory()->ownedBy($user)->withInitialBalance(100_00)->create(['name' => 'Ativa']);
    Account::factory()->ownedBy($user)->archived()->withInitialBalance(999_00)->create(['name' => 'Arquivada']);

    Livewire::test(Dashboard::class)
        ->assertSee('R$ 100,00')
        ->assertDontSee('Arquivada');
});

it('shows the current month income, expense and net in the metrics grid', function (): void {
    $this->travelTo('2026-09-15 12:00:00');

    $user = User::factory()->create();
    $this->actingAs($user);

    $account = Account::factory()->ownedBy($user)->create();
    $income = Category::factory()->ownedBy($user)->income()->create();
    $expense = Category::factory()->ownedBy($user)->expense()->create();

    Transaction::factory()->forAccount($account)->forCategory($income)->amountCents(500_00)->on('2026-09-03')->create();
    Transaction::factory()->forAccount($account)->forCategory($expense)->amountCents(200_00)->on('2026-09-08')->create();

    Livewire::test(Dashboard::class)
        ->assertSee('Entradas no mês')
        ->assertSee('R$ 500,00')
        ->assertSee('Saídas no mês')
        ->assertSee('R$ 200,00')
        ->assertSee('Setembro de 2026')
        ->assertSee('R$ 300,00'); // net result
});

it('lists the most recent transactions', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $account = Account::factory()->ownedBy($user)->create();
    $category = Category::factory()->ownedBy($user)->expense()->create();
    Transaction::factory()->forAccount($account)->forCategory($category)->on('2026-05-01')->create(['description' => 'Café da esquina']);

    Livewire::test(Dashboard::class)
        ->assertSee('Café da esquina');
});
