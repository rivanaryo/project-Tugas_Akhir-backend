<?php
namespace App\Modules\Production\PurchaseOrder\Repositories;

use App\Modules\Production\PurchaseOrder\Reference\Models\PurchaseOrderItems;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseOrderItemsRepositories
{
    public function __construct(private readonly PurchaseOrderItems $model)
    {
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

    public function findById(string $id): ?PurchaseOrderItems
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(string $id): PurchaseOrderItems
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): PurchaseOrderItems
    {
        return DB::transaction(function () use ($payload): PurchaseOrderItems {
            return $this->model->create($payload);
        });
    }

    public function update(string $id, array $payload): PurchaseOrderItems
    {
        return DB::transaction(function () use ($id, $payload): PurchaseOrderItems {
            $purchaseOrderItems = $this->findOrFail($id);
            $purchaseOrderItems->update($payload);
            return $purchaseOrderItems->fresh() ?? $purchaseOrderItems;
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
    }

    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model
            ->newQuery()
            ->with(['purchaseOrder', 'pricelist.vendor', 'pricelist.product'])
            ->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $purchaseOrderId = $this->normalizeString($filters['purchase_order_id'] ?? null);
        $productId = $this->normalizeString($filters['product_id'] ?? null);
        $pricelistId = $this->normalizeString($filters['pricelist_id'] ?? null);

        return $query
            ->when($purchaseOrderId !== null, fn (Builder $q): Builder => $q->where('purchase_order_id', $purchaseOrderId))
            ->when($productId !== null, fn (Builder $q): Builder => $q->where('product_id', $productId))
            ->when($pricelistId !== null, fn (Builder $q): Builder => $q->where('pricelist_id', $pricelistId));
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

}
