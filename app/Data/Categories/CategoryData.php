<?php

declare(strict_types=1);

namespace App\Data\Categories;

use App\Enums\CategoryType;

final readonly class CategoryData
{
    public function __construct(
        public string $name,
        public CategoryType $type,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: trim($data['name']),
            type: CategoryType::from($data['type']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
        ];
    }
}
