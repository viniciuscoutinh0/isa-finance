<?php

declare(strict_types=1);

namespace App\Exceptions\Transfers;

final class SameAccountTransfer extends TransferException
{
    public static function make(): self
    {
        return new self('A transfer must be between two different accounts.');
    }
}
