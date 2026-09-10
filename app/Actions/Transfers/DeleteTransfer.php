<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Models\Transfer;

final readonly class DeleteTransfer
{
    public function handle(Transfer $transfer): void
    {
        $transfer->delete();
    }
}
