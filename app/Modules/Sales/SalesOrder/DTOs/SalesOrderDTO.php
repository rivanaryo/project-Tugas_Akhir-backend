<?php

namespace App\Modules\Sales\SalesOrder\DTOs;

use App\Modules\Sales\SalesOrder\Request\StoreSalesOrderRequest;
use App\Modules\Sales\SalesOrder\Request\UpdateSalesOrderRequest;

class SalesOrderDTO
{
    public function __construct(
        public readonly ?string $order_number,
        public readonly string $customer_id,
        public readonly ?string $order_date,
        public readonly int $status,
    ) {
    }

    public static function fromStoreRequest(StoreSalesOrderRequest $request): self

    {
        $validated = $request->validated();
        return new self(
            order_number: $validated['order_number'],
            customer_id: $validated['customer_id'],
            order_date: $validated['order_date'],
            status: $validated['status'],
        );
    }

    public static function fromUpdateRequest(UpdateSalesOrderRequest $request, string $id): self
    {
        $validated = $request->validated();
        return new self(
            order_number: $validated['order_number'] ?? null,
            customer_id: $validated['customer_id'],
            order_date: $validated['order_date'],
            status: $validated['status'],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'order_date' => $this->order_date,
            'status' => $this->status,
        ], static fn ($value): bool => $value !== null);
    }




}
