<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Data\Money;
use App\Exceptions\Transfers\SameAccountTransfer;
use App\Models\Account;
use App\Models\Transfer;
use Carbon\CarbonImmutable;

final readonly class UpdateTransfer
{
    /**
     * @throws SameAccountTransfer
     */
    public function handle(
        Transfer $transfer,
        Account $from,
        Account $to,
        CarbonImmutable $date,
        Money $amount,
        ?string $notes = null,
    ): Transfer {
        if ($from->id === $to->id) {
            throw SameAccountTransfer::make();
        }

        $transfer->update([
            'from_account_id' => $from->id,
            'to_account_id' => $to->id,
            'amount' => $amount->cents,
            'date' => $date,
            'notes' => $notes,
        ]);

        return $transfer;
    }
}
