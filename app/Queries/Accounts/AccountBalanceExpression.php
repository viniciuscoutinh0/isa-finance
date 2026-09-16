<?php

declare(strict_types=1);

namespace App\Queries\Accounts;

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
