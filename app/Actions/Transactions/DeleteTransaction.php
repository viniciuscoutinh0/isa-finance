<?php

declare(strict_types=1);

namespace App\Actions\Transactions;

use App\Models\Transaction;

final readonly class DeleteTransaction
{
    public function handle(Transaction $transaction): void
    {
        $transaction->delete();
    }
}
