<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\Money;
use App\Enums\AccountType;
use App\Models\Account;
use App\Rules\Accounts\AccountRules;
use Livewire\Attributes\Locked;
use Livewire\Form;

final class AccountForm extends Form
{
    #[Locked]
    public ?Account $account = null;

    public string $name = '';

    public string $type = AccountType::Checking->value;

    public string $initial_balance = '0,00';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return AccountRules::for();
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return AccountRules::attributes();
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;

        $this->name = $account->name;
        $this->type = $account->type->value;
        $this->initial_balance = Money::fromCents($account->initial_balance)->forInput();
    }
}
