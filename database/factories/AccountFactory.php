<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
final class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->randomElement(['Nubank', 'Itaú', 'Carteira', 'Inter', 'Poupança']),
            'type' => $this->faker->randomElement(AccountType::cases()),
            'initial_balance' => $this->faker->numberBetween(0, 500_000),
            'archived_at' => null,
        ];
    }

    public function ownedBy(User $user): static
    {
        return $this->state(['user_id' => $user->id]);
    }

    public function ofType(AccountType $type): static
    {
        return $this->state(['type' => $type]);
    }

    public function creditCard(): static
    {
        return $this->state([
            'type' => AccountType::CreditCard,
            'initial_balance' => $this->faker->numberBetween(-300_000, 0),
        ]);
    }

    public function archived(): static
    {
        return $this->state(['archived_at' => now()]);
    }

    public function withInitialBalance(int $cents): static
    {
        return $this->state(['initial_balance' => $cents]);
    }
}
