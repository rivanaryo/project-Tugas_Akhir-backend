<?php 
namespace App\Modules\Sales\Invoice\DTOs;
use App\Modules\Sales\Invoice\Models\InvoiceItem;
use App\Modules\Sales\Invoice\Request\StoreInvoiceItemRequest;
use App\Modules\Sales\Invoice\Request\UpdateInvoiceItemRequest;

class InvoiceItemDTO
{
    public function __construct(
        public readonly string $invoice_id,
        public readonly string $sales_order_item_id,
        public readonly int $qty_invoice,
        public readonly float $unit_price,
        public readonly ?float $tax_rate = null,
    ) {}

    public static function fromRequest(StoreInvoiceItemRequest $request, string $invoiceId): self
    {
        $validated = $request->validated();
        return new self(
            invoice_id: $invoiceId,
            sales_order_item_id: $validated['sales_order_item_id'],
            qty_invoice: $validated['qty_invoice'],
            unit_price: $validated['unit_price'],
            tax_rate: $validated['tax_rate'] ?? null,
        );
    }

    public static function fromUpdateRequest(UpdateInvoiceItemRequest $request, string $id): self
    {
        $validated = $request->validated();
        return new self(
            invoice_id: $validated['invoice_id'],
            sales_order_item_id: $validated['sales_order_item_id'],
            qty_invoice: $validated['qty_invoice'],
            unit_price: $validated['unit_price'],
            tax_rate: $validated['tax_rate'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'invoice_id' => $this->invoice_id,
            'sales_order_item_id' => $this->sales_order_item_id,
            'qty_invoice' => $this->qty_invoice,
            'unit_price' => $this->unit_price,
            'tax_rate' => $this->tax_rate,
        ], static fn ($value): bool => $value !== null);
    }
}