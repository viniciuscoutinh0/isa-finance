<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\User;
use App\Queries\Accounts\AccountsOverviewQuery;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

final class ListAccounts implements Tool
{
    public function __construct(private readonly User $user) {}

    public function description(): string
    {
        return 'List the current user\'s active accounts with their type and current balance. '
            .'Call this before answering questions about account balances.';
    }

    public function handle(Request $request): string
    {
        $accounts = app(AccountsOverviewQuery::class)->handle($this->user);

        if ($accounts->isEmpty()) {
            return json_encode(['accounts' => [], 'note' => 'The user has no accounts yet.'], JSON_THROW_ON_ERROR);
        }

        return json_encode([
            'accounts' => $accounts->map(fn ($account): array => [
                'name' => $account->name,
                'type' => $account->type->label(),
                'balance_cents' => $account->balance->cents,
                'balance' => $account->balance->format(),
            ])->all(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
