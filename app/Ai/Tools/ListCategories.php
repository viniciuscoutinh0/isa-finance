<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\User;
use App\Queries\Categories\CategoriesQuery;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

final class ListCategories implements Tool
{
    public function __construct(private readonly User $user) {}

    public function description(): string
    {
        return 'List the current user\'s categories with their type (entrada = income, saída = expense).';
    }

    public function handle(Request $request): string
    {
        $categories = app(CategoriesQuery::class)->handle($this->user);

        return json_encode([
            'categories' => $categories->map(fn ($category): array => [
                'name' => $category->name,
                'type' => $category->type->label(),
            ])->all(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
