<?php

namespace App\Modules\Production\FinishedGoodsReceipt\Services;

use App\Modules\Inventory\StockMovement\Repositories\StockMovementRepositories;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\Production\FinishedGoodsReceipt\Models\FinishedGoodsReceipt;
use App\Modules\Production\FinishedGoodsReceipt\Reference\Models\FinishedGoodsReceiptItems;
use App\Modules\Production\WorkOrder\Models\WorkOrder;
use App\Modules\Rd\TechPack\Models\TechPack;
use App\Modules\Sales\SalesDelivery\Models\SalesDelivery;
use Illuminate\Support\Collection;

/**
 * Service untuk memproses efek Finished Goods Receipt:
 * - Stock movement "In" barang jadi ke warehouse
 * - Update qty_completed/qty_reject dan status work order (SPK)
 * - Membuat draft surat jalan (sales delivery) otomatis
 */
class FinishedGoodsReceiptProcessor
{
    public function __construct(
        private readonly StockMovementRepositories $stockMovementRepo,
    ) {
    }

    /**
     * Proses semua item FGR (dipanggil saat status menjadi received).
     */
    public function process(FinishedGoodsReceipt $fgr): void
    {
        $fgr->loadMissing(['items.workOrder.salesOrderItem', 'items.workOrder.salesOrder.customer', 'items.workOrder.techPack.bomItems', 'stockMovements']);

        if ($fgr->stockMovements->isNotEmpty()) {
            return; // sudah diproses
        }

        foreach ($fgr->items as $item) {
            $this->createStockIn($fgr, $item);
            $this->consumeMaterials($fgr, $item);
            $this->updateWorkOrderProgress($item);
        }

        $this->createDraftDeliveries($fgr);
    }

    /**
     * Rollback efek FGR (dipanggil saat delete / cancel):
     * revert stok produk, kurangi qty_completed work order, hapus draft delivery terkait.
     */
    public function reverse(FinishedGoodsReceipt $fgr): void
    {
        $fgr->loadMissing(['items.workOrder', 'stockMovements']);

        foreach ($fgr->stockMovements as $movement) {
            $product = Product::lockForUpdate()->find($movement->m_products_id);
            if ($product) {
                $newStock = $movement->type === 'In'
                    ? $product->stock_level - $movement->quantity
                    : $product->stock_level + $movement->quantity;
                $product->update(['stock_level' => max(0, $newStock)]);
            }
            $movement->delete();
        }

        foreach ($fgr->items as $item) {
            $workOrder = $item->workOrder;
            if (! $workOrder) {
                continue;
            }
            $workOrder->qty_completed = max(0, $workOrder->qty_completed - $item->qty_good);
            $workOrder->qty_reject = max(0, $workOrder->qty_reject - $item->qty_reject);
            $this->applyWorkOrderStatus($workOrder);
            $workOrder->save();
        }

        SalesDelivery::where('delivery_number', 'like', "DO-{$fgr->fgr_number}%")
            ->where('status', 'draft')
            ->each(fn (SalesDelivery $delivery) => $delivery->delete());
    }

    private function createStockIn(FinishedGoodsReceipt $fgr, FinishedGoodsReceiptItems $item): void
    {
        if ($item->qty_good <= 0) {
            return;
        }

        $this->stockMovementRepo->createMovement([
            'm_products_id' => $item->product_id,
            'm_warehouses_id' => $fgr->warehouse_id,
            'type' => 'In',
            'quantity' => $item->qty_good,
            'reference_number' => $fgr->fgr_number,
            'description' => 'Finished Goods Receipt',
            'referenceable_id' => $fgr->id,
            'referenceable_type' => FinishedGoodsReceipt::class,
        ], auth()->id());
    }

