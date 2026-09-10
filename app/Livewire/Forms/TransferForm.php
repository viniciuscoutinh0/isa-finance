<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\Money;
use App\Models\Transfer;
use App\Rules\MoneyString;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class TransferForm extends Form
{
    public ?int $transferId = null;

    public string $fromAccountId = '';

    public string $toAccountId = '';

    public string $date = '';

    public string $amount = '';

    public string $notes = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $ownAccount = Rule::exists('accounts', 'id')->where('user_id', Auth::id());

        return [
            'fromAccountId' => ['required', $ownAccount],
            'toAccountId' => ['required', 'different:fromAccountId', $ownAccount],
            'date' => ['required', 'date'],
            'amount' => ['required', 'string', new MoneyString(allowNegative: false, allowZero: false)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'fromAccountId' => 'conta de origem',
            'toAccountId' => 'conta de destino',
            'date' => 'data',
            'amount' => 'valor',
            'notes' => 'observação',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'toAccountId.different' => 'A conta de destino deve ser diferente da conta de origem.',
        ];
    }

    public function setTransfer(Transfer $transfer): void
    {
        $this->transferId = $transfer->id;
        $this->fromAccountId = (string) $transfer->from_account_id;
        $this->toAccountId = (string) $transfer->to_account_id;
        $this->date = $transfer->date->toDateString();
        $this->amount = Money::fromCents($transfer->amount)->forInput();
        $this->notes = (string) $transfer->notes;
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
