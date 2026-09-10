<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Data\Transactions\MonthlyCashFlowData;
use App\Models\User;
use App\Queries\Transactions\MonthlyCashFlowQuery;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Read tool: income vs. expense totals per calendar month, current month last.
 * Use this for "how much did I earn / spend in month X" questions.
 */
final class MonthlyCashFlow implements Tool
{
    public function __construct(private readonly User $user) {}

    public function description(): string
    {
        return 'Income and expense totals per calendar month for the user, most recent month last. '
            .'Best tool for "how much did I earn/spend in <month>" questions.';
    }

    public function handle(Request $request): string
    {
        $months = max(1, min(12, $request->integer('months', 6)));

        $flow = app(MonthlyCashFlowQuery::class)->handle($this->user, $months);

        return json_encode([
            'months' => $flow->map(fn (MonthlyCashFlowData $month): array => [
                'month' => $month->month->format('Y-m'),
                'label' => $month->monthLabel(),
                'income_cents' => $month->income->cents,
                'income' => $month->income->format(),
                'expense_cents' => $month->expense->cents,
                'expense' => $month->expense->format(),
                'net_cents' => $month->net()->cents,
                'net' => $month->net()->format(),
            ])->all(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'months' => $schema->integer()->min(1)->max(12)
                ->description('How many months back to include, ending with the current month. Defaults to 6.'),
        ];
    }
}
