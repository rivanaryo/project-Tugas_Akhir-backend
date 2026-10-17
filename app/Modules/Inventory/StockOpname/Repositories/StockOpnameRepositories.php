<?php

namespace App\Modules\Inventory\StockOpname\Repositories;

use App\Modules\Inventory\StockMovement\Models\StockMovement;
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
                $opname->items()->create($this->prepareItem($item, $opname));
            }

            $this->syncAdjustmentMovements($opname);

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
                    $opname->items()->create($this->prepareItem($item, $opname));
                }
            }

            $opname = $opname->fresh() ?? $opname;
            $this->syncAdjustmentMovements($opname);

            return $opname;
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
    }

    private function prepareItem(array $item, StockOpname $opname): array
    {
        $physical = (int) ($item['physical_qty'] ?? 0);
        $system = $this->calculateSystemQty(
            (string) $item['product_id'],
            (string) $opname->warehouse_id,
            (string) $opname->opname_date?->toDateString()
        );

        return [
            'product_id' => $item['product_id'],
            'system_qty' => $system,
            'physical_qty' => $physical,
            'difference_qty' => $physical - $system,
            'notes' => $item['notes'] ?? null,
        ];
    }

    private function calculateSystemQty(string $productId, string $warehouseId, string $opnameDate): int
    {
        $result = DB::table('stock_movements')
            ->where('m_products_id', $productId)
            ->where('m_warehouses_id', $warehouseId)
            ->whereDate('date', '<=', $opnameDate)
            ->selectRaw("SUM(CASE WHEN type = 'In' THEN quantity ELSE -quantity END) as qty")
            ->first();

        return (int) ($result?->qty ?? 0);
    }

    private function syncAdjustmentMovements(StockOpname $opname): void
    {
        $opname->stockMovements()->delete();

        if ($opname->status !== 'Completed') {
            return;
        }

        foreach ($opname->items as $item) {
            $difference = (int) $item->difference_qty;

            if ($difference === 0) {
                continue;
            }

            $opname->stockMovements()->create([
                'm_products_id' => $item->product_id,
                'm_warehouses_id' => $opname->warehouse_id,
                'type' => $difference > 0 ? 'In' : 'Out',
                'quantity' => abs($difference),
                'stock_before' => (int) $item->system_qty,
                'stock_after' => (int) $item->physical_qty,
                'referenceable_id' => $opname->id,
                'referenceable_type' => StockOpname::class,
                'reference_number' => $opname->opname_number,
                'description' => 'Stock opname adjustment',
            ]);
        }
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
