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

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function (Builder $builder) use ($search): void {
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

        if (! empty($filters['category_id'])) {
            $query->where('category_id', (string) $filters['category_id']);
        }

        if (! empty($filters['unit_id'])) {
            $query->where('unit_id', (string) $filters['unit_id']);
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false);
        }

        return $query;
    }
}