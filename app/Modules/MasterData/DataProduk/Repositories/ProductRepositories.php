<?php

namespace App\Modules\MasterData\DataProduk\Repositories;

use App\Modules\MasterData\DataProduk\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductRepositories
{
    public function __construct(private readonly Product $model)
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

    public function findById(string $id): ?Product
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(string $id): Product
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): Product
    {
        return DB::transaction(function () use ($payload): Product {
            return $this->model->create($payload);
        });
    }

    public function update(string $id, array $payload): Product
    {
        return DB::transaction(function () use ($id, $payload): Product {
            $product = $this->findOrFail($id);
            $product->update($payload);

            return $product->fresh(['category', 'unit']) ?? $product;
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            return (bool) $this->findOrFail($id)->delete();
        });
    }

    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model
            ->newQuery()
            ->with([
                'category:id,category_name',
                'unit:id,unit_code,unit_name',
            ])
            ->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $search = $this->normalizeString($filters['search'] ?? null);
        $categoryId = $this->normalizeString($filters['category_id'] ?? null);
        $unitId = $this->normalizeString($filters['unit_id'] ?? null);
        $warehouseId = $this->normalizeString($filters['warehouse_id'] ?? null);
        $isActive = $this->normalizeNullableBool($filters['is_active'] ?? null);

        return $query
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search))
            ->when($categoryId !== null, fn (Builder $q): Builder => $q->where('category_id', $categoryId))
            ->when($unitId !== null, fn (Builder $q): Builder => $q->where('unit_id', $unitId))
            ->when($warehouseId !== null, fn (Builder $q): Builder => $q->where('warehouse_id', $warehouseId))
            ->when($isActive !== null, fn (Builder $q): Builder => $q->where('is_active', $isActive));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $builder) use ($search): void {
            $builder->where('product_name', 'like', "%{$search}%")
                ->orWhereHas('category', function (Builder $categoryQuery) use ($search): void {
                    $categoryQuery->where('category_name', 'like', "%{$search}%");
                })
                ->orWhereHas('unit', function (Builder $unitQuery) use ($search): void {
                    $unitQuery->where('unit_code', 'like', "%{$search}%")
                        ->orWhere('unit_name', 'like', "%{$search}%");
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

    private function normalizeNullableBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
    }
}
