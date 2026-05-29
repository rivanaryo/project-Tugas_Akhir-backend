<?php 
namespace App\Modules\Sales\Invoice\Services;
use App\Modules\Sales\Invoice\Models\InvoiceItem;
use App\Modules\Sales\Invoice\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceCalculator
{
    public function updateInvoiceHeaderTotals(string $invoiceId): void
    {
        $items = DB::table('invoice_items')->where('invoice_id', $invoiceId)->get();
        
        $subtotal = '0.00';
        $taxTotal = '0.00';

        foreach ($items as $item) {
            $lineSubtotal = $this->calculateLineSubtotal((int) $item->qty_invoice, (string) $item->unit_price);
            $subtotal = $this->calculateLineTotal($subtotal, $lineSubtotal);
            $taxTotal = $this->calculateLineTotal($taxTotal, (string) $item->tax_amount);
        }

        $grandTotal = $this->calculateLineTotal($subtotal, $taxTotal);

        Invoice::where('id', $invoiceId)->update([
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'grand_total' => $grandTotal,
        ]);
    }

    public function applyTotalPrice(array $payload, ?InvoiceItem $existing = null): array
    {
        $quantity = $payload['qty_invoice'] ?? ($existing?->qty_invoice);
        $unitPrice = $payload['unit_price'] ?? ($existing?->unit_price);
        $taxRate = $payload['tax_rate'] ?? ($existing?->tax_rate ?? 0);

        if ($quantity === null || $unitPrice === null) {
            return $payload;
        }

        $lineSubtotal = $this->calculateLineSubtotal((int) $quantity, (string) $unitPrice);
        $taxAmount = $this->calculateTaxAmount($lineSubtotal, (string) $taxRate);

        $payload['subtotal'] = $lineSubtotal;
        $payload['tax_rate'] = (string) $taxRate;
        $payload['tax_amount'] = $taxAmount;
        $payload['total_price'] = $this->calculateLineTotal($lineSubtotal, $taxAmount);

        return $payload;
    }

    public function calculateLineSubtotal(int $quantity, string $unitPrice): string
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

    public function calculateTaxAmount(string $subtotal, string $taxRate): string
    {
        $subtotal = trim($subtotal);
        $taxRate = trim($taxRate);

        if ($subtotal === '' || $taxRate === '') {
            return '0.00';
        }

        if (function_exists('bcmul') && function_exists('bcdiv')) {
            $rate = (string) bcdiv($taxRate, '100', 6);
            return (string) bcmul($subtotal, $rate, 2);
        }
        $rate = ((float) $taxRate) / 100;
        $taxAmount = ((float) $subtotal) * $rate;

        return number_format($taxAmount, 2, '.', '');
    }

    public function calculateLineTotal(string $subtotal, string $taxAmount): string
    {
        if (function_exists('bcadd')) {
            return (string) bcadd($subtotal, $taxAmount, 2);
        }

        $total = ((float) $subtotal) + ((float) $taxAmount);

        return number_format($total, 2, '.', '');
    }

    public function applyInvoiceTotals(array $payload, array $invoiceItems): array
    {
        $subtotal = '0.00';
        $taxTotal = '0.00';

        foreach ($invoiceItems as $item) {
            $subtotal = $this->calculateLineTotal($subtotal, $item['subtotal']);
            $taxTotal = $this->calculateLineTotal($taxTotal, $item['tax_amount']);
        }

        $grandTotal = $this->calculateLineTotal($subtotal, $taxTotal);

        $payload['subtotal'] = $subtotal;
        $payload['tax_total'] = $taxTotal;
        $payload['grand_total'] = $grandTotal;

        return $payload;
    }

    public function applyInvoiceItemTotals(array $payload, ?InvoiceItem $existing = null): array
    {
        $quantity = $payload['qty_invoice'] ?? ($existing?->qty_invoice);
        $unitPrice = $payload['unit_price'] ?? ($existing?->unit_price);
        $taxRate = $payload['tax_rate'] ?? ($existing?->tax_rate ?? 0);

        if ($quantity === null || $unitPrice === null) {
            return $payload;
        }

        $lineSubtotal = $this->calculateLineSubtotal((int) $quantity, (string) $unitPrice);
        $taxAmount = $this->calculateTaxAmount($lineSubtotal, (string) $taxRate);

        $payload['subtotal'] = $lineSubtotal;
        $payload['tax_rate'] = (string) $taxRate;
        $payload['tax_amount'] = $taxAmount;
        $payload['total_price'] = $this->calculateLineTotal($lineSubtotal, $taxAmount);

        return $payload;
    }
}