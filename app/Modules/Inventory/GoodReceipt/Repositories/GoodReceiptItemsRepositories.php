<?php
namespace App\Modules\Inventory\GoodReceipt\Repositories;

use App\Modules\Inventory\GoodReceipt\Reference\Models\GoodReceiptItems;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GoodReceiptItemsRepositories
{
    public function __construct(
        private readonly GoodReceiptItems $model
    ) {
    }
    
    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model
            ->newQuery()
            ->with([
                'goodReceipt',
                'purchaseOrderItem.purchaseOrder',
                'pricelist.vendor',
                'product',
            ])
            ->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $goodReceiptId = $this->normalizeString($filters['good_receipt_id'] ?? null);
        $purchaseOrderItemId = $this->normalizeString($filters['purchase_order_item_id'] ?? null);
        $pricelistId = $this->normalizeString($filters['pricelist_id'] ?? null);
        $productId = $this->normalizeString($filters['product_id'] ?? null);
        $search = $this->normalizeString($filters['q'] ?? ($filters['search'] ?? null));

        return $query
            ->when($goodReceiptId !== null, fn (Builder $q): Builder => $q->where('good_receipt_id', $goodReceiptId))
            ->when($purchaseOrderItemId !== null, fn (Builder $q): Builder => $q->where('purchase_order_item_id', $purchaseOrderItemId))
            ->when($pricelistId !== null, fn (Builder $q): Builder => $q->where('pricelist_id', $pricelistId))
            ->when($productId !== null, fn (Builder $q): Builder => $q->where('product_id', $productId))
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search));
    }
    
    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $innerQuery) use ($search): void {
            $innerQuery
                ->where('id', 'like', "%{$search}%")
                ->orWhere('qty_received', 'like', "%{$search}%")
                ->orWhereHas('goodReceipt', function (Builder $goodReceiptQuery) use ($search): void {
                    $goodReceiptQuery->where('gr_number', 'like', "%{$search}%");
                })
                ->orWhereHas('purchaseOrderItem.purchaseOrder', function (Builder $purchaseOrderQuery) use ($search): void {
                    $purchaseOrderQuery->where('po_number', 'like', "%{$search}%");
                })
                ->orWhereHas('product', function (Builder $productQuery) use ($search): void {
                    $productQuery->where('product_name', 'like', "%{$search}%");
                })
                ->orWhereHas('pricelist.vendor', function (Builder $vendorQuery) use ($search): void {
                    $vendorQuery->where('vendor_name', 'like', "%{$search}%");
                });
        });
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
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

    public function create(array $payload): GoodReceiptItems
    {
        return DB::transaction(function () use ($payload): GoodReceiptItems {
            return $this->model->create($this->preparePayload($payload));
        });
    }
    public function findById(int|string $id): ?GoodReceiptItems
    {
        return $this->baseQuery()->find($id);
    }
    public function findOrFail(int|string $id): GoodReceiptItems
    {
        return $this->baseQuery()->findOrFail($id);
    }
    public function update(int|string $id, array $payload): GoodReceiptItems
    {
        return DB::transaction(function () use ($id, $payload): GoodReceiptItems {
            $goodReceiptItems = $this->findOrFail($id);
            $goodReceiptItems->update($this->preparePayload($payload));
            return $goodReceiptItems->fresh() ?? $goodReceiptItems;
        });
    }
    public function delete(int|string $id): void
    {
        DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
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
