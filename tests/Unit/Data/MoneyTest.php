<?php

declare(strict_types=1);

use App\Data\Money;

it('holds an integer of centavos', function () {
    expect(Money::fromCents(123456)->cents)->toBe(123456);
});

it('builds from an amount of reais', function () {
    expect(Money::fromReais(1234.56)->cents)->toBe(123456)
        ->and(Money::fromReais(10)->cents)->toBe(1000)
        ->and(Money::fromReais(0.1)->cents)->toBe(10);
});

it('parses pt-BR strings the user types', function (string $input, int $cents) {
    expect(Money::parse($input)->cents)->toBe($cents);
})->with([
    'plain integer' => ['1234', 123400],
    'comma decimal' => ['1234,56', 123456],
    'thousands and decimal' => ['1.234,56', 123456],
    'millions' => ['1.234.567,89', 123456789],
    'with currency symbol' => ['R$ 1.234,56', 123456],
    'single dot as decimal' => ['1234.56', 123456],
    'trailing whitespace' => ['  99,90 ', 9990],
    'one decimal digit' => ['5,5', 550],
    'negative' => ['-1.000,00', -100000],
]);

it('rejects a string with no digits', function () {
    expect(fn () => Money::parse('abc'))->toThrow(InvalidArgumentException::class);
});

it('formats as pt-BR currency', function (int $cents, string $expected) {
    expect(Money::fromCents($cents)->format())->toBe($expected);
})->with([
    [123456, 'R$ 1.234,56'],
    [0, 'R$ 0,00'],
    [9, 'R$ 0,09'],
    [-100000, '-R$ 1.000,00'],
    [100000000, 'R$ 1.000.000,00'],
]);

it('formats for an editable input without the currency symbol', function (int $cents, string $expected) {
    expect(Money::fromCents($cents)->forInput())->toBe($expected);
})->with([
    [123456, '1.234,56'],
    [0, '0,00'],
    [-100000, '-1.000,00'],
    [9, '0,09'],
]);

it('reports sign and adds and subtracts producing new instances', function () {
    $a = Money::fromCents(1000);
    $b = Money::fromCents(250);

    expect($a->add($b)->cents)->toBe(1250)
        ->and($a->subtract($b)->cents)->toBe(750)
        ->and($a->subtract(Money::fromCents(3000))->isNegative())->toBeTrue()
        ->and(Money::fromCents(0)->isZero())->toBeTrue()
        ->and($a->cents)->toBe(1000);
});
