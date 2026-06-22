<?php

namespace App\Modules\Inventory\StockMovement\DTOs;

use App\Modules\Inventory\StockMovement\Request\StoreStockMovementRequest;
use App\Modules\Inventory\StockMovement\Request\UpdateStockMovementRequest;

class StockMovementDTO
{
    public function __construct(
        public readonly string $productId,
        public readonly string $warehouseId,
        public readonly string $type,
        public readonly int $quantity,
        public readonly string $referenceNumber,
        public readonly string $referenceableId,
        public readonly string $referenceableType,
        public readonly ?string $description = null
    ) {}

    public static function fromRequest(StoreStockMovementRequest $request): self
    {
        return new self(
            productId: $request->validated('m_products_id'),
            warehouseId: $request->validated('m_warehouses_id'),
            type: $request->validated('type'),
            quantity: (int) $request->validated('quantity'),
            referenceNumber: $request->validated('reference_number'),
            referenceableId: $request->validated('referenceable_id'),
            referenceableType: $request->validated('referenceable_type'),
            description: $request->validated('description')
        );
    }

    public static function fromUpdateRequest(UpdateStockMovementRequest $request): self
    {
        return new self(
            productId: $request->validated('m_products_id'),
            warehouseId: $request->validated('m_warehouses_id'),
            type: $request->validated('type'),
            quantity: (int) $request->validated('quantity'),
            referenceNumber: $request->validated('reference_number'),
            referenceableId: $request->validated('referenceable_id'),
            referenceableType: $request->validated('referenceable_type'),
            description: $request->validated('description')
        );
    }

    public function toArray(): array
    {
        return [
            'm_products_id'        => $this->productId,
            'm_warehouses_id'      => $this->warehouseId,
            'type'                 => $this->type,
            'quantity'             => $this->quantity,
            'reference_number'     => $this->referenceNumber,
            'referenceable_id'     => $this->referenceableId,
            'referenceable_type'   => $this->referenceableType,
            'description'        => $this->description,
        ];
    }
}