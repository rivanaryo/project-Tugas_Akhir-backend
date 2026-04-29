<?php

namespace App\Modules\MasterData\DataProduk\Repositories;

use App\Modules\MasterData\DataProduk\References\Models\Units;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UnitsRepository
{
    public function __construct(private readonly Units $model)
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

    public function findById(string $id): ?Units
    {
        return $this->model->newQuery()->find($id);
    }

    public function findOrFail(string $id): Units
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    public function create(array $payload): Units
    {
        return DB::transaction(fn (): Units => $this->model->create($payload));
    }

    public function update(string $id, array $payload): Units
    {
        return DB::transaction(function () use ($id, $payload): Units {
            $unit = $this->findOrFail($id);
            $unit->update($payload);

            return $unit->fresh() ?? $unit;
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

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('unit_code', 'like', "%{$search}%")
                    ->orWhere('unit_name', 'like', "%{$search}%");
            });
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false);
        }

        return $query;
    }
}