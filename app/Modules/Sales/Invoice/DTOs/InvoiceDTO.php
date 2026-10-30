<?php

namespace App\Modules\Sales\Invoice\DTOs;

use App\Modules\Sales\Invoice\Request\StoreInvoiceRequest;
use App\Modules\Sales\Invoice\Request\UpdateInvoiceRequest;

class InvoiceDTO
{
    public function __construct(
        public readonly ?string $invoice_number = null,
        public readonly ?string $invoice_date = null,
        public readonly ?string $customer_id = null,
        public readonly ?string $sales_order_id = null,
        public readonly ?string $type = null,
        public readonly ?string $status = null,
        public readonly ?array $items = null,
    ) {}

    public static function fromRequest(StoreInvoiceRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            invoice_number: $validated['invoice_number'] ?? null,
            invoice_date: $validated['invoice_date'] ?? null,
            customer_id: $validated['customer_id'] ?? null,
            sales_order_id: $validated['sales_order_id'] ?? null,
            type: $validated['type'] ?? null,
            status: $validated['status'] ?? null,
            items: $validated['items'] ?? null,
        );
    }

    public static function fromUpdateRequest(UpdateInvoiceRequest $request, string $id): self
    {
        $validated = $request->validated();

        return new self(
            invoice_number: $validated['invoice_number'] ?? null,
            invoice_date: $validated['invoice_date'] ?? null,
            customer_id: $validated['customer_id'] ?? null,
            sales_order_id: $validated['sales_order_id'] ?? null,
            type: $validated['type'] ?? null,
            status: $validated['status'] ?? null,
            items: $validated['items'] ?? null,
        );
    }

    public function toArray(): array
    {
        $result = array_filter([
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date,
            'customer_id' => $this->customer_id,
            'sales_order_id' => $this->sales_order_id,
            'type' => $this->type,
            'status' => $this->status,
        ], static fn ($value): bool => $value !== null);

        if ($this->items !== null) {
            $result['items'] = $this->items;
        }

        return $result;
    }
}
