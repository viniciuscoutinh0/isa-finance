<?php

declare(strict_types=1);

namespace App\Data;

use InvalidArgumentException;

/**
 * A monetary amount in Brazilian Real, held as an integer of centavos.
 *
 * There is no currency field: the whole application is BRL-only
 * (see docs/adr/0002-dinheiro-em-centavos.md). Parsing and formatting
 * pt-BR strings ("R$ 1.234,56") happens here, at the edge, and nowhere else.
 */
final readonly class Money
{
    public function __construct(public int $cents) {}

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    /**
     * Build from a numeric amount of reais (e.g. 1234.56 => 123456 centavos).
     */
    public static function fromReais(int|float $reais): self
    {
        return new self((int) round($reais * 100));
    }

    /**
     * Parse a user-typed pt-BR string: "R$ 1.234,56", "1.234,56", "1234,5", "1234".
     */
    public static function parse(string $input): self
    {
        $clean = trim($input);
        $clean = preg_replace('/[^\d,.-]/', '', $clean) ?? '';

        if ($clean === '' || $clean === '-') {
            throw new InvalidArgumentException("Not a valid money string: [{$input}]");
        }

        $negative = str_starts_with($clean, '-');
        $clean = ltrim($clean, '-');

        if (str_contains($clean, ',')) {
            // Comma is the decimal separator; dots are thousands separators.
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (substr_count($clean, '.') > 1) {
            // Multiple dots can only be thousands separators.
            $clean = str_replace('.', '', $clean);
        }
        // A single dot is left as-is: treated as the decimal separator.

        if (! is_numeric($clean)) {
            throw new InvalidArgumentException("Not a valid money string: [{$input}]");
        }

        $cents = (int) round(((float) $clean) * 100);

        return new self($negative ? -$cents : $cents);
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    public function isNegative(): bool
    {
        return $this->cents < 0;
    }

    public function isPositive(): bool
    {
        return $this->cents > 0;
    }

    public function abs(): self
    {
        return new self(abs($this->cents));
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function subtract(self $other): self
    {
        return new self($this->cents - $other->cents);
    }

    /**
     * "R$ 1.234,56" — always two decimal places, a leading minus for negatives.
     */
    public function format(): string
    {
        $formatted = number_format(abs($this->cents) / 100, 2, ',', '.');

        return ($this->isNegative() ? '-R$ ' : 'R$ ').$formatted;
    }

    /**
     * "1.234,56" — no currency symbol, for prefilling an editable input.
     */
    public function forInput(): string
    {
        $formatted = number_format(abs($this->cents) / 100, 2, ',', '.');

        return ($this->isNegative() ? '-' : '').$formatted;
    }
}
