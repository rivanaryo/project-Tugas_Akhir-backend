<?php
namespace App\Modules\Purchase\PurchaseOrder\Repositories;

use App\Modules\Purchase\PurchaseOrder\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PurchaseOrderRepositories
{
    public function __construct(private readonly PurchaseOrder $model)
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

    public function findById(string $id): ?PurchaseOrder
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(string $id): PurchaseOrder
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): PurchaseOrder
    {
        return DB::transaction(function () use ($payload): PurchaseOrder {
            return $this->model->create($payload);
        });
    }

    public function update(string $id, array $payload): PurchaseOrder
    {
        return DB::transaction(function () use ($id, $payload): PurchaseOrder {
            $purchaseOrder = $this->findOrFail($id);
            $purchaseOrder->update($payload);
            return $purchaseOrder->fresh() ?? $purchaseOrder;
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
            ->with(['vendor', 'items.pricelist.vendor', 'items.pricelist.product'])
            ->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $vendorId = $this->normalizeString($filters['vendor_id'] ?? null);
        $status = $this->normalizeString($filters['status'] ?? null);
        $poDateFrom = $this->normalizeString($filters['po_date_from'] ?? null);
        $poDateTo = $this->normalizeString($filters['po_date_to'] ?? null);
        $search = $this->normalizeString($filters['q'] ?? ($filters['search'] ?? null));

        return $query
            ->when($vendorId !== null, fn (Builder $q): Builder => $q->where('vendor_id', $vendorId))
            ->when($status !== null, fn (Builder $q): Builder => $q->where('status', $status))
            ->when($poDateFrom !== null, fn (Builder $q): Builder => $q->whereDate('po_date', '>=', $poDateFrom))
            ->when($poDateTo !== null, fn (Builder $q): Builder => $q->whereDate('po_date', '<=', $poDateTo))
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $innerQuery) use ($search): void {
            $innerQuery
                ->where('po_number', 'like', "%{$search}%")
                ->orWhereHas('vendor', function (Builder $vendorQuery) use ($search): void {
                    $vendorQuery->where('vendor_name', 'like', "%{$search}%");
                })
                ->orWhereHas('items.pricelist.product', function (Builder $productQuery) use ($search): void {
                    $productQuery->where('product_name', 'like', "%{$search}%");
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

}
