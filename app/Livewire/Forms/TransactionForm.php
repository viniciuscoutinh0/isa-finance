<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\Money;
use App\Models\Transaction;
use App\Models\User;
use App\Rules\Transactions\TransactionRules;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Form;

final class TransactionForm extends Form
{
    #[Locked]
    public ?Transaction $transaction = null;

    public ?int $account_id = null;

    public ?int $category_id = null;

    public ?string $date = null;

    public string $description = '';

    public string $amount = '';

    public ?string $notes = null;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = Auth::user();

        return TransactionRules::for($user);
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return TransactionRules::attributes();
    }

    public function setTransaction(Transaction $transaction): void
    {
        $this->transaction = $transaction;

        $this->account_id = $transaction->account_id;
        $this->category_id = $transaction->category_id;
        $this->date = $transaction->date->toDateString();
        $this->description = $transaction->description;
        $this->amount = Money::fromCents($transaction->amount)->forInput();
        $this->notes = $transaction->notes;
    }
}
