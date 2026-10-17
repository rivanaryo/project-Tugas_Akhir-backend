<?php

namespace App\Modules\Inventory\StockBalance\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StockBalanceRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->baseQuery($filters);

        return $query
            ->orderByDesc('current_stock')
            ->paginate($perPage)
            ->withQueryString();
    }

    private function baseQuery(array $filters): Builder
    {
        $subQuery = DB::table('stock_movements')
            ->select('m_products_id', 'm_warehouses_id')
            ->selectRaw("SUM(CASE WHEN type = 'In' THEN quantity ELSE -quantity END) AS current_stock")
            ->selectRaw("SUM(CASE WHEN type = 'In' THEN quantity ELSE 0 END) AS total_in")
            ->selectRaw("SUM(CASE WHEN type = 'Out' THEN quantity ELSE 0 END) AS total_out")
            ->groupBy('m_products_id', 'm_warehouses_id');

        $query = DB::query()->fromSub($subQuery, 'balance')
            ->join('m_products', 'balance.m_products_id', '=', 'm_products.id')
            ->join('warehouses', 'balance.m_warehouses_id', '=', 'warehouses.id')
            ->join('units', 'm_products.unit_id', '=', 'units.id')
            ->select(
                'balance.m_products_id AS product_id',
                'm_products.product_name',
                'balance.m_warehouses_id AS warehouse_id',
                'warehouses.name AS warehouse_name',
                'warehouses.code AS warehouse_code',
                'units.unit_code',
                'balance.total_in',
                'balance.total_out',
                'balance.current_stock',
            );

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $productId = $this->normalizeString($filters['product_id'] ?? null);
        $warehouseId = $this->normalizeString($filters['warehouse_id'] ?? null);
        $search = $this->normalizeString($filters['search'] ?? null);

        return $query
            ->when($productId !== null, fn (Builder $q): Builder => $q->where('balance.m_products_id', $productId))
            ->when($warehouseId !== null, fn (Builder $q): Builder => $q->where('balance.m_warehouses_id', $warehouseId))
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $builder) use ($search): void {
            $builder->where('m_products.product_name', 'like', "%{$search}%")
                ->orWhere('warehouses.name', 'like', "%{$search}%")
                ->orWhere('warehouses.code', 'like', "%{$search}%")
                ->orWhere('units.unit_code', 'like', "%{$search}%");
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
