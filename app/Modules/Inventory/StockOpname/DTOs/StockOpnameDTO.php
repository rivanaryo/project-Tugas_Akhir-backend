<?php

namespace App\Modules\Inventory\StockOpname\DTOs;

use App\Modules\Inventory\StockOpname\Request\StoreStockOpnameRequest;
use App\Modules\Inventory\StockOpname\Request\UpdateStockOpnameRequest;

class StockOpnameDTO
{
    public function __construct(
        public readonly ?string $opnameNumber,
        public readonly ?string $warehouseId,
        public readonly ?string $opnameDate,
        public readonly ?string $status,
        public readonly ?string $notes,
        public readonly ?array $items,
    ) {}

    public static function fromRequest(StoreStockOpnameRequest $request): self
    {
        return new self(
            opnameNumber: $request->validated('opname_number'),
            warehouseId: $request->validated('warehouse_id'),
            opnameDate: $request->validated('opname_date'),
            status: $request->validated('status'),
            notes: $request->validated('notes'),
            items: $request->validated('items'),
        );
    }

    public static function fromUpdateRequest(UpdateStockOpnameRequest $request): self
    {
        return new self(
            opnameNumber: $request->validated('opname_number'),
            warehouseId: $request->validated('warehouse_id'),
            opnameDate: $request->validated('opname_date'),
            status: $request->validated('status'),
            notes: $request->validated('notes'),
            items: $request->validated('items'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'opname_number' => $this->opnameNumber,
            'warehouse_id' => $this->warehouseId,
            'opname_date' => $this->opnameDate,
            'status' => $this->status,
            'notes' => $this->notes,
            'items' => $this->items,
        ]);
    }
}
