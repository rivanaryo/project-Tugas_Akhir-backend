<?php 
namespace App\Modules\Purchase\PurchaseOrder\DTOs;

use App\Modules\Purchase\PurchaseOrder\Request\StorePurchaseOrderItemsRequest;
use App\Modules\Purchase\PurchaseOrder\Request\UpdatePurchaseOrderItemsRequest;

class PurchaseOrderItemsDTO
{
    public function __construct(
        public readonly ?string $purchase_order_id = null,
        public readonly ?string $pricelist_id = null,
        public readonly ?int $quantity = null,
        public readonly ?string $unit_price = null,
        public readonly ?string $total_price = null,
    ) {
    }

    public static function fromRequest(StorePurchaseOrderItemsRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            purchase_order_id: $validated['purchase_order_id'],
            pricelist_id: $validated['pricelist_id'],
            quantity: $validated['quantity'],
            unit_price: array_key_exists('unit_price', $validated) ? (string) $validated['unit_price'] : null,
            total_price: array_key_exists('total_price', $validated) ? (string) $validated['total_price'] : null,
        );
    }

    public static function fromUpdateRequest(UpdatePurchaseOrderItemsRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            purchase_order_id: $validated['purchase_order_id'] ?? null,
            pricelist_id: $validated['pricelist_id'] ?? null,
            quantity: $validated['quantity'] ?? null,
            unit_price: array_key_exists('unit_price', $validated) ? (string) $validated['unit_price'] : null,
            total_price: array_key_exists('total_price', $validated) ? (string) $validated['total_price'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'purchase_order_id' => $this->purchase_order_id,
            'pricelist_id' => $this->pricelist_id,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
        ], static fn ($value): bool => $value !== null);
    }

}
