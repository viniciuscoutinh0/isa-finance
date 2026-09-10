<?php

declare(strict_types=1);

use App\Actions\Transactions\DeleteTransaction;
use App\Models\Transaction;

it('hard deletes the transaction', function (): void {
    $transaction = Transaction::factory()->create();

    app(DeleteTransaction::class)->handle($transaction);

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});
