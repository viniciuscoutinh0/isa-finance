<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;

final readonly class SeedDefaultCategories
{
    /**
     * @var array<value-of<CategoryType>, list<string>>
     */
    private const DEFAULTS = [
        'income' => [
            'Salário',
            'Freelance / Extra',
            'Rendimentos',
            'Presente / Outros',
        ],
        'expense' => [
            'Moradia',
            'Mercado',
            'Transporte',
            'Alimentação (fora)',
            'Saúde',
            'Lazer',
            'Educação',
            'Assinaturas',
            'Compras',
            'Contas / Serviços',
            'Outros',
        ],
    ];

    public function handle(User $user): void
    {
        $rows = [];
        $now = now();

        foreach (self::DEFAULTS as $type => $names) {
            foreach ($names as $name) {
                $rows[] = [
                    'user_id' => $user->id,
                    'name' => $name,
                    'type' => $type,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        Category::insert($rows);
    }
}
