<?php

declare(strict_types=1);

namespace App\Actions\Transactions;

use App\Data\Transactions\TransactionData;
use App\Exceptions\Transactions\TransactionException;
use App\Models\Transaction;
use App\Models\User;

final readonly class UpdateTransaction
{
    public function handle(User $user, Transaction $transaction, TransactionData $data): void
    {
        $hasAccount = $user
            ->accounts()
            ->whereKey($data->accountId)
            ->exists();

        if (! $hasAccount) {
            throw TransactionException::invalidAccount();
        }

        $hasCategory = $user
            ->categories()
            ->whereKey($data->categoryId)
            ->exists();

        if (! $hasCategory) {
            throw TransactionException::invalidCategory();
        }

        $transaction->update($data->toArray());
    }
}
