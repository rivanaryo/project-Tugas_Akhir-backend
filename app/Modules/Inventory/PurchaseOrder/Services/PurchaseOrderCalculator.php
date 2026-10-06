<?php

namespace App\Modules\Inventory\PurchaseOrder\Services;

use App\Modules\Inventory\PurchaseOrder\Reference\Models\PurchaseOrderItems;

class PurchaseOrderCalculator
{
    public function applyTotalPrice(array $payload, ?PurchaseOrderItems $existing = null): array
    {
        $quantity = $payload['quantity'] ?? ($existing?->quantity);
        $unitPrice = $payload['unit_price'] ?? ($existing?->unit_price);
        $taxRate = $payload['tax_rate'] ?? ($existing?->tax_rate ?? 0);

        if ($quantity === null || $unitPrice === null) {
            return $payload;
        }

        $lineSubtotal = $this->calculateLineSubtotal((int) $quantity, (string) $unitPrice);
        $taxAmount = $this->calculateTaxAmount($lineSubtotal, (string) $taxRate);

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

        $taxAmount = round(((float) $subtotal) * (((float) $taxRate) / 100), 2);

        return number_format($taxAmount, 2, '.', '');
    }

    public function calculateLineTotal(string $subtotal, string $taxAmount): string
    {
        $subtotal = trim($subtotal);
        $taxAmount = trim($taxAmount);

        if ($subtotal === '') {
            $subtotal = '0.00';
        }

        if ($taxAmount === '') {
            $taxAmount = '0.00';
        }

        if (function_exists('bcadd')) {
            return (string) bcadd($subtotal, $taxAmount, 2);
        }

        $total = round(((float) $subtotal) + ((float) $taxAmount), 2);

        return number_format($total, 2, '.', '');
    }
}
