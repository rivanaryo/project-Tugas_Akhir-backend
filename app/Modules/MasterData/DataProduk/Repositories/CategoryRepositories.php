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

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('category_name', 'like', "%{$search}%");
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false);
        }

        return $query;
    }
}



