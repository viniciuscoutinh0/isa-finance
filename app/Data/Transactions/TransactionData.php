<?php

declare(strict_types=1);

namespace App\Data\Transactions;

use App\Data\Money;
use Carbon\CarbonImmutable;

final readonly class TransactionData
{
    public function __construct(
        public int $accountId,
        public int $categoryId,
        public CarbonImmutable $date,
        public string $description,
        public Money $amount,
        public ?string $notes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            accountId: (int) $data['accountId'],
            categoryId: (int) $data['categoryId'],
            date: CarbonImmutable::parse($data['date']),
            description: $data['description'],
            amount: Money::parse($data['amount']),
            notes: filled($data['notes']) ? trim($data['notes']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'account_id' => $this->accountId,
            'category_id' => $this->categoryId,
            'date' => $this->date,
            'description' => trim($this->description),
            'amount' => $this->amount->cents,
            'notes' => $this->notes,
        ];
    }
}
