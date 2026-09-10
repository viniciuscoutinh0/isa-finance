<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Data\Money;
use App\Enums\CategoryType;
use App\Models\Transaction;
use App\Models\User;
use App\Queries\Accounts\AccountsQuery;
use App\Queries\Categories\CategoriesQuery;
use App\Queries\Transactions\TransactionsQuery;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Read tool: search the user's transactions by account, category, type, date
 * range or description text. Account and category are matched by name; an
 * unmatched name is reported and its filter is dropped rather than failing.
 */
final class SearchTransactions implements Tool
{
    private const MAX_ROWS = 50;

    public function __construct(private readonly User $user) {}

    public function description(): string
    {
        return 'Search the current user\'s transactions (lançamentos). All filters are optional and combine with AND. '
            .'Returns up to '.self::MAX_ROWS.' rows, newest first, plus the total match count and the total of the returned rows.';
    }

    public function handle(Request $request): string
    {
        $notes = [];

        $accountId = $this->resolveAccountId($request->string('account')->toString(), $notes);
        $categoryId = $this->resolveCategoryId($request->string('category')->toString(), $notes);
        $type = $this->resolveType($request->string('type')->toString(), $notes);

        $filters = [
            'account_id' => $accountId,
            'category_id' => $categoryId,
            'type' => $type,
            'from' => $this->cleanDate($request->string('from')->toString()),
            'to' => $this->cleanDate($request->string('to')->toString()),
            'search' => $request->string('search')->toString() ?: null,
        ];

        $results = app(TransactionsQuery::class)->handle($this->user, $filters, self::MAX_ROWS);

        /** @var array<int, Transaction> $rows */
        $rows = $results->items();
        $shownTotalCents = array_sum(array_map(static fn (Transaction $t): int => $t->amount, $rows));

        return json_encode([
            'notes' => $notes,
            'total_count' => $results->total(),
            'returned_count' => count($rows),
            'returned_total_cents' => $shownTotalCents,
            'returned_total' => Money::fromCents($shownTotalCents)->format(),
            'transactions' => array_map(fn (Transaction $t): array => [
                'date' => $t->date->toDateString(),
                'description' => $t->description,
                'account' => $t->account->name,
                'category' => $t->category->name,
                'type' => $t->category->type->label(),
                'amount_cents' => $t->amount,
                'amount' => Money::fromCents($t->amount)->format(),
            ], $rows),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'account' => $schema->string()->description('Account name to filter by. Optional.'),
            'category' => $schema->string()->description('Category name to filter by. Optional.'),
            'type' => $schema->string()->enum(['income', 'expense'])
                ->description('income = entrada, expense = saída. Optional.'),
            'from' => $schema->string()->description('Start date (inclusive) as YYYY-MM-DD. Optional.'),
            'to' => $schema->string()->description('End date (inclusive) as YYYY-MM-DD. Optional.'),
            'search' => $schema->string()->description('Text to match within the transaction description. Optional.'),
        ];
    }

    /**
     * @param  list<string>  $notes
     */
    private function resolveAccountId(string $name, array &$notes): ?int
    {
        if ($name === '') {
            return null;
        }

        $account = app(AccountsQuery::class)->handle($this->user, includeArchived: true)
            ->first(fn ($account): bool => Str::lower($account->name) === Str::lower($name));

        if ($account === null) {
            $notes[] = "Conta \"{$name}\" não encontrada; filtro de conta ignorado.";

            return null;
        }

        return $account->id;
    }

    /**
     * @param  list<string>  $notes
     */
    private function resolveCategoryId(string $name, array &$notes): ?int
    {
        if ($name === '') {
            return null;
        }

        $category = app(CategoriesQuery::class)->handle($this->user)
            ->first(fn ($category): bool => Str::lower($category->name) === Str::lower($name));

        if ($category === null) {
            $notes[] = "Categoria \"{$name}\" não encontrada; filtro de categoria ignorado.";

            return null;
        }

        return $category->id;
    }

    /**
     * @param  list<string>  $notes
     */
    private function resolveType(string $type, array &$notes): ?CategoryType
    {
        if ($type === '') {
            return null;
        }

        $resolved = CategoryType::tryFrom($type);

        if ($resolved === null) {
            $notes[] = "Tipo \"{$type}\" desconhecido; filtro de tipo ignorado.";
        }

        return $resolved;
    }

    private function cleanDate(string $date): ?string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? $date : null;
    }
}
