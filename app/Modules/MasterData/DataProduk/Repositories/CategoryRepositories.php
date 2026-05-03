<?php

namespace App\Modules\MasterData\DataProduk\Repositories;

use App\Modules\MasterData\DataProduk\References\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryRepositories
{
    public function __construct(private readonly Category $model)
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

    public function findById(string $id): ?Category
    {
        return $this->model->newQuery()->find($id);
    }

    public function findOrFail(string $id): Category
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    public function create(array $payload): Category
    {
        return DB::transaction(fn (): Category => $this->model->create($payload));
    }

    public function update(string $id, array $payload): Category
    {
        return DB::transaction(function () use ($id, $payload): Category {
            $category = $this->findOrFail($id);
            $category->update($payload);

            return $category->fresh() ?? $category;
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
            ->when($search !== null, fn (Builder $q): Builder => $q->where('category_name', 'like', "%{$search}%"))
            ->when($isActive !== null, fn (Builder $q): Builder => $q->where('is_active', $isActive));
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



