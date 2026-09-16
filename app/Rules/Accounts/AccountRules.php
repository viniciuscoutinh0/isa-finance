<?php

declare(strict_types=1);

namespace App\Rules\Accounts;

use App\Enums\AccountType;
use App\Rules\MoneyString;
use Illuminate\Validation\Rule;

final class AccountRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function for(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'type' => [
                'required',
                Rule::enum(AccountType::class),
            ],
            'initial_balance' => [
                'required',
                'string',
                new MoneyString(allowNegative: true, allowZero: true),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributes(): array
    {
        return [
            'name' => 'nome',
            'type' => 'tipo',
            'initial_balance' => 'saldo inicial',
        ];
    }
}
