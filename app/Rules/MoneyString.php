<?php

declare(strict_types=1);

namespace App\Rules;

use App\Data\Money;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;

final class MoneyString implements ValidationRule
{
    public function __construct(
        private readonly bool $allowNegative = true,
        private readonly bool $allowZero = true,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_int($value)) {
            $fail('O campo :attribute deve ser um valor válido.');

            return;
        }

        try {
            $money = Money::parse((string) $value);
        } catch (InvalidArgumentException) {
            $fail('O campo :attribute deve ser um valor válido.');

            return;
        }

        if (! $this->allowNegative && $money->isNegative()) {
            $fail('O campo :attribute não pode ser negativo.');

            return;
        }

        if (! $this->allowZero && $money->isZero()) {
            $fail('O campo :attribute deve ser maior que zero.');
        }
    }
}
