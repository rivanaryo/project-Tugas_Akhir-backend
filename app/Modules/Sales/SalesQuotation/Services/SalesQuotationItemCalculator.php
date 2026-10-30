<?php

namespace App\Modules\Sales\SalesQuotation\Services;

use InvalidArgumentException;

/**
 * Service untuk menghitung detail item sales quotation.
 * Menangani perhitungan: subtotal, tax amount, dan total price item.
 */
class SalesQuotationItemCalculator
{
    /**
     * Hitung subtotal item (quantity * unit_price)
     */
    public function calculateSubtotal(int $quantity, float $unitPrice): float
    {
        if ($quantity < 0 || $unitPrice < 0) {
            throw new InvalidArgumentException('Quantity dan unit price tidak boleh negatif');
        }

        return $quantity * $unitPrice;
    }

    /**
     * Hitung tax amount untuk item
     * Formula: subtotal * (tax_rate / 100)
     */
    public function calculateTaxAmount(float $subtotal, float $taxRate): float
    {
        if ($taxRate < 0) {
            throw new InvalidArgumentException('Tax rate tidak boleh negatif');
        }

        return round($subtotal * ($taxRate / 100), 2);
    }

    /**
     * Hitung total price item (subtotal + tax)
     */
    public function calculateTotalPrice(float $subtotal, float $taxAmount): float
    {
        return round($subtotal + $taxAmount, 2);
    }

    /**
     * Hitung semua detail item dalam satu operasi
     *
     * @return array{subtotal: float, tax_amount: float, total_price: float}
     */
    public function calculate(int $quantity, float $unitPrice, float $taxRate = 0): array
    {
        $subtotal = $this->calculateSubtotal($quantity, $unitPrice);
        $taxAmount = $this->calculateTaxAmount($subtotal, $taxRate);
        $totalPrice = $this->calculateTotalPrice($subtotal, $taxAmount);

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_price' => $totalPrice,
        ];
    }
}
