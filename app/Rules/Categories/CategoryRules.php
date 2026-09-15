<?php

declare(strict_types=1);

namespace App\Rules\Categories;

use App\Enums\CategoryType;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * Validation rules for a category payload.
 *
 * A name is unique per user and type, so the rule needs the type being
 * submitted, and the category being edited to exclude itself.
 */
final class CategoryRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function for(User $user, ?string $type, ?int $ignoreId = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where('user_id', $user->id)
                    ->where('type', $type)
                    ->ignore($ignoreId),
            ],
            'type' => [
                'required',
                Rule::enum(CategoryType::class),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributes(): array
    {
        return [
            'name' => 'nome',
            'type' => 'tipo',
        ];
    }
}
