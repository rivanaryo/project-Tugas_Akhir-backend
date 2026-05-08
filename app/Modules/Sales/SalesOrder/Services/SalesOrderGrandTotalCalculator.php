<?php

namespace App\Modules\Sales\SalesOrder\Services;

use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk menghitung totalan sales order.
 * Menghitung: subtotal, total tax, dan grand total dari items.
 */
class SalesOrderGrandTotalCalculator
{
    /**
     * Hitung subtotal dari semua items (tanpa tax)
     */
    public function calculateSubtotal(string $salesOrderId): float
    {
        return (float) DB::table('sales_order_items')
            ->where('sales_order_id', $salesOrderId)
            ->sum(DB::raw('quantity * unit_price'));
    }

    /**
     * Hitung total tax dari semua items
     */
    public function calculateTotalTax(string $salesOrderId): float
    {
        return (float) DB::table('sales_order_items')
            ->where('sales_order_id', $salesOrderId)
            ->sum('tax_amount');
    }

    /**
     * Hitung grand total (subtotal + total tax)
     */
    public function calculateGrandTotal(float $subtotal, float $totalTax): float
    {
        return round($subtotal + $totalTax, 2);
    }

    /**
     * Update totalan di sales order
     */
    public function updateOrderTotals(string $salesOrderId): void
    {
        $subtotal = $this->calculateSubtotal($salesOrderId);
        $totalTax = $this->calculateTotalTax($salesOrderId);
        $grandTotal = $this->calculateGrandTotal($subtotal, $totalTax);

        SalesOrder::where('id', $salesOrderId)->update([
            'subtotal' => $subtotal,
            'total_tax' => $totalTax,
            'grand_total' => $grandTotal,
        ]);
    }

    /**
     * Hitung dan return semua total dalam transaction
     *
     * @return array{subtotal: float, total_tax: float, grand_total: float}
     */
    public function calculateAll(string $salesOrderId): array
    {
        return DB::transaction(function () use ($salesOrderId) {
            $subtotal = $this->calculateSubtotal($salesOrderId);
            $totalTax = $this->calculateTotalTax($salesOrderId);
            $grandTotal = $this->calculateGrandTotal($subtotal, $totalTax);

            $this->updateOrderTotals($salesOrderId);

            return [
                'subtotal' => $subtotal,
                'total_tax' => $totalTax,
                'grand_total' => $grandTotal,
            ];
        });
    }
}
