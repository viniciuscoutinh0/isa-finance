<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
final class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => ucfirst($this->faker->unique()->words(2, true)),
            'type' => $this->faker->randomElement(CategoryType::cases()),
        ];
    }

    public function income(): static
    {
        return $this->state(['type' => CategoryType::Income]);
    }

    public function expense(): static
    {
        return $this->state(['type' => CategoryType::Expense]);
    }

    public function ownedBy(User $user): static
    {
        return $this->state(['user_id' => $user->id]);
    }
}
