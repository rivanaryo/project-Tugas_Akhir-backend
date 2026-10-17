<?php

namespace App\Modules\Production\GoodReceipt\Observers;

use App\Modules\Production\GoodReceipt\Reference\Models\GoodReceiptItems;
use App\Modules\Production\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Production\PurchaseOrder\Reference\Models\PurchaseOrderItems;
use Illuminate\Support\Facades\DB;

class GoodReceiptItemsObserver
{
    /**
     * Handle the GoodReceiptItems "created" event.
     */
    public function created(GoodReceiptItems $goodReceiptItem): void
    {
        $this->updatePurchaseOrderStatus($goodReceiptItem);
    }

    /**
     * Handle the GoodReceiptItems "updated" event.
     */
    public function updated(GoodReceiptItems $goodReceiptItem): void
    {
        $this->updatePurchaseOrderStatus($goodReceiptItem);
    }

    /**
     * Handle the GoodReceiptItems "deleted" event.
     */
    public function deleted(GoodReceiptItems $goodReceiptItem): void
    {
        $this->updatePurchaseOrderStatus($goodReceiptItem);
    }

    /**
     * Logika untuk update status Purchase Order berdasarkan perbandingan quantity.
     */
    private function updatePurchaseOrderStatus(GoodReceiptItems $goodReceiptItem): void
    {
        // Ambil Purchase Order ID dari item terkait
        $purchaseOrderItem = PurchaseOrderItems::find($goodReceiptItem->purchase_order_item_id);
        if (!$purchaseOrderItem) return;

        $purchaseOrderId = $purchaseOrderItem->purchase_order_id;
        $purchaseOrder = PurchaseOrder::find($purchaseOrderId);
        if (!$purchaseOrder) return;

        DB::transaction(function () use ($purchaseOrder) {
            // 1. Hitung total quantity yang diminta di PurchaseOrderItems
            $totalOrdered = (int) DB::table('purchase_order_items')
                ->where('purchase_order_id', $purchaseOrder->id)
                ->sum('quantity');

            // 2. Hitung total quantity yang sudah diterima dari semua GoodReceiptItems yang merujuk ke PO tersebut
            // Kita join ke purchase_order_items untuk memfilter berdasarkan purchase_order_id
            $totalReceived = (int) DB::table('good_receipt_items')
                ->join('purchase_order_items', 'good_receipt_items.purchase_order_item_id', '=', 'purchase_order_items.id')
                ->where('purchase_order_items.purchase_order_id', $purchaseOrder->id)
                ->sum('good_receipt_items.qty_received');

            // 3. Logika Update Status
            if ($totalReceived <= 0) {
                $newStatus = 'draft';
            } elseif ($totalReceived >= $totalOrdered) {
                $newStatus = 'received';
            } else {
                $newStatus = 'approved';
            }

            // Update jika status berbeda
            if ($purchaseOrder->status !== $newStatus) {
                $purchaseOrder->update(['status' => $newStatus]);
            }
        });
    }
}
