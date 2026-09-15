<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\Money;
use App\Models\Transfer;
use App\Models\User;
use App\Rules\Transfers\TransferRules;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Form;

final class TransferForm extends Form
{
    #[Locked]
    public ?Transfer $transfer = null;

    public ?int $from_account_id = null;

    public ?int $to_account_id = null;

    public ?string $date = null;

    public string $amount = '';

    public ?string $notes = null;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = Auth::user();

        return TransferRules::for($user);
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return TransferRules::attributes();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return TransferRules::messages();
    }

    public function setTransfer(Transfer $transfer): void
    {
        $this->transfer = $transfer;

        $this->from_account_id = $transfer->from_account_id;
        $this->to_account_id = $transfer->to_account_id;
        $this->date = $transfer->date->toDateString();
        $this->amount = Money::fromCents($transfer->amount)->forInput();
        $this->notes = $transfer->notes;
    }
}
