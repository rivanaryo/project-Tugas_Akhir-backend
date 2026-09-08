<?php

namespace App\Modules\Inventory\StockOpname\Repositories;

use App\Modules\Inventory\StockOpname\Models\StockOpname;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StockOpnameRepositories
{
    public function __construct(private readonly StockOpname $model)
    {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseQuery($filters)
            ->with(['warehouse', 'items.product', 'creator'])
            ->paginate($perPage)
            ->withQueryString();
    }

    public function all(array $filters = []): Collection
    {
        return $this->baseQuery($filters)
            ->with(['warehouse', 'items.product', 'creator'])
            ->get();
    }

    public function findById(string $id): ?StockOpname
    {
        return $this->baseQuery()
            ->with(['warehouse', 'items.product', 'creator'])
            ->find($id);
    }

    public function findOrFail(string $id): StockOpname
    {
        return $this->baseQuery()
            ->with(['warehouse', 'items.product', 'creator'])
            ->findOrFail($id);
    }

    public function createOpname(array $data): StockOpname
    {
        return DB::transaction(function () use ($data): StockOpname {
            $opname = $this->model->create([
                'opname_number' => $data['opname_number'],
                'warehouse_id' => $data['warehouse_id'],
                'opname_date' => $data['opname_date'],
                'status' => $data['status'] ?? 'Draft',
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $opname->items()->create($this->prepareItem($item));
            }

            return $opname;
        });
    }

    public function update(string $id, array $data): StockOpname
    {
        return DB::transaction(function () use ($id, $data): StockOpname {
            $opname = $this->findOrFail($id);

            $header = Arr::only($data, ['opname_number', 'warehouse_id', 'opname_date', 'status', 'notes']);
            if (! empty($header)) {
                $opname->update($header);
            }

            if (array_key_exists('items', $data) && is_array($data['items'])) {
                $opname->items()->delete();
                foreach ($data['items'] as $item) {
                    $opname->items()->create($this->prepareItem($item));
                }
            }

            return $opname->fresh() ?? $opname;
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
    }

    private function prepareItem(array $item): array
    {
        $system = (int) ($item['system_qty'] ?? 0);
        $physical = (int) ($item['physical_qty'] ?? 0);

        return [
            'product_id' => $item['product_id'],
            'system_qty' => $system,
            'physical_qty' => $physical,
            'difference_qty' => $physical - $system,
            'notes' => $item['notes'] ?? null,
        ];
    }

    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model->newQuery()->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $search = $this->normalizeString($filters['search'] ?? null);
        $status = $this->normalizeString($filters['status'] ?? null);
        $warehouseId = $this->normalizeString($filters['warehouse_id'] ?? null);

        return $query
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search))
            ->when($status !== null, fn (Builder $q): Builder => $q->where('status', $status))
            ->when($warehouseId !== null, fn (Builder $q): Builder => $q->where('warehouse_id', $warehouseId));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $builder) use ($search): void {
            $builder->where('opname_number', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
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
