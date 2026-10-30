<?php

namespace App\Modules\Sales\SalesQuotation\Services;

use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use App\Modules\Sales\SalesQuotation\Models\SalesQuotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Service untuk mengonversi sales quotation menjadi sales order.
 * Menyalin header dan seluruh items (termasuk quantity per ukuran).
 */
class SalesQuotationConverter
{
    public function convert(SalesQuotation $quotation): SalesOrder
    {
        if (in_array($quotation->status, ['rejected', 'expired', 'cancelled'], true)) {
            throw ValidationException::withMessages([
                'status' => "Quotation dengan status {$quotation->status} tidak dapat dikonversi menjadi sales order.",
            ]);
        }

        $quotation->loadMissing('items');

        if ($quotation->items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Quotation tidak memiliki items untuk dikonversi.',
            ]);
        }

        return DB::transaction(function () use ($quotation): SalesOrder {
            $salesOrder = SalesOrder::create([
                'order_number' => $this->generateOrderNumber($quotation),
                'customer_id' => $quotation->customer_id,
                'sales_quotation_id' => $quotation->id,
                'order_date' => now()->toDateString(),
                'status' => 'draft',
                'subtotal' => $quotation->subtotal,
                'total_tax' => $quotation->total_tax,
                'grand_total' => $quotation->grand_total,
            ]);

            foreach ($quotation->items as $item) {
                $salesOrder->items()->create([
                    'product_id' => $item->product_id,
                    'size' => $item->size,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'tax_rate' => $item->tax_rate,
                    'tax_amount' => $item->tax_amount,
                ]);
            }

            $quotation->updateStatus('accepted');

            return $salesOrder;
        });
    }

    /**
     * Generate nomor SO dari nomor quotation (SQ-2026-001 -> SO-2026-001),
     * dengan suffix -2, -3, ... jika sudah dipakai.
     */
    private function generateOrderNumber(SalesQuotation $quotation): string
    {
        $base = preg_replace('/^[A-Za-z]+/', 'SO', $quotation->quotation_number);
        $candidate = $base;
        $counter = 2;

        while (SalesOrder::where('order_number', $candidate)->exists()) {
            $candidate = "{$base}-{$counter}";
            $counter++;
        }

        return $candidate;
    }
}
