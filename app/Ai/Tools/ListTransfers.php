<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Data\Money;
use App\Models\Transfer;
use App\Models\User;
use App\Queries\Accounts\AccountsQuery;
use App\Queries\Transfers\TransfersQuery;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

final class ListTransfers implements Tool
{
    private const MAX_ROWS = 50;

    public function __construct(private readonly User $user) {}

    public function description(): string
    {
        return 'List the current user\'s transfers (transferências) between their own accounts, newest first. '
            .'All filters optional. Returns up to '.self::MAX_ROWS.' rows plus the total match count.';
    }

    public function handle(Request $request): string
    {
        $notes = [];
        $accountName = $request->string('account')->toString();
        $accountId = null;

        if ($accountName !== '') {
            $account = app(AccountsQuery::class)->handle($this->user, includeArchived: true)
                ->first(fn ($account): bool => Str::lower($account->name) === Str::lower($accountName));

            if ($account === null) {
                $notes[] = "Conta \"{$accountName}\" não encontrada; filtro de conta ignorado.";
            } else {
                $accountId = $account->id;
            }
        }

        $results = app(TransfersQuery::class)->handle($this->user, [
            'account_id' => $accountId,
            'from' => $this->cleanDate($request->string('from')->toString()),
            'to' => $this->cleanDate($request->string('to')->toString()),
        ], self::MAX_ROWS);

        /** @var array<int, Transfer> $rows */
        $rows = $results->items();

        return json_encode([
            'notes' => $notes,
            'total_count' => $results->total(),
            'returned_count' => count($rows),
            'transfers' => array_map(fn (Transfer $transfer): array => [
                'date' => $transfer->date->toDateString(),
                'from' => $transfer->fromAccount->name,
                'to' => $transfer->toAccount->name,
                'amount_cents' => $transfer->amount,
                'amount' => Money::fromCents($transfer->amount)->format(),
            ], $rows),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'account' => $schema->string()->description('Account name on either side of the transfer. Optional.'),
            'from' => $schema->string()->description('Start date (inclusive) as YYYY-MM-DD. Optional.'),
            'to' => $schema->string()->description('End date (inclusive) as YYYY-MM-DD. Optional.'),
        ];
    }

    private function cleanDate(string $date): ?string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? $date : null;
    }
}
