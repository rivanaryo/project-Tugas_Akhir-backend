<?php

namespace App\Modules\Sales\Invoice\Repositories;

use App\Modules\Sales\Invoice\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceItemRepositories
{
    public function __construct(private readonly InvoiceItem $model) {}

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

    public function findById(string $id): ?InvoiceItem
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(string $id): InvoiceItem
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): InvoiceItem
    {
        return DB::transaction(function () use ($payload): InvoiceItem {
            return $this->model->create($payload);
        });
    }

    public function update(string $id, array $payload): InvoiceItem
    {
        return DB::transaction(function () use ($id, $payload): InvoiceItem {
            $invoiceItem = $this->findOrFail($id);
            $invoiceItem->update($payload);

            return $invoiceItem->fresh() ?? $invoiceItem;
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
            ->with(['invoice.customer', 'salesOrderItem.product'])
            ->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $invoiceId = $this->normalizeString($filters['invoice_id'] ?? null);
        $salesOrderItemId = $this->normalizeString($filters['sales_order_item_id'] ?? null);
        $search = $this->normalizeString($filters['q'] ?? ($filters['search'] ?? null));

        return $query
            ->when($invoiceId !== null, fn (Builder $q): Builder => $q->where('invoice_id', $invoiceId))
            ->when($salesOrderItemId !== null, fn (Builder $q): Builder => $q->where('sales_order_item_id', $salesOrderItemId))
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $innerQuery) use ($search): void {
            $innerQuery
                ->whereHas('invoice', function (Builder $invoiceQuery) use ($search): void {
                    $invoiceQuery->where('invoice_number', 'like', "%{$search}%");
                })
                ->orWhereHas('salesOrderItem.product', function (Builder $productQuery) use ($search): void {
                    $productQuery->where('product_name', 'like', "%{$search}%");
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
}
