<?php

namespace App\Modules\Production\PurchaseOrder\DTOs;

use App\Modules\Production\PurchaseOrder\Request\StorePurchaseOrderItemsRequest;
use App\Modules\Production\PurchaseOrder\Request\UpdatePurchaseOrderItemsRequest;

class PurchaseOrderItemsDTO
{
    public function __construct(
        public readonly ?string $purchase_order_id = null,
        public readonly ?string $product_id = null,
        public readonly ?string $unit_id = null,
        public readonly ?int $quantity = null,
        public readonly ?string $unit_price = null,
        public readonly ?string $tax_rate = null,
    ) {}

    public static function fromRequest(StorePurchaseOrderItemsRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            purchase_order_id: $validated['purchase_order_id'],
            product_id: $validated['product_id'],
            unit_id: $validated['unit_id'],
            quantity: $validated['quantity'],
            unit_price: array_key_exists('unit_price', $validated) ? (string) $validated['unit_price'] : null,
            tax_rate: array_key_exists('tax_rate', $validated) ? (string) $validated['tax_rate'] : null,
        );
    }

    public static function fromUpdateRequest(UpdatePurchaseOrderItemsRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            purchase_order_id: $validated['purchase_order_id'] ?? null,
            product_id: $validated['product_id'] ?? null,
            unit_id: $validated['unit_id'] ?? null,
            quantity: $validated['quantity'] ?? null,
            unit_price: array_key_exists('unit_price', $validated) ? (string) $validated['unit_price'] : null,
            tax_rate: array_key_exists('tax_rate', $validated) ? (string) $validated['tax_rate'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'purchase_order_id' => $this->purchase_order_id,
            'product_id' => $this->product_id,
            'unit_id' => $this->unit_id,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'tax_rate' => $this->tax_rate,
        ], static fn ($value): bool => $value !== null);
    }
}
