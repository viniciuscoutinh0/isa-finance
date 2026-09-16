<?php

declare(strict_types=1);

namespace App\Data;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(public int $cents) {}

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function fromReais(int|float $reais): self
    {
        return new self((int) round($reais * 100));
    }

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
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (substr_count($clean, '.') > 1) {
            $clean = str_replace('.', '', $clean);
        }

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

    public function format(): string
    {
        $formatted = number_format(abs($this->cents) / 100, 2, ',', '.');

        return ($this->isNegative() ? '-R$ ' : 'R$ ').$formatted;
    }

    public function forInput(): string
    {
        $formatted = number_format(abs($this->cents) / 100, 2, ',', '.');

        return ($this->isNegative() ? '-' : '').$formatted;
    }
}
