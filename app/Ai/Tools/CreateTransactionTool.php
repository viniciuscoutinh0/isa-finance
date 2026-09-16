<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Actions\Transactions\CreateTransaction;
use App\Data\Money;
use App\Data\Transactions\TransactionData;
use App\Exceptions\Transactions\TransactionException;
use App\Models\User;
use App\Rules\Transactions\TransactionRules;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

final class CreateTransactionTool implements Approvable, Tool
{
    use InteractsWithApprovals;

    public function __construct(
        private readonly User $user,
    ) {}

    public function description(): string
    {
        return <<<'PROMPT'
                Record a new transaction (lançamento) for the user. The user reviews and confirms the
                account and category on screen before it is saved. Provide amount, description and (optionally)
                date, plus any account/category name the user mentioned as hints. Leave account_id and category_id null.
            PROMPT;
    }

    public function handle(Request $request): string
    {
        $request['date'] ??= CarbonImmutable::now()->toDateString();

        try {
            $validated = $request->validate(
                rules: TransactionRules::for($this->user),
                attributes: TransactionRules::attributes(),
            );

            $transaction = app(CreateTransaction::class)->handle(
                $this->user,
                TransactionData::fromArray($validated),
            );
        } catch (ValidationException $exception) {
            return 'Could not record the transaction: '.implode(' ', $exception->validator->errors()->all());
        } catch (TransactionException $exception) {
            return 'Could not record the transaction: '.$exception->getMessage();
        }

        return sprintf(
            'Recorded "%s" of %s in account "%s", category "%s", on %s.',
            $transaction->description,
            Money::fromCents($transaction->amount)->format(),
            $transaction->account->name,
            $transaction->category->name,
            $transaction->date->toDateString(),
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'amount' => $schema
                ->string()
                ->required()
                ->description('Amount in Brazilian Real, always positive, e.g. "150,00" or "1.234,56".'),

            'description' => $schema
                ->string()
                ->required()
                ->description('Short pt-BR description of the transaction.'),

            'date' => $schema
                ->string()
                ->description('Transaction date as YYYY-MM-DD. Defaults to today when omitted.'),

            'account' => $schema
                ->string()
                ->description('Account name the user mentioned, if any. A hint only.'),

            'category' => $schema
                ->string()
                ->description('Category name the user mentioned, if any. A hint only.'),

            'account_id' => $schema
                ->integer()
                ->description('Leave null. Set when the user picks the account on screen.'),

            'category_id' => $schema
                ->integer()
                ->description('Leave null. Set when the user picks the category on screen.'),
        ];
    }
}
