<?php

declare(strict_types=1);

namespace App\Rules\Transactions;

use App\Models\User;
use App\Rules\MoneyString;
use Illuminate\Validation\Rule;

final class TransactionRules
{
    public static function for(User $user): array
    {
        return [
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where('user_id', $user->id)->whereNull('archived_at'),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', $user->id),
            ],
            'date' => [
                'required',
                'date',
                'date_format:Y-m-d',
            ],
            'description' => [
                'required',
                'string',
                'max:255',
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

    public static function attributes(): array
    {
        return [
            'account_id' => 'conta',
            'category_id' => 'categoria',
            'date' => 'data',
            'description' => 'descrição',
            'amount' => 'valor',
            'notes' => 'observação',
        ];
    }
}
