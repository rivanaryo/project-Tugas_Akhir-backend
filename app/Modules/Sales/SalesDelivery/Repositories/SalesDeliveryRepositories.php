<?php 

namespace App\Modules\Sales\SalesDelivery\Repositories;

use App\Modules\Sales\SalesDelivery\Models\SalesDelivery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SalesDeliveryRepositories
{
    public function __construct(private readonly SalesDelivery $model)
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

    public function findById(string $id): ?SalesDelivery
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(string $id): SalesDelivery
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): SalesDelivery
    {
        return DB::transaction(function () use ($payload): SalesDelivery {
            return $this->model->create($payload);
        });
    }

    public function update(string $id, array $payload): SalesDelivery
    {
        return DB::transaction(function () use ($id, $payload): SalesDelivery {
            $salesDelivery = $this->findOrFail($id);
            $salesDelivery->update($payload);
            return $salesDelivery->fresh() ?? $salesDelivery;
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
    }


    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model->newQuery()
            ->with(['customer', 'salesOrder', 'items.salesOrderItem.product'])
            ->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $search = $this->normalizeString($filters['q'] ?? ($filters['search'] ?? null));
        $customerId = $this->normalizeString($filters['customer_id'] ?? null);
        $salesOrderId = $this->normalizeString($filters['sales_order_id'] ?? null);

        return $query
            ->when($customerId, fn (Builder $q) => $q->where('customer_id', $customerId))
            ->when($salesOrderId, fn (Builder $q) => $q->where('sales_order_id', $salesOrderId))
            ->when($search, fn (Builder $q) => $this->applySearch($q, $search));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('delivery_number', 'like', "%{$search}%")
                ->orWhereHas('customer', fn (Builder $sub) => 
                    $sub->where('customer_name', 'like', "%{$search}%")
                )->orWhereHas('salesOrder', fn (Builder $sub) => 
                    $sub->where('order_number', 'like', "%{$search}%")
                );
        });
    }

    private function normalizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

}
