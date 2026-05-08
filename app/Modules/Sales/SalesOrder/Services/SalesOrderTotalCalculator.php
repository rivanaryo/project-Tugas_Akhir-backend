<?php

namespace App\Modules\Sales\SalesOrder\Services;

use App\Modules\Sales\SalesOrder\Reference\Models\SalesOrderItems;
use Illuminate\Support\Facades\DB;

class SalesOrderTotalCalculator
{
    /**
     * Calculate total amount for a sales order based on its items
     */
    public function calculateTotal(string $salesOrderId): float
    {
        return SalesOrderItems::where('sales_order_id', $salesOrderId)
            ->sum('total_price');
    }

    /**
     * Update the total amount in the sales order.
     *
     * The sales order table no longer stores a total_amount column,
     * so this method only ensures the total is calculated when needed.
     */
    public function updateOrderTotal(string $salesOrderId): bool
    {
        $this->calculateTotal($salesOrderId);

        return true;
    }

    /**
     * Calculate the total amount for a sales order.
     *
     * This method no longer persists total_amount to the sales_order table.
     */
    public function calculateAndUpdateTotal(string $salesOrderId): float
    {
        return $this->calculateTotal($salesOrderId);
    }

    /**
     * Apply total price calculation to payload data (referenced from PurchaseOrderCalculator)
     */
    public function applyTotalPrice(array $payload, ?SalesOrderItems $existing = null): array
    {
        $quantity = $payload['quantity'] ?? ($existing?->quantity);
        $unitPrice = $payload['unit_price'] ?? ($existing?->unit_price);

        if ($quantity === null || $unitPrice === null) {
            return $payload;
        }

        $payload['total_price'] = $this->calculateLineTotal((int) $quantity, (string) $unitPrice);

        return $payload;
    }

    /**
     * Calculate line total with precise decimal handling (referenced from PurchaseOrderCalculator)
     */
    public function calculateLineTotal(int $quantity, string $unitPrice): string
    {
        if ($quantity <= 0) {
            return '0.00';
        }

        $unitPrice = trim($unitPrice);

        if ($unitPrice === '') {
            return '0.00';
        }

        if (function_exists('bcmul')) {
            return (string) bcmul($unitPrice, (string) $quantity, 2);
        }

        $total = round(((float) $unitPrice) * $quantity, 2);

        return number_format($total, 2, '.', '');
    }

    /**
     * Recalculate total price for a single item
     */
    public function calculateItemTotal(int $quantity, float $unitPrice): float
    {
        return $quantity * $unitPrice;
    }

    /**
     * Update total price for a specific item
     */
    public function updateItemTotal(string $itemId, int $quantity, float $unitPrice): bool
    {
        $totalPrice = $this->calculateItemTotal($quantity, $unitPrice);

        return SalesOrderItems::where('id', $itemId)
            ->update(['total_price' => $totalPrice]);
    }
}