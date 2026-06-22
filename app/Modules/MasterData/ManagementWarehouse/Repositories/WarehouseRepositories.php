<?php

namespace App\Modules\MasterData\ManagementWarehouse\Repositories;

use App\Modules\MasterData\ManagementWarehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WarehouseRepositories
{
    public function __construct(private readonly Warehouse $model)
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

    public function findById(string $id): ?Warehouse
    {
        return $this->baseQuery()->find($id);
    }

    public function findByCode(string $code): ?Warehouse
    {
        return $this->baseQuery()->where('code', $code)->first();
    }

    public function findOrFail(string $id): Warehouse
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): Warehouse
    {
        return DB::transaction(function () use ($payload): Warehouse {
            return $this->model->create($payload);
        });
    }

    public function update(string $id, array $payload): Warehouse
    {
        return DB::transaction(function () use ($id, $payload): Warehouse {
            $warehouse = $this->findOrFail($id);
            $warehouse->update($payload);

            return $warehouse->fresh() ?? $warehouse;
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
        $isActive = $this->normalizeNullableBool($filters['is_active'] ?? null);

        return $query
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search))
            ->when($isActive !== null, fn (Builder $q): Builder => $q->where('is_active', $isActive));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $builder) use ($search): void {
            $builder->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
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
