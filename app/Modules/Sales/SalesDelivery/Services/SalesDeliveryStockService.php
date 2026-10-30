<?php

namespace App\Modules\Sales\SalesDelivery\Services;

use App\Modules\Inventory\StockMovement\Repositories\StockMovementRepositories;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\Sales\SalesDelivery\Models\SalesDelivery;

/**
 * Service untuk mencatat stock movement Out saat barang jadi dikirim ke customer.
 * Dipanggil saat status sales delivery menjadi 'delivered'.
 */
class SalesDeliveryStockService
{
    public function __construct(
        private readonly StockMovementRepositories $stockMovementRepo,
    ) {
    }

    /**
     * Buat movement Out untuk setiap item delivery (qty_delivered).
     */
    public function process(SalesDelivery $delivery): void
    {
        $delivery->loadMissing(['items.salesOrderItem.product', 'stockMovements']);

        if ($delivery->stockMovements->isNotEmpty()) {
            return; // sudah diproses
        }

        foreach ($delivery->items as $item) {
            $product = $item->salesOrderItem?->product;
            if (! $product || $item->qty_delivered <= 0) {
                continue;
            }

            $this->stockMovementRepo->createMovement([
                'm_products_id' => $product->id,
                'm_warehouses_id' => $product->warehouse_id,
                'type' => 'Out',
                'quantity' => (int) $item->qty_delivered,
                'reference_number' => $delivery->delivery_number,
                'description' => 'Sales Delivery',
                'referenceable_id' => $delivery->id,
                'referenceable_type' => SalesDelivery::class,
            ], auth()->id());
        }
    }

    /**
     * Rollback: kembalikan stok produk dan hapus movement terkait delivery ini.
     */
    public function reverse(SalesDelivery $delivery): void
    {
        $delivery->loadMissing('stockMovements');

        foreach ($delivery->stockMovements as $movement) {
            $product = Product::lockForUpdate()->find($movement->m_products_id);
            if ($product) {
                $product->update(['stock_level' => $product->stock_level + $movement->quantity]);
            }
            $movement->delete();
        }
    }
}
