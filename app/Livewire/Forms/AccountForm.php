<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\Money;
use App\Enums\AccountType;
use App\Models\Account;
use App\Rules\MoneyString;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class AccountForm extends Form
{
    public ?int $accountId = null;

    public string $name = '';

    public string $type = '';

    public string $initialBalance = '0,00';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(AccountType::class)],
            'initialBalance' => ['required', 'string', new MoneyString(allowNegative: true, allowZero: true)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'name' => 'nome',
            'type' => 'tipo',
            'initialBalance' => 'saldo inicial',
        ];
    }

    public function setAccount(Account $account): void
    {
        $this->accountId = $account->id;
        $this->name = $account->name;
        $this->type = $account->type->value;
        $this->initialBalance = Money::fromCents($account->initial_balance)->forInput();
    }

    public function type(): AccountType
    {
        return AccountType::from($this->type);
    }

    public function initialBalanceMoney(): Money
    {
        return Money::parse($this->initialBalance);
    }
}
