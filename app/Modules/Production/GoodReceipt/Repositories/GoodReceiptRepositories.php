<?php
namespace App\Modules\Production\GoodReceipt\Repositories;

use App\Modules\Production\GoodReceipt\Models\GoodReceipt;
use App\Modules\Production\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Inventory\StockMovement\Repositories\StockMovementRepositories;
use App\Modules\MasterData\DataProduk\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GoodReceiptRepositories
{
    public function __construct(
        private readonly GoodReceipt $model,
        private readonly StockMovementRepositories $stockMovementRepo,
    ) {
    }
    
    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model
            ->newQuery()
            ->with([
                'purchaseOrder',
                'vendor',
                'warehouse',
                'items.product.unit',
            ])
            ->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $grNumber = $this->normalizeString($filters['gr_number'] ?? null);
        $purchaseOrderId = $this->normalizeString($filters['purchase_order_id'] ?? null);
        $vendorId = $this->normalizeString($filters['vendor_id'] ?? null);
        $warehouseId = $this->normalizeString($filters['warehouse_id'] ?? null);
        $status = $this->normalizeString($filters['status'] ?? null);
        $receiptDate = $this->normalizeString($filters['receipt_date'] ?? null);
        $search = $this->normalizeString($filters['q'] ?? ($filters['search'] ?? null));

        return $query
            ->when($grNumber !== null, fn (Builder $q): Builder => $q->where('gr_number', $grNumber))
            ->when($purchaseOrderId !== null, fn (Builder $q): Builder => $q->where('purchase_order_id', $purchaseOrderId))
            ->when($vendorId !== null, fn (Builder $q): Builder => $q->where('vendor_id', $vendorId))
            ->when($warehouseId !== null, fn (Builder $q): Builder => $q->where('warehouse_id', $warehouseId))
            ->when($status !== null, fn (Builder $q): Builder => $q->where('status', $status))
            ->when($receiptDate !== null, fn (Builder $q): Builder => $q->whereDate('receipt_date', $receiptDate))
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $innerQuery) use ($search): void {
            $innerQuery
                ->where('gr_number', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhereHas('purchaseOrder', function (Builder $purchaseOrderQuery) use ($search): void {
                    $purchaseOrderQuery->where('po_number', 'like', "%{$search}%");
                })
                ->orWhereHas('vendor', function (Builder $vendorQuery) use ($search): void {
                    $vendorQuery->where('vendor_name', 'like', "%{$search}%");
                })
                ->orWhereHas('warehouse', function (Builder $warehouseQuery) use ($search): void {
                    $warehouseQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('items.product', function (Builder $productQuery) use ($search): void {
                    $productQuery->where('product_name', 'like', "%{$search}%");
                });
        });
    }

    private function normalizeString(mixed $string): ?string
    {
        if ($string === null) {
            return null;
        }

        $string = trim((string) $string);

        return $string === '' ? null : $string;
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function all(array $filters = []): Collection
    {
        return $this->baseQuery($filters)->get();
    }

    public function findById(int|string $id): ?GoodReceipt
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(int|string $id): GoodReceipt
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): GoodReceipt
    {
        return DB::transaction(function () use ($payload): GoodReceipt {
            $items = $payload['items'] ?? [];
            unset($payload['items']);

            $goodReceipt = $this->model->create($this->preparePayload($payload));

            foreach ($items as $item) {
                // Ambil data dari purchase order item untuk sinkronisasi product/pricelist
                $poItem = DB::table('purchase_order_items')->where('id', $item['purchase_order_item_id'])->first();
                
                if ($poItem) {
                    $goodReceipt->items()->create([
                        'purchase_order_item_id' => $item['purchase_order_item_id'],
                        'qty_received' => $item['qty_received'],
                        'product_id' => $poItem->product_id,
                        'pricelist_id' => $poItem->pricelist_id,
                    ]);
                }
            }

            $goodReceipt = $goodReceipt->load('items');

            if ($goodReceipt->status === 'received') {
                $this->createStockMovements($goodReceipt);
            }

            $this->syncPurchaseOrderStatus($goodReceipt);
            return $goodReceipt;
        });
    }

    public function update(int|string $id, array $payload): GoodReceipt
    {
        return DB::transaction(function () use ($id, $payload): GoodReceipt {
            $items = $payload['items'] ?? [];
            unset($payload['items']);

            $goodReceipt = $this->findOrFail($id);
            $goodReceipt->update($this->preparePayload($payload));

            if (!empty($items)) {
                // Sederhana: Hapus yang lama dan masukkan yang baru jika items dikirim
                // Atau bisa juga logic sync yang lebih kompleks
                $goodReceipt->items()->delete();
                
                foreach ($items as $item) {
                    $poItem = DB::table('purchase_order_items')->where('id', $item['purchase_order_item_id'])->first();
                    
                    if ($poItem) {
                        $goodReceipt->items()->create([
                            'purchase_order_item_id' => $item['purchase_order_item_id'],
                            'qty_received' => $item['qty_received'],
                            'product_id' => $poItem->product_id,
                            'pricelist_id' => $poItem->pricelist_id,
                        ]);
                    }
                }
            }

            $goodReceipt = $goodReceipt->fresh(['items', 'stockMovements']) ?? $goodReceipt;

            if ($goodReceipt->status === 'received') {
                $this->syncStockMovements($goodReceipt);
            } else {
                $this->deleteStockMovements($goodReceipt);
            }

            $this->syncPurchaseOrderStatus($goodReceipt);
            return $goodReceipt;
        });
    }

    public function delete(int|string $id): void
    {
        $goodReceipt = $this->findOrFail($id);
        $goodReceipt->load('stockMovements');
        $this->deleteStockMovements($goodReceipt);
        $goodReceipt->delete();
    }

    private function syncPurchaseOrderStatus(GoodReceipt $goodReceipt): void
    {
        $status = $goodReceipt->status;
        if ($status !== 'received' || $goodReceipt->purchase_order_id === null) {
            return;
        }

        $purchaseOrder = $goodReceipt->purchaseOrder;
        if ($purchaseOrder && $purchaseOrder->status !== 'received') {
            $purchaseOrder->update(['status' => 'received']);
        }
    }

    public function syncStockMovements(GoodReceipt $goodReceipt): void
    {
        $this->deleteStockMovements($goodReceipt);
        $this->createStockMovements($goodReceipt);
    }

    private function createStockMovements(GoodReceipt $goodReceipt): void
    {
        if ($goodReceipt->warehouse_id === null || $goodReceipt->items->isEmpty()) {
            return;
        }

        foreach ($goodReceipt->items as $item) {
            $this->stockMovementRepo->createMovement([
                'm_products_id' => $item->product_id,
                'm_warehouses_id' => $goodReceipt->warehouse_id,
                'type' => 'In',
                'quantity' => (int) $item->qty_received,
                'reference_number' => $goodReceipt->gr_number,
                'description' => 'Good Receipt',
                'referenceable_id' => $goodReceipt->id,
                'referenceable_type' => GoodReceipt::class,
            ], auth()->id());
        }
    }

    private function deleteStockMovements(GoodReceipt $goodReceipt): void
    {
        foreach ($goodReceipt->stockMovements as $movement) {
            $product = Product::lockForUpdate()->find($movement->m_products_id);
            if ($product) {
                $newStock = $product->stock_level - $movement->quantity;
                $product->update(['stock_level' => max(0, $newStock)]);
            }
            $movement->delete();
        }
    }

    private function preparePayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_string($value)) {
                $payload[$key] = trim($value);
            }
        }

        return array_filter($payload, static fn ($value): bool => $value !== null);
    }

}
