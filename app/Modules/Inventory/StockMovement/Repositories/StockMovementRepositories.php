<?php

namespace App\Modules\Inventory\StockMovement\Repositories;

use App\Modules\Inventory\StockMovement\Models\StockMovement;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\ManagementWarehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StockMovementRepositories
{
    public function __construct(private readonly StockMovement $model)
    {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseQuery($filters)
            ->with(['product', 'warehouse'])
            ->paginate($perPage)
            ->withQueryString();
    }

    public function all(array $filters = []): Collection
    {
        return $this->baseQuery($filters)
            ->with(['product', 'warehouse'])
            ->get();
    }

    public function findById(string $id): ?StockMovement
    {
        return $this->baseQuery()
            ->with(['product', 'warehouse'])
            ->find($id);
    }

    public function findOrFail(string $id): StockMovement
    {
        return $this->baseQuery()
            ->with(['product', 'warehouse'])
            ->findOrFail($id);
    }

    public function createMovement(array $data, ?string $userId): StockMovement
    {
        return DB::transaction(function () use ($data, $userId) {
            $product = Product::lockForUpdate()->findOrFail($data['m_products_id']);
            
            $stockBefore = $product->stock_level;
            
            $stockAfter = $data['type'] === 'in' 
                ? $stockBefore + $data['quantity'] 
                : $stockBefore - $data['quantity'];

            $product->update(['stock_level' => $stockAfter]);

            return $this->model->create([
                'm_products_id' => $data['m_products_id'],
                'm_warehouses_id' => $data['m_warehouses_id'],
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_number' => $data['reference_number'],
                'description' => $data['description'] ?? null,
                'referenceable_id' => $data['referenceable_id'] ?? null,
                'referenceable_type' => $data['referenceable_type'] ?? null,
                'user_id' => $userId,
            ]);
        });
    }

    public function create(array $payload): StockMovement
    {
        return DB::transaction(function () use ($payload): StockMovement {
            return $this->model->create($payload);
        });
    }

    public function update(string $id, array $payload): StockMovement
    {
        return DB::transaction(function () use ($id, $payload): StockMovement {
            $stockMovement = $this->findOrFail($id);
            $stockMovement->update($payload);

            return $stockMovement->fresh() ?? $stockMovement;
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
            ->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $search = $this->normalizeString($filters['search'] ?? null);
        $type = $this->normalizeString($filters['type'] ?? null);
        $warehouseId = $this->normalizeString($filters['m_warehouses_id'] ?? null);
        $productId = $this->normalizeString($filters['m_products_id'] ?? null);

        return $query
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search))
            ->when($type !== null, fn (Builder $q): Builder => $q->where('type', $type))
            ->when($warehouseId !== null, fn (Builder $q): Builder => $q->where('m_warehouses_id', $warehouseId))
            ->when($productId !== null, fn (Builder $q): Builder => $q->where('m_products_id', $productId));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $builder) use ($search): void {
            $builder->where('reference_number', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('product', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
                ->orWhereHas('warehouse', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"));
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