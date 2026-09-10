<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Actions\Transactions\CreateTransaction;
use App\Data\Money;
use App\Models\User;
use App\Rules\MoneyString;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Write tool: record a transaction for the user. Always approval-gated — the
 * account and category are chosen by the user on screen and arrive as
 * account_id / category_id in the (edited) approval arguments.
 */
final class CreateTransactionTool implements Approvable, Tool
{
    use InteractsWithApprovals;

    public function __construct(private readonly User $user) {}

    public function description(): string
    {
        return 'Record a new transaction (lançamento) for the user. The user reviews and confirms the '
            .'account and category on screen before it is saved. Provide amount, description and (optionally) '
            .'date, plus any account/category name the user mentioned as hints. Leave account_id and category_id null.';
    }

    public function handle(Request $request): string
    {
        try {
            $data = $request->validate([
                'amount' => ['required', 'string', new MoneyString(allowNegative: false, allowZero: false)],
                'description' => ['required', 'string', 'max:255'],
                'date' => ['nullable', 'date'],
                'account_id' => [
                    'required', 'integer',
                    Rule::exists('accounts', 'id')->where('user_id', $this->user->id)->whereNull('archived_at'),
                ],
                'category_id' => [
                    'required', 'integer',
                    Rule::exists('categories', 'id')->where('user_id', $this->user->id),
                ],
            ]);
        } catch (ValidationException $e) {
            return 'Could not record the transaction: '.implode(' ', $e->validator->errors()->all());
        }

        $account = $this->user->accounts()->findOrFail($data['account_id']);
        $category = $this->user->categories()->findOrFail($data['category_id']);
        $date = CarbonImmutable::parse($data['date'] ?? CarbonImmutable::now()->toDateString())->startOfDay();

        $transaction = app(CreateTransaction::class)->handle(
            $this->user,
            $account,
            $category,
            $date,
            $data['description'],
            Money::parse($data['amount']),
            null,
        );

        return sprintf(
            'Recorded "%s" of %s in account "%s", category "%s", on %s.',
            $transaction->description,
            Money::fromCents($transaction->amount)->format(),
            $account->name,
            $category->name,
            $date->toDateString(),
        );
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'amount' => $schema->string()->required()
                ->description('Amount in Brazilian Real, always positive, e.g. "150,00" or "1.234,56".'),
            'description' => $schema->string()->required()
                ->description('Short pt-BR description of the transaction.'),
            'date' => $schema->string()
                ->description('Transaction date as YYYY-MM-DD. Defaults to today when omitted.'),
            'account' => $schema->string()
                ->description('Account name the user mentioned, if any. A hint only.'),
            'category' => $schema->string()
                ->description('Category name the user mentioned, if any. A hint only.'),
            'account_id' => $schema->integer()
                ->description('Leave null. Set when the user picks the account on screen.'),
            'category_id' => $schema->integer()
                ->description('Leave null. Set when the user picks the category on screen.'),
        ];
    }
}
