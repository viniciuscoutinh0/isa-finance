<?php

declare(strict_types=1);

use App\Actions\Transfers\DeleteTransfer;
use App\Models\Transfer;

it('hard deletes the transfer', function (): void {
    $transfer = Transfer::factory()->create();

    app(DeleteTransfer::class)->handle($transfer);

    $this->assertDatabaseMissing('transfers', ['id' => $transfer->id]);
});
