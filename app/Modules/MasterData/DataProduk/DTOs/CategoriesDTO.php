<?php 

namespace App\Modules\MasterData\DataProduk\DTOs;

use App\Modules\MasterData\DataProduk\Request\Categories\StoreCategoriesRequest;
use App\Modules\MasterData\DataProduk\Request\Categories\UpdateCategoriesRequest;

class CategoriesDTO
{
    public function __construct(
        public readonly string $category_name,
        public readonly bool $is_active = true,
    ) {
    }

    public static function fromRequest(StoreCategoriesRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            category_name: $validated['category_name'],
            is_active: $validated['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'category_name' => $this->category_name,
            'is_active' => $this->is_active,
        ];
    }

    public static function fromUpdateRequest(UpdateCategoriesRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            category_name: $validated['category_name'],
            is_active: $validated['is_active'] ?? true,
        );
    }


}