<?php 
namespace App\Modules\Sales\SalesDelivery\DTOs;

use App\Modules\Sales\SalesDelivery\Request\StoreSalesDeliveryRequest;
use App\Modules\Sales\SalesDelivery\Request\UpdateSalesDeliveryRequest;

class SalesDeliveryDTO
{
    public function __construct(
        public readonly ?string $delivery_number = null,
        public readonly ?string $customer_id = null,
        public readonly ?string $sales_order_id = null,
        public readonly ?string $delivery_date = null,
        public readonly ?string $status = null,
    ) {
    }

    public static function fromStoreRequest(StoreSalesDeliveryRequest $request): self
    {
        $validated = $request->validated();
        return new self(
            delivery_number: $validated['delivery_number'] ?? null,
            customer_id: $validated['customer_id'] ?? null,
            sales_order_id: $validated['sales_order_id'] ?? null,
            delivery_date: $validated['delivery_date'] ?? null,
            status: $validated['status'] ?? null,
        );
    }

    public static function fromUpdateRequest(UpdateSalesDeliveryRequest $request, string $id): self
    {
        $validated = $request->validated();
        return new self(
            delivery_number: $validated['delivery_number'] ?? null,
            customer_id: $validated['customer_id'] ?? null,
            sales_order_id: $validated['sales_order_id'] ?? null,
            delivery_date: $validated['delivery_date'] ?? null,
            status: $validated['status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'delivery_number' => $this->delivery_number,
            'customer_id' => $this->customer_id,
            'sales_order_id' => $this->sales_order_id,
            'delivery_date' => $this->delivery_date,
            'status' => $this->status,
        ], static fn ($value): bool => $value !== null);
    }
}