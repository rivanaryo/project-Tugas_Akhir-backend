<?php
namespace App\Modules\Sales\SalesDelivery\DTOs;

use App\Modules\Sales\SalesDelivery\Request\StoreSalesDeliveryItemsRequest;
use App\Modules\Sales\SalesDelivery\Request\UpdateSalesDeliveryItemsRequest;

class SalesDeliveryItemsDTO
{
    public function __construct(
        public readonly string $sales_delivery_id,
        public readonly string $sales_order_item_id,
        public readonly int $qty_delivered,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'sales_delivery_id' => $this->sales_delivery_id,
            'sales_order_item_id' => $this->sales_order_item_id,
            'qty_delivered' => $this->qty_delivered,
        ], static fn ($value): bool => $value !== null);
    }

    public static function fromStoreRequest(StoreSalesDeliveryItemsRequest $request): self
    {
        $validated = $request->validated();
        return new self(
            sales_delivery_id: $validated['sales_delivery_id'],
            sales_order_item_id: $validated['sales_order_item_id'],
            qty_delivered: (int) $validated['qty_delivered'],
        );
    }

    public static function fromUpdateRequest(UpdateSalesDeliveryItemsRequest $request): self
    {
        $validated = $request->validated();
        return new self(
            sales_delivery_id: $validated['sales_delivery_id'],
            sales_order_item_id: $validated['sales_order_item_id'],
            qty_delivered: (int) $validated['qty_delivered'],
        );
    }
}