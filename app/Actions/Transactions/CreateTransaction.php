<?php

declare(strict_types=1);

namespace App\Actions\Transactions;

use App\Data\Transactions\TransactionData;
use App\Exceptions\Transactions\TransactionException;
use App\Models\Transaction;
use App\Models\User;

final readonly class CreateTransaction
{
    public function handle(User $user, TransactionData $data): Transaction
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

        return $user->transactions()->create([
            'account_id' => $data->accountId,
            'category_id' => $data->categoryId,
            'date' => $data->date,
            'description' => $data->description,
            'amount' => $data->amount->cents,
            'notes' => $data->notes,
        ]);
    }
}
