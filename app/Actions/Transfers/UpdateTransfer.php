<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Data\Transfers\TransferData;
use App\Exceptions\Transfers\SameAccountTransfer;
use App\Exceptions\Transfers\TransferException;
use App\Exceptions\Transfers\UnknownTransferAccount;
use App\Models\Transfer;
use App\Models\User;

final readonly class UpdateTransfer
{
    /**
     * @throws TransferException
     */
    public function handle(User $user, Transfer $transfer, TransferData $data): Transfer
    {
        if ($data->fromAccountId === $data->toAccountId) {
            throw SameAccountTransfer::make();
        }

        $owned = $user
            ->accounts()
            ->whereKey([$data->fromAccountId, $data->toAccountId])
            ->count();

        if ($owned !== 2) {
            throw UnknownTransferAccount::make();
        }

        $transfer->update($data->toArray());

        return $transfer;
    }
}
