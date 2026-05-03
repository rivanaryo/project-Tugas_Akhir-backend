<?php

namespace App\Modules\MasterData\ManagementPricelist\Repositories;

use App\Modules\MasterData\ManagementPricelist\Models\Pricelist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PricelistRepositories
{
    public function __construct(private readonly Pricelist $model) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseQuery($filters)
            ->with(['vendor', 'product'])
            ->paginate($perPage)
            ->withQueryString();
    }

    public function all(array $filters = []): Collection
    {
        return $this->baseQuery($filters)->with(['vendor', 'product'])->get();
    }

    public function findById(string $id): ?Pricelist
    {
        return $this->baseQuery()->with(['vendor', 'product'])->find($id);
    }

    public function findOrFail(string $id): Pricelist
    {
        return $this->baseQuery()->with(['vendor', 'product'])->findOrFail($id);
    }

    public function create(array $payload): Pricelist
    {
        return DB::transaction(function () use ($payload): Pricelist {
            $pricelist = $this->model->create($payload);

            return $pricelist->load(['vendor', 'product']);
        });
    }

    public function update(string $id, array $payload): Pricelist
    {
        return DB::transaction(function () use ($id, $payload): Pricelist {
            $pricelist = $this->findOrFail($id);
            $pricelist->update($payload);

            return $pricelist->fresh(['vendor', 'product']) ?? $pricelist;
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
    }

    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model->newQuery()->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $search = $this->normalizeString($filters['search'] ?? null);

        return $query->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $builder) use ($search): void {
            $builder->whereHas('vendor', fn (Builder $q): Builder => $q->where('vendor_name', 'like', "%{$search}%"))
                ->orWhereHas('product', fn (Builder $q): Builder => $q->where('product_name', 'like', "%{$search}%"))
                ->orWhere('currency', 'like', "%{$search}%");
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
