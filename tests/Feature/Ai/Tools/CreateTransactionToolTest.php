<?php

declare(strict_types=1);

use App\Ai\Tools\CreateTransactionTool;
use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Tools\Request;

it('always requires approval', function (): void {
    $tool = new CreateTransactionTool(User::factory()->create());

    expect($tool->shouldRequestApproval(new Request))->toBeInstanceOf(Approval::class);
});

it('records the transaction once the picked account and category are supplied', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create(['name' => 'Nubank']);
    $category = Category::factory()->ownedBy($user)->expense()->create(['name' => 'Mercado']);

    $message = (new CreateTransactionTool($user))->handle(new Request([
        'amount' => '150,00',
        'description' => 'Feira do mês',
        'date' => '2026-03-10',
        'account_id' => $account->id,
        'category_id' => $category->id,
    ]));

    expect($message)->toContain('Feira do mês')->toContain('R$ 150,00');

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'description' => 'Feira do mês',
        'amount' => 15000,
    ]);
});

it('defaults the date to today when omitted', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create();
    $category = Category::factory()->ownedBy($user)->expense()->create();

    (new CreateTransactionTool($user))->handle(new Request([
        'amount' => '10,00',
        'description' => 'Café',
        'account_id' => $account->id,
        'category_id' => $category->id,
    ]));

    expect($user->transactions()->sole()->date->toDateString())->toBe(today()->toDateString());
});

it('refuses an account that belongs to another user without creating anything', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->ownedBy($user)->expense()->create();
    $strangersAccount = Account::factory()->create();

    $message = (new CreateTransactionTool($user))->handle(new Request([
        'amount' => '10,00',
        'description' => 'Tentativa',
        'account_id' => $strangersAccount->id,
        'category_id' => $category->id,
    ]));

    expect($message)->toContain('Could not record the transaction');
    $this->assertDatabaseCount('transactions', 0);
});

it('rejects a zero or negative amount', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->ownedBy($user)->create();
    $category = Category::factory()->ownedBy($user)->expense()->create();

    $message = (new CreateTransactionTool($user))->handle(new Request([
        'amount' => '0,00',
        'description' => 'Nada',
        'account_id' => $account->id,
        'category_id' => $category->id,
    ]));

    expect($message)->toContain('Could not record the transaction');
    $this->assertDatabaseCount('transactions', 0);
});
