<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Tools\CreateTransactionTool;
use App\Ai\Tools\CreateTransferTool;
use App\Ai\Tools\ListAccounts;
use App\Ai\Tools\ListCategories;
use App\Ai\Tools\ListTransfers;
use App\Ai\Tools\MonthlyCashFlow;
use App\Ai\Tools\SearchTransactions;
use App\Models\User;
use App\Queries\Accounts\AccountsQuery;
use App\Queries\Categories\CategoriesQuery;
use Carbon\CarbonImmutable;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

#[MaxSteps(8)]
#[MaxTokens(2048)]
final class Assistant implements Agent, Conversational, HasTools
{
    use Promptable;
    use RemembersConversations;

    public function __construct(private readonly User $user) {}

    public function instructions(): string
    {
        $today = CarbonImmutable::now()->toDateString();
        $accounts = app(AccountsQuery::class)->handle($this->user)
            ->map(fn ($account): string => "- {$account->name} ({$account->type->label()})")
            ->implode("\n");
        $categories = app(CategoriesQuery::class)->handle($this->user)
            ->map(fn ($category): string => "- {$category->name} ({$category->type->label()})")
            ->implode("\n");

        return <<<PROMPT
        You are the finance assistant built into "Blade", a personal finance app. You help the
        signed-in user understand and record their own money: accounts, transactions
        (lançamentos) and transfers (transferências) between accounts.

        Today is {$today}.

        Rules:
        - Always answer in Brazilian Portuguese (pt-BR), even though these instructions are in English.
        - Format every monetary value as "R$ 1.234,56".
        - Only discuss this user's finances inside this app. If asked about anything else
          (general knowledge, other people, investing advice, code, etc.), decline in one short
          sentence and offer to help with their accounts instead.
        - Never invent numbers, balances, dates, account names or category names. To answer any
          factual question about the user's money, first call a read tool and base the answer on
          what it returns.
        - Prefer MonthlyCashFlow for "how much did I earn/spend in month X" questions, and
          SearchTransactions to list or inspect individual entries.
        - When the user wants to record a transaction or a transfer, call the matching write tool
          (CreateTransactionTool / CreateTransferTool) with your best guess for amount, description
          and date. Put any account or category the user named in the "account" / "category" hint
          fields, but always leave account_id / category_id (and the transfer *_account_id fields)
          null: the user selects the real account and category with on-screen buttons before the
          operation runs.
        - Keep answers short and direct. Use simple Markdown (bold, short lists) when it helps.

        The user's accounts:
        {$accounts}

        The user's categories:
        {$categories}
        PROMPT;
    }

    /**
     * @return list<ListAccounts|ListCategories|SearchTransactions|MonthlyCashFlow|ListTransfers|CreateTransactionTool|CreateTransferTool>
     */
    public function tools(): iterable
    {
        return [
            new ListAccounts($this->user),
            new ListCategories($this->user),
            new SearchTransactions($this->user),
            new MonthlyCashFlow($this->user),
            new ListTransfers($this->user),
            new CreateTransactionTool($this->user),
            new CreateTransferTool($this->user),
        ];
    }
}
