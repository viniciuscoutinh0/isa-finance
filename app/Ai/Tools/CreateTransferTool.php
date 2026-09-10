<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Actions\Transfers\CreateTransfer;
use App\Data\Money;
use App\Exceptions\Transfers\SameAccountTransfer;
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
 * Write tool: record a transfer between two of the user's own accounts. Always
 * approval-gated — both accounts are chosen on screen and arrive as
 * from_account_id / to_account_id in the (edited) approval arguments.
 */
final class CreateTransferTool implements Approvable, Tool
{
    use InteractsWithApprovals;

    public function __construct(private readonly User $user) {}

    public function description(): string
    {
        return 'Record a transfer (transferência) moving money between two of the user\'s own accounts. '
            .'The user picks the origin and destination accounts on screen before it is saved. Provide amount '
            .'and (optionally) date, plus any account names mentioned as hints. Leave the *_account_id fields null.';
    }

    public function handle(Request $request): string
    {
        try {
            $data = $request->validate([
                'amount' => ['required', 'string', new MoneyString(allowNegative: false, allowZero: false)],
                'date' => ['nullable', 'date'],
                'from_account_id' => [
                    'required', 'integer', 'different:to_account_id',
                    Rule::exists('accounts', 'id')->where('user_id', $this->user->id)->whereNull('archived_at'),
                ],
                'to_account_id' => [
                    'required', 'integer',
                    Rule::exists('accounts', 'id')->where('user_id', $this->user->id)->whereNull('archived_at'),
                ],
            ]);
        } catch (ValidationException $e) {
            return 'Could not record the transfer: '.implode(' ', $e->validator->errors()->all());
        }

        $from = $this->user->accounts()->findOrFail($data['from_account_id']);
        $to = $this->user->accounts()->findOrFail($data['to_account_id']);
        $date = CarbonImmutable::parse($data['date'] ?? CarbonImmutable::now()->toDateString())->startOfDay();

        try {
            $transfer = app(CreateTransfer::class)->handle($this->user, $from, $to, $date, Money::parse($data['amount']), null);
        } catch (SameAccountTransfer $e) {
            return 'Could not record the transfer: '.$e->getMessage();
        }

        return sprintf(
            'Recorded a transfer of %s from "%s" to "%s" on %s.',
            Money::fromCents($transfer->amount)->format(),
            $from->name,
            $to->name,
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
            'date' => $schema->string()
                ->description('Transfer date as YYYY-MM-DD. Defaults to today when omitted.'),
            'from_account' => $schema->string()
                ->description('Origin account name the user mentioned, if any. A hint only.'),
            'to_account' => $schema->string()
                ->description('Destination account name the user mentioned, if any. A hint only.'),
            'from_account_id' => $schema->integer()
                ->description('Leave null. Set when the user picks the origin account on screen.'),
            'to_account_id' => $schema->integer()
                ->description('Leave null. Set when the user picks the destination account on screen.'),
        ];
    }
}
