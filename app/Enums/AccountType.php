<?php

declare(strict_types=1);

namespace App\Enums;

enum AccountType: string
{
    case Checking = 'checking';
    case Savings = 'savings';
    case Cash = 'cash';
    case CreditCard = 'credit_card';

    /**
     * pt-BR label shown to the user.
     */
    public function label(): string
    {
        return match ($this) {
            self::Checking => 'Conta-corrente',
            self::Savings => 'Poupança',
            self::Cash => 'Dinheiro',
            self::CreditCard => 'Cartão de crédito',
        };
    }

    /**
     * Heroicon name for this account type.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Checking => 'building-library',
            self::Savings => 'banknotes',
            self::Cash => 'wallet',
            self::CreditCard => 'credit-card',
        };
    }
}
