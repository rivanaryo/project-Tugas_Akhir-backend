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
        public readonly ?string $warehouse_id = null,
        public readonly int $stock_level = 0,
        public readonly int $min_stock_level = 0,
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
            warehouse_id: $validated['warehouse_id'] ?? null,
            stock_level: $validated['stock_level'] ?? 0,
            min_stock_level: $validated['min_stock_level'] ?? 0,
            is_active: $validated['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'product_name' => $this->product_name,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
            'warehouse_id' => $this->warehouse_id,
            'stock_level' => $this->stock_level,
            'min_stock_level' => $this->min_stock_level,
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
            warehouse_id: $validated['warehouse_id'] ?? null,
            stock_level: $validated['stock_level'] ?? 0,
            min_stock_level: $validated['min_stock_level'] ?? 0,
            is_active: $validated['is_active'] ?? true,
        );
    }
}
