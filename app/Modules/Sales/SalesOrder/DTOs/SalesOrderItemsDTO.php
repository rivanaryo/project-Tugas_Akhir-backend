<?php 

namespace App\Modules\Sales\SalesOrder\DTOs;

use App\Modules\Sales\SalesOrder\Request\StoreSalesOrderItemsRequest;
use App\Modules\Sales\SalesOrder\Request\UpdateSalesOrderItemsRequest;
use App\Modules\Sales\SalesOrder\Services\SalesOrderItemCalculator;

/**
 * DTO untuk Sales Order Items dengan perhitungan otomatis tax dan total.
 */
class SalesOrderItemsDTO
{
    public function __construct(
        public readonly string $product_id,
        public readonly ?string $size,
        public readonly int $quantity,
        public readonly float $unit_price,
        public readonly float $tax_rate,
        public readonly float $tax_amount,
        public readonly float $total_price,
    ) {
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->product_id,
            'size' => $this->size,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'total_price' => $this->total_price,
        ];
    }

    /**
     * Buat dari store request dengan perhitungan otomatis
     */
    public static function fromStoreRequest(StoreSalesOrderItemsRequest $request): self
    {
        $validated = $request->validated();
        $calculator = new SalesOrderItemCalculator();

        $quantity = (int) $validated['quantity'];
        $unitPrice = (float) $validated['unit_price'];
        $taxRate = (float) ($validated['tax_rate'] ?? 0);

        $calculations = $calculator->calculate($quantity, $unitPrice, $taxRate);

        return new self(
            product_id: $validated['product_id'],
            size: $validated['size'] ?? null,
            quantity: $quantity,
            unit_price: $unitPrice,
            tax_rate: $taxRate,
            tax_amount: $calculations['tax_amount'],
            total_price: $calculations['total_price'],
        );
    }

    /**
     * Buat dari update request dengan perhitungan otomatis
     */
    public static function fromUpdateRequest(UpdateSalesOrderItemsRequest $request): self
    {
        $validated = $request->validated();
        $calculator = new SalesOrderItemCalculator();

        $quantity = (int) $validated['quantity'];
        $unitPrice = (float) $validated['unit_price'];
        $taxRate = (float) ($validated['tax_rate'] ?? 0);

        $calculations = $calculator->calculate($quantity, $unitPrice, $taxRate);

        return new self(
            product_id: $validated['product_id'],
            size: $validated['size'] ?? null,
            quantity: $quantity,
            unit_price: $unitPrice,
            tax_rate: $taxRate,
            tax_amount: $calculations['tax_amount'],
            total_price: $calculations['total_price'],
        );
    }

    


}