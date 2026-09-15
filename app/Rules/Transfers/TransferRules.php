<?php

declare(strict_types=1);

namespace App\Rules\Transfers;

use App\Models\User;
use App\Rules\MoneyString;
use Illuminate\Validation\Rule;

/**
 * Validation rules for a transfer payload, shared by every boundary that
 * accepts one.
 *
 * Keys are snake_case: they cross a boundary (wire payloads, validated arrays),
 * so they follow the wire naming, not PHP property style.
 */
final class TransferRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function for(User $user): array
    {
        $ownAccount = Rule::exists('accounts', 'id')
            ->where('user_id', $user->id)
            ->whereNull('archived_at');

        return [
            'from_account_id' => [
                'required',
                $ownAccount,
            ],
            'to_account_id' => [
                'required',
                'different:from_account_id',
                $ownAccount,
            ],
            'date' => [
                'required',
                'date',
            ],
            'amount' => [
                'required',
                'string',
                new MoneyString(allowNegative: false, allowZero: false),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributes(): array
    {
        return [
            'from_account_id' => 'conta de origem',
            'to_account_id' => 'conta de destino',
            'date' => 'data',
            'amount' => 'valor',
            'notes' => 'observação',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'to_account_id.different' => 'A conta de destino deve ser diferente da conta de origem.',
        ];
    }
}
