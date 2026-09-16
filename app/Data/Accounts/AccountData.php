<?php

declare(strict_types=1);

namespace App\Data\Accounts;

use App\Data\Money;
use App\Enums\AccountType;

final readonly class AccountData
{
    public function __construct(
        public string $name,
        public AccountType $type,
        public Money $initialBalance,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: trim($data['name']),
            type: AccountType::from($data['type']),
            initialBalance: Money::parse($data['initial_balance']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'initial_balance' => $this->initialBalance->cents,
        ];
    }
}
