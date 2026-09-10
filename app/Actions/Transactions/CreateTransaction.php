<?php

declare(strict_types=1);

namespace App\Actions\Transactions;

use App\Data\Money;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class CreateTransaction
{
    public function handle(
        User $user,
        Account $account,
        Category $category,
        CarbonImmutable $date,
        string $description,
        Money $amount,
        ?string $notes = null,
    ): Transaction {
        return $user->transactions()->create([
            'account_id' => $account->id,
            'category_id' => $category->id,
            'date' => $date,
            'description' => $description,
            'amount' => $amount->cents,
            'notes' => $notes,
        ]);
    }
}
