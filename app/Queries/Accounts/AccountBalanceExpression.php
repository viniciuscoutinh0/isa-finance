<?php

declare(strict_types=1);

namespace App\Queries\Accounts;

/**
 * The single SQL definition of a derived account balance (ADR 0003), shared
 * by AccountsOverviewQuery and AccountBalanceQuery so the two never drift.
 *
 * Correlates on `accounts.id`, so it must be used in a query whose FROM/join
 * exposes the `accounts` table. Portable across SQL Server and SQLite.
 */
final class AccountBalanceExpression
{
    public const SQL = <<<'SQL'
        accounts.initial_balance
        + COALESCE((
            SELECT SUM(CASE WHEN categories.type = 'income'
                            THEN transactions.amount
                            ELSE -transactions.amount END)
            FROM transactions
            INNER JOIN categories ON categories.id = transactions.category_id
            WHERE transactions.account_id = accounts.id
        ), 0)
        - COALESCE((
            SELECT SUM(transfers.amount) FROM transfers
            WHERE transfers.from_account_id = accounts.id
        ), 0)
        + COALESCE((
            SELECT SUM(transfers.amount) FROM transfers
            WHERE transfers.to_account_id = accounts.id
        ), 0)
        SQL;
}
