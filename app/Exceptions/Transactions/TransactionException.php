<?php

declare(strict_types=1);

namespace App\Exceptions\Transactions;

use RuntimeException;

final class TransactionException extends RuntimeException
{
    public static function invalidAccount(): self
    {
        return new self('Conta não existe ou não pertence ao usuário atual.');
    }

    public static function invalidCategory(): self
    {
        return new self('Categoria não existe ou não pertence ao usuário atual.');
    }
}
