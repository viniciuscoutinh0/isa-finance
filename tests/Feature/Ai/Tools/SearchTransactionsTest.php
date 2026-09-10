<?php

declare(strict_types=1);

use App\Ai\Tools\SearchTransactions;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Laravel\Ai\Tools\Request;

function searchTransactionsResult(User $user, array $args = []): array
{
    return json_decode((new SearchTransactions($user))->handle(new Request($args)), true, flags: JSON_THROW_ON_ERROR);
}

it('returns only the user transactions, newest first, with formatted amounts', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create(['name' => 'Nubank']);
    $category = Category::factory()->ownedBy($user)->expense()->create(['name' => 'Mercado']);

    Transaction::factory()->forAccount($account)->forCategory($category)
        ->on('2026-03-01')->amountCents(10_00)->create(['description' => 'Mais antiga']);
    Transaction::factory()->forAccount($account)->forCategory($category)
        ->on('2026-03-10')->amountCents(25_00)->create(['description' => 'Mais nova']);
    Transaction::factory()->create(['description' => 'De outro usuário']);

    $result = searchTransactionsResult($user);

    expect($result['total_count'])->toBe(2)
        ->and($result['returned_count'])->toBe(2)
        ->and($result['returned_total'])->toBe('R$ 35,00')
        ->and($result['transactions'][0]['description'])->toBe('Mais nova')
        ->and($result['transactions'][0]['amount'])->toBe('R$ 25,00')
        ->and($result['transactions'][0]['amount_cents'])->toBe(2500);
});

it('filters by account and category name case-insensitively', function (): void {
    $user = User::factory()->create();
    $nubank = Account::factory()->ownedBy($user)->create(['name' => 'Nubank']);
    $itau = Account::factory()->ownedBy($user)->create(['name' => 'Itaú']);
    $market = Category::factory()->ownedBy($user)->expense()->create(['name' => 'Mercado']);
    $fuel = Category::factory()->ownedBy($user)->expense()->create(['name' => 'Combustível']);

    Transaction::factory()->forAccount($nubank)->forCategory($market)->create(['description' => 'Feira']);
    Transaction::factory()->forAccount($itau)->forCategory($fuel)->create(['description' => 'Gasolina']);

    $result = searchTransactionsResult($user, ['account' => 'nubank', 'category' => 'MERCADO']);

    expect($result['returned_count'])->toBe(1)
        ->and($result['transactions'][0]['description'])->toBe('Feira')
        ->and($result['notes'])->toBe([]);
});

it('drops an unknown filter name and explains it in the notes', function (): void {
    $user = User::factory()->create();
    Transaction::factory()->ownedBy($user)->create(['description' => 'Compra']);

    $result = searchTransactionsResult($user, ['account' => 'Conta que não existe']);

    expect($result['returned_count'])->toBe(1)
        ->and($result['notes'])->toHaveCount(1)
        ->and($result['notes'][0])->toContain('não encontrada');
});

it('caps the returned rows at 50 while still counting every match', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create();
    $category = Category::factory()->ownedBy($user)->expense()->create();

    Transaction::factory()->count(55)->forAccount($account)->forCategory($category)->create();

    $result = searchTransactionsResult($user);

    expect($result['total_count'])->toBe(55)
        ->and($result['returned_count'])->toBe(50);
});
