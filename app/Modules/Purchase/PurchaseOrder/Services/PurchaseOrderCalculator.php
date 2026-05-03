<?php

namespace App\Modules\Purchase\PurchaseOrder\Services;

use App\Modules\Purchase\PurchaseOrder\Reference\Models\PurchaseOrderItems;

class PurchaseOrderCalculator
{
    public function applyTotalPrice(array $payload, ?PurchaseOrderItems $existing = null): array
    {
        $quantity = $payload['quantity'] ?? ($existing?->quantity);
        $unitPrice = $payload['unit_price'] ?? ($existing?->unit_price);

        if ($quantity === null || $unitPrice === null) {
            return $payload;
        }

        $payload['total_price'] = $this->calculateLineTotal((int) $quantity, (string) $unitPrice);

        return $payload;
    }

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
}
