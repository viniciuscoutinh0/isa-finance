<?php

declare(strict_types=1);

namespace App\Data\Accounts;

use App\Data\Money;
use App\Enums\AccountType;

/**
 * An account plus its derived balance, as produced by AccountsOverviewQuery.
 * The balance is computed (ADR 0003), never stored.
 */
final readonly class AccountBalanceData
{
    public function __construct(
        public int $id,
        public string $name,
        public AccountType $type,
        public Money $initialBalance,
        public Money $balance,
        public bool $archived,
    ) {}

    /**
     * @param  object{id: int, name: string, type: string, initial_balance: int, balance: int|string, archived_at: string|null}  $row
     */
    public static function fromRow(object $row): self
    {
        return new self(
            id: (int) $row->id,
            name: $row->name,
            type: AccountType::from($row->type),
            initialBalance: Money::fromCents((int) $row->initial_balance),
            balance: Money::fromCents((int) $row->balance),
            archived: $row->archived_at !== null,
        );
    }
}
