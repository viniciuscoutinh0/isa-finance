<?php

declare(strict_types=1);

use App\Rules\MoneyString;
use Illuminate\Support\Facades\Validator;

function moneyFails(string $value, MoneyString $rule): bool
{
    return Validator::make(['amount' => $value], ['amount' => $rule])->fails();
}

it('accepts pt-BR money strings', function (string $value): void {
    expect(moneyFails($value, new MoneyString))->toBeFalse();
})->with(['1.234,56', '99,90', '1000', '0,00', '-500,00']);

it('rejects a non-numeric string', function (): void {
    expect(moneyFails('abc', new MoneyString))->toBeTrue();
});

it('can forbid negative values', function (): void {
    expect(moneyFails('-1,00', new MoneyString(allowNegative: false)))->toBeTrue()
        ->and(moneyFails('1,00', new MoneyString(allowNegative: false)))->toBeFalse();
});

it('can forbid zero', function (): void {
    expect(moneyFails('0,00', new MoneyString(allowZero: false)))->toBeTrue()
        ->and(moneyFails('0,01', new MoneyString(allowZero: false)))->toBeFalse();
});
