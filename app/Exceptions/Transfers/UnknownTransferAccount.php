<?php

declare(strict_types=1);

namespace App\Exceptions\Transfers;

final class UnknownTransferAccount extends TransferException
{
    public static function make(): self
    {
        return new self('A transfer must use accounts that belong to the user.');
    }
}
