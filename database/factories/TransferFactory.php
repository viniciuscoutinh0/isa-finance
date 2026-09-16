<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transfer>
 */
final class TransferFactory extends Factory
{
    protected $model = Transfer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'user_id' => $user->id,
            'from_account_id' => Account::factory()->ownedBy($user),
            'to_account_id' => Account::factory()->ownedBy($user),
            'amount' => $this->faker->numberBetween(1_000, 300_000),
            'date' => today()->subDays($this->faker->numberBetween(0, 60))->toDateString(),
            'notes' => null,
        ];
    }

    public function between(Account $from, Account $to): static
    {
        return $this->state([
            'user_id' => $from->user_id,
            'from_account_id' => $from->id,
            'to_account_id' => $to->id,
        ]);
    }

    public function ownedBy(User $user): static
    {
        return $this->state([
            'user_id' => $user->id,
            'from_account_id' => Account::factory()->ownedBy($user),
            'to_account_id' => Account::factory()->ownedBy($user),
        ]);
    }

    public function on(string $date): static
    {
        return $this->state(['date' => $date]);
    }

    public function amountCents(int $cents): static
    {
        return $this->state(['amount' => $cents]);
    }
}
