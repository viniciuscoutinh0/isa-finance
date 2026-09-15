<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Data\Transfers\TransferData;
use App\Exceptions\Transfers\SameAccountTransfer;
use App\Exceptions\Transfers\TransferException;
use App\Exceptions\Transfers\UnknownTransferAccount;
use App\Models\Transfer;
use App\Models\User;

final readonly class CreateTransfer
{
    /**
     * @throws TransferException
     */
    public function handle(User $user, TransferData $data): Transfer
    {
        if ($data->fromAccountId === $data->toAccountId) {
            throw SameAccountTransfer::make();
        }

        $this->assertOwnsAccounts($user, $data);

        return $user->transfers()->create($data->toArray());
    }

    /**
     * @throws TransferException
     */
    private function assertOwnsAccounts(User $user, TransferData $data): void
    {
        $owned = $user
            ->accounts()
            ->whereKey([$data->fromAccountId, $data->toAccountId])
            ->count();

        if ($owned !== 2) {
            throw UnknownTransferAccount::make();
        }
    }
}
