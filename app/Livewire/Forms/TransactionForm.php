<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\Money;
use App\Models\Transaction;
use App\Rules\MoneyString;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

final class TransactionForm extends Form
{
    #[Locked]
    public ?Transaction $transaction = null;

    public ?int $accountId = null;

    public ?int $categoryId = null;

    public ?string $date = null;

    public string $description = '';

    public string $amount = '';

    public ?string $notes = null;

    public function rules(): array
    {
        return [
            'accountId' => [
                'required',
                Rule::exists('accounts', 'id')->where('user_id', Auth::id())->whereNull('archived_at'),
            ],
            'categoryId' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', Auth::id()),
            ],
            'date' => [
                'required',
                'date',
                'date_format:Y-m-d',
            ],
            'description' => [
                'required',
                'string',
                'max:255',
            ],
            'amount' => [
                'required',
                'string',
                new MoneyString(allowNegative: false, allowZero: false),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'accountId' => 'conta',
            'categoryId' => 'categoria',
            'date' => 'data',
            'description' => 'descrição',
            'amount' => 'valor',
            'notes' => 'observação',
        ];
    }

    public function setTransaction(Transaction $transaction): void
    {
        $this->transaction = $transaction;

        $this->accountId = $transaction->account_id;
        $this->categoryId = $transaction->category_id;
        $this->date = $transaction->date->toDateString();
        $this->description = $transaction->description;
        $this->amount = Money::fromCents($transaction->amount)->forInput();
        $this->notes = $transaction->notes;
    }

    public function amountMoney(): Money
    {
        return Money::parse($this->amount);
    }

    public function dateValue(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->date);
    }

    public function notesValue(): ?string
    {
        return trim($this->notes) === '' ? null : trim($this->notes);
    }
}
