<?php

namespace App\Modules\MasterData\DataProduk\DTOs;

use App\Modules\MasterData\DataProduk\Request\Product\StoreProductRequest;
use App\Modules\MasterData\DataProduk\Request\Product\UpdateProductRequest;

class ProductDTO
{
    public function __construct(
        public readonly string $product_name,
        public readonly string $category_id,
        public readonly string $unit_id,
        public readonly bool $is_active = true,
    ) {
    }

    public static function fromRequest(StoreProductRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            product_name: $validated['product_name'],
            category_id: $validated['category_id'],
            unit_id: $validated['unit_id'],
            is_active: $validated['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'product_name' => $this->product_name,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
            'is_active' => $this->is_active,
        ];
    }
    
    public static function fromUpdateRequest(UpdateProductRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            product_name: $validated['product_name'],
            category_id: $validated['category_id'],
            unit_id: $validated['unit_id'],
            is_active: $validated['is_active'] ?? true,
        );
    }
}
