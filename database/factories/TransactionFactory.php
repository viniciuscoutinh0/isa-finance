<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 *
 * The default state builds a consistent user + account + category trio.
 * Use forAccount()/forCategory() to pin them; both also align user_id.
 */
final class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'user_id' => $user->id,
            'account_id' => Account::factory()->ownedBy($user),
            'category_id' => Category::factory()->ownedBy($user),
            'date' => today()->subDays($this->faker->numberBetween(0, 60))->toDateString(),
            'description' => ucfirst($this->faker->words(3, true)),
            'amount' => $this->faker->numberBetween(500, 500_000),
            'notes' => null,
        ];
    }

    public function forAccount(Account $account): static
    {
        return $this->state([
            'account_id' => $account->id,
            'user_id' => $account->user_id,
        ]);
    }

    public function forCategory(Category $category): static
    {
        return $this->state([
            'category_id' => $category->id,
            'user_id' => $category->user_id,
        ]);
    }

    public function ownedBy(User $user): static
    {
        return $this->state([
            'user_id' => $user->id,
            'account_id' => Account::factory()->ownedBy($user),
            'category_id' => Category::factory()->ownedBy($user),
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
