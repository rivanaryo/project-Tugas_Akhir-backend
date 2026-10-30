<?php

namespace App\Modules\Sales\SalesQuotation\Services;

use App\Modules\Sales\SalesQuotation\Models\SalesQuotation;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk menghitung totalan sales quotation.
 * Menghitung: subtotal, total tax, dan grand total dari items.
 */
class SalesQuotationGrandTotalCalculator
{
    /**
     * Hitung subtotal dari semua items (tanpa tax)
     */
    public function calculateSubtotal(string $salesQuotationId): float
    {
        return (float) DB::table('sales_quotation_items')
            ->where('sales_quotation_id', $salesQuotationId)
            ->sum(DB::raw('quantity * unit_price'));
    }

    /**
     * Hitung total tax dari semua items
     */
    public function calculateTotalTax(string $salesQuotationId): float
    {
        return (float) DB::table('sales_quotation_items')
            ->where('sales_quotation_id', $salesQuotationId)
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
     * Update totalan di sales quotation
     */
    public function updateQuotationTotals(string $salesQuotationId): void
    {
        $subtotal = $this->calculateSubtotal($salesQuotationId);
        $totalTax = $this->calculateTotalTax($salesQuotationId);
        $grandTotal = $this->calculateGrandTotal($subtotal, $totalTax);

        SalesQuotation::where('id', $salesQuotationId)->update([
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
    public function calculateAll(string $salesQuotationId): array
    {
        return DB::transaction(function () use ($salesQuotationId) {
            $subtotal = $this->calculateSubtotal($salesQuotationId);
            $totalTax = $this->calculateTotalTax($salesQuotationId);
            $grandTotal = $this->calculateGrandTotal($subtotal, $totalTax);

            $this->updateQuotationTotals($salesQuotationId);

            return [
                'subtotal' => $subtotal,
                'total_tax' => $totalTax,
                'grand_total' => $grandTotal,
            ];
        });
    }
}
