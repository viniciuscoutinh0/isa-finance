<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Actions\Transfers\CreateTransfer;
use App\Data\Money;
use App\Data\Transfers\TransferData;
use App\Exceptions\Transfers\TransferException;
use App\Models\User;
use App\Rules\Transfers\TransferRules;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Validation\ValidationException;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

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
        $request['date'] ??= CarbonImmutable::now()->toDateString();

        try {
            $data = $request->validate(
                rules: TransferRules::for($this->user),
                messages: TransferRules::messages(),
                attributes: TransferRules::attributes(),
            );

            $transfer = app(CreateTransfer::class)->handle($this->user, TransferData::fromArray($data));
        } catch (ValidationException $exception) {
            return 'Could not record the transfer: '.implode(' ', $exception->validator->errors()->all());
        } catch (TransferException $exception) {
            return 'Could not record the transfer: '.$exception->getMessage();
        }

        return sprintf(
            'Recorded a transfer of %s from "%s" to "%s" on %s.',
            Money::fromCents($transfer->amount)->format(),
            $transfer->fromAccount->name,
            $transfer->toAccount->name,
            $transfer->date->toDateString(),
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
