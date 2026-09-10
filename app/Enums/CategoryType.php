<?php

declare(strict_types=1);

namespace App\Enums;

enum CategoryType: string
{
    case Income = 'income';
    case Expense = 'expense';

    /**
     * pt-BR label shown to the user.
     */
    public function label(): string
    {
        return match ($this) {
            self::Income => 'Entrada',
            self::Expense => 'Saída',
        };
    }

    /**
     * Flux badge / accent color for this type.
     */
    public function color(): string
    {
        return match ($this) {
            self::Income => 'green',
            self::Expense => 'rose',
        };
    }

    /**
     * Sign applied to a Transaction amount of this type when summing a balance.
     */
    public function sign(): int
    {
        return match ($this) {
            self::Income => 1,
            self::Expense => -1,
        };
    }
}