    /**
     * Backflushing: kurangi stok bahan baku sesuai BOM tech pack,
     * sebesar usage * qty_good per item FGR.
     */
    private function consumeMaterials(FinishedGoodsReceipt $fgr, FinishedGoodsReceiptItems $item): void
    {
        $workOrder = $item->workOrder;
        if (! $workOrder || $item->qty_good <= 0) {
            return;
        }

        $bomItems = $workOrder->techPack?->bomItems
            ?? TechPack::where('product_id', $workOrder->product_id)->latest()->get()
                ->firstWhere('status', 'approved')?->bomItems
            ?? TechPack::where('product_id', $workOrder->product_id)->latest()->first()?->bomItems
            ?? collect();

        foreach ($bomItems as $bomItem) {
            $quantity = (int) round((float) $bomItem->usage * $item->qty_good);
            if ($quantity <= 0) {
                continue;
            }

            $this->stockMovementRepo->createMovement([
                'm_products_id' => $bomItem->material_product_id,
                'm_warehouses_id' => $fgr->warehouse_id,
                'type' => 'Out',
                'quantity' => $quantity,
                'reference_number' => $fgr->fgr_number,
                'description' => "Material consumption {$workOrder->wo_number}",
                'referenceable_id' => $fgr->id,
                'referenceable_type' => FinishedGoodsReceipt::class,
            ], auth()->id());
        }
    }

    private function updateWorkOrderProgress(FinishedGoodsReceiptItems $item): void
    {
        $workOrder = $item->workOrder;
        if (! $workOrder) {
            return;
        }

        $workOrder->qty_completed += $item->qty_good;
        $workOrder->qty_reject += $item->qty_reject;
        $this->applyWorkOrderStatus($workOrder);
        $workOrder->save();
    }

    private function applyWorkOrderStatus(WorkOrder $workOrder): void
    {
        if ($workOrder->qty_completed >= $workOrder->quantity) {
            $workOrder->stage = 'ready';
            $workOrder->status = 'completed';
            $workOrder->progress = 100;
        } elseif ($workOrder->status !== 'cancelled') {
            $workOrder->status = 'in-progress';
            $workOrder->progress = WorkOrder::stageToProgress($workOrder->stage);
        }
    }

    /**
     * Buat draft sales delivery per sales order dari item-item FGR.
     */
    private function createDraftDeliveries(FinishedGoodsReceipt $fgr): void
    {
        $items = $fgr->items->filter(fn (FinishedGoodsReceiptItems $item): bool => $item->qty_good > 0);

        $items->groupBy(fn (FinishedGoodsReceiptItems $item): ?string => $item->workOrder?->sales_order_id)
            ->filter(fn (Collection $group, ?string $salesOrderId): bool => $salesOrderId !== null)
            ->each(function (Collection $group, string $salesOrderId) use ($fgr): void {
                $salesOrder = $group->first()->workOrder->salesOrder;
                if (! $salesOrder) {
                    return;
                }

                $delivery = SalesDelivery::create([
                    'delivery_number' => $this->generateDeliveryNumber($fgr),
                    'customer_id' => $salesOrder->customer_id,
                    'sales_order_id' => $salesOrder->id,
                    'delivery_date' => $fgr->receipt_date,
                    'status' => 'draft',
                ]);

                foreach ($group as $item) {
                    $salesOrderItemId = $this->resolveSalesOrderItemId($item, $salesOrder->id);
                    if ($salesOrderItemId === null) {
                        continue;
                    }

                    $delivery->items()->create([
                        'sales_order_item_id' => $salesOrderItemId,
                        'qty_delivered' => $item->qty_good,
                    ]);
                }
            });
    }

    private function resolveSalesOrderItemId(FinishedGoodsReceiptItems $item, string $salesOrderId): ?string
    {
        $salesOrderItemId = $item->workOrder?->sales_order_item_id;
        if ($salesOrderItemId) {
            return $salesOrderItemId;
        }

        // Fallback untuk work order lama tanpa sales_order_item_id:
        // cocokkan item SO berdasarkan product_id + size
        return \App\Modules\Sales\SalesOrder\Reference\Models\SalesOrderItems::where('sales_order_id', $salesOrderId)
            ->where('product_id', $item->product_id)
            ->when($item->size !== null, fn ($q) => $q->where('size', $item->size))
            ->value('id');
    }

    private function generateDeliveryNumber(FinishedGoodsReceipt $fgr): string
    {
        $base = "DO-{$fgr->fgr_number}";
        $candidate = $base;
        $counter = 2;

        while (SalesDelivery::where('delivery_number', $candidate)->exists()) {
            $candidate = "{$base}-{$counter}";
            $counter++;
        }

        return $candidate;
    }
}
