<?php 
namespace App\Modules\Production\PurchaseOrder\DTOs;
use App\Modules\Production\PurchaseOrder\Request\StorePurchaseOrderRequest;
use App\Modules\Production\PurchaseOrder\Request\UpdatePurchaseOrderRequest;

class PurchaseOrderDTO
{
    public function __construct(
        public readonly ?string $po_number = null,
        public readonly ?string $vendor_id = null,
        public readonly ?string $status = null,
        public readonly ?string $po_date = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'po_number' => $this->po_number,
            'vendor_id' => $this->vendor_id,
            'status' => $this->status,
            'po_date' => $this->po_date,
        ], static fn ($value): bool => $value !== null);
    }

    public static function fromUpdateRequest(UpdatePurchaseOrderRequest $request): self
    {
        $validated = $request->validated();
        return new self(
            po_number: $validated['po_number'] ?? null,
            vendor_id: $validated['vendor_id'] ?? null,
            status: $validated['status'] ?? null,
            po_date: $validated['po_date'] ?? null,
        );
    }

    public static function fromRequest(StorePurchaseOrderRequest $request): self
    {
        $validated = $request->validated();
        return new self(
            po_number: $validated['po_number'],
            vendor_id: $validated['vendor_id'],
            status: $validated['status'],
            po_date: $validated['po_date'],
        );
    }




}
