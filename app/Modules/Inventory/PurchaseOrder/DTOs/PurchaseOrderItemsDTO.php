<?php
namespace App\Modules\Inventory\PurchaseOrder\DTOs;

use App\Modules\Inventory\PurchaseOrder\Request\StorePurchaseOrderItemsRequest;
use App\Modules\Inventory\PurchaseOrder\Request\UpdatePurchaseOrderItemsRequest;

class PurchaseOrderItemsDTO
{
    public function __construct(
        public readonly ?string $purchase_order_id = null,
        public readonly ?string $pricelist_id = null,
        public readonly ?int $quantity = null,
        public readonly ?string $tax_rate = null,
    ) {
    }

    public static function fromRequest(StorePurchaseOrderItemsRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            purchase_order_id: $validated['purchase_order_id'],
            pricelist_id: $validated['pricelist_id'],
            quantity: $validated['quantity'],
            tax_rate: array_key_exists('tax_rate', $validated) ? (string) $validated['tax_rate'] : null,
        );
    }

    public static function fromUpdateRequest(UpdatePurchaseOrderItemsRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            purchase_order_id: $validated['purchase_order_id'] ?? null,
            pricelist_id: $validated['pricelist_id'] ?? null,
            quantity: $validated['quantity'] ?? null,
            tax_rate: array_key_exists('tax_rate', $validated) ? (string) $validated['tax_rate'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'purchase_order_id' => $this->purchase_order_id,
            'pricelist_id' => $this->pricelist_id,
            'quantity' => $this->quantity,
            'tax_rate' => $this->tax_rate,
        ], static fn ($value): bool => $value !== null);
    }

}
