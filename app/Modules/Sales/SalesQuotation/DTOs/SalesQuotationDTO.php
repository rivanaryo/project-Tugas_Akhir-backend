<?php

namespace App\Modules\Sales\SalesQuotation\DTOs;

use App\Modules\Sales\SalesQuotation\Request\StoreSalesQuotationRequest;
use App\Modules\Sales\SalesQuotation\Request\UpdateSalesQuotationRequest;

class SalesQuotationDTO
{
    public function __construct(
        public readonly ?string $quotation_number = null,
        public readonly ?string $customer_id = null,
        public readonly ?string $quotation_date = null,
        public readonly ?string $valid_until = null,
        public readonly ?string $status = null,
        public readonly ?string $notes = null,
    ) {
    }

    public static function fromStoreRequest(StoreSalesQuotationRequest $request): self

    {
        $validated = $request->validated();
        return new self(
            quotation_number: $validated['quotation_number'] ?? null,
            customer_id: $validated['customer_id'] ?? null,
            quotation_date: $validated['quotation_date'] ?? null,
            valid_until: $validated['valid_until'] ?? null,
            status: $validated['status'] ?? null,
            notes: $validated['notes'] ?? null,
        );
    }

    public static function fromUpdateRequest(UpdateSalesQuotationRequest $request, string $id): self
    {
        $validated = $request->validated();
        return new self(
            quotation_number: $validated['quotation_number'] ?? null,
            customer_id: $validated['customer_id'] ?? null,
            quotation_date: $validated['quotation_date'] ?? null,
            valid_until: $validated['valid_until'] ?? null,
            status: $validated['status'] ?? null,
            notes: $validated['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'quotation_number' => $this->quotation_number,
            'customer_id' => $this->customer_id,
            'quotation_date' => $this->quotation_date,
            'valid_until' => $this->valid_until,
            'status' => $this->status,
            'notes' => $this->notes,
        ], static fn ($value): bool => $value !== null);
    }

}
