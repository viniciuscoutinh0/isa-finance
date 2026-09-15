<?php

declare(strict_types=1);

namespace App\Data\Transfers;

use App\Data\Money;
use Carbon\CarbonImmutable;

final readonly class TransferData
{
    public function __construct(
        public int $fromAccountId,
        public int $toAccountId,
        public CarbonImmutable $date,
        public Money $amount,
        public ?string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fromAccountId: (int) $data['from_account_id'],
            toAccountId: (int) $data['to_account_id'],
            date: CarbonImmutable::parse($data['date']),
            amount: Money::parse($data['amount']),
            notes: filled($data['notes'] ?? null) ? trim($data['notes']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'from_account_id' => $this->fromAccountId,
            'to_account_id' => $this->toAccountId,
            'date' => $this->date,
            'amount' => $this->amount->cents,
            'notes' => $this->notes,
        ];
    }
}
