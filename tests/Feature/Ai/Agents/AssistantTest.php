<?php

declare(strict_types=1);

use App\Ai\Agents\Assistant;
use App\Ai\Tools\CreateTransactionTool;
use App\Ai\Tools\CreateTransferTool;
use App\Ai\Tools\ListAccounts;
use App\Ai\Tools\SearchTransactions;
use App\Models\Account;
use App\Models\Category;
use App\Models\User;

it('answers a prompt through the faked gateway', function (): void {
    Assistant::fake(['Você tem R$ 100,00 na conta Nubank.']);

    $response = Assistant::make(user: User::factory()->create())->prompt('quanto eu tenho?');

    expect((string) $response)->toBe('Você tem R$ 100,00 na conta Nubank.');
    Assistant::assertPrompted('quanto eu tenho?');
});

it('grounds its instructions in the user own accounts and categories', function (): void {
    $user = User::factory()->create();
    Account::factory()->ownedBy($user)->create(['name' => 'Nubank']);
    Category::factory()->ownedBy($user)->expense()->create(['name' => 'Mercado']);

    $instructions = (new Assistant($user))->instructions();

    expect($instructions)->toContain('Nubank')
        ->toContain('Mercado')
        ->toContain('pt-BR');
});

it('exposes the finance read and write tools', function (): void {
    $tools = collect((new Assistant(User::factory()->create()))->tools())
        ->map(fn (object $tool): string => $tool::class);

    expect($tools)->toContain(ListAccounts::class)
        ->toContain(SearchTransactions::class)
        ->toContain(CreateTransactionTool::class)
        ->toContain(CreateTransferTool::class);
});
