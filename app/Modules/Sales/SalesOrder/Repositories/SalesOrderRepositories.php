<?php 
namespace App\Modules\Sales\SalesOrder\Repositories;
use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;



class SalesOrderRepositories
{
    public function __construct(private readonly SalesOrder $model)
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

    public function findById(string $id): ?SalesOrder
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(string $id): SalesOrder
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): SalesOrder
    {
        return DB::transaction(function () use ($payload): SalesOrder {
            
            return $this->model->create($payload);
        });
    }

    public function update(string $id, array $payload): SalesOrder
    {
        return DB::transaction(function () use ($id, $payload): SalesOrder {
            $salesOrder = $this->findOrFail($id);
            $salesOrder->update($payload);
            return $salesOrder->fresh() ?? $salesOrder;
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
    }

    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model->newQuery();

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['order_date_from']) && isset($filters['order_date_to'])) {
            $query->whereBetween('order_date', [$filters['order_date_from'], $filters['order_date_to']]);
        } elseif (isset($filters['order_date_from'])) {
            $query->where('order_date', '>=', $filters['order_date_from']);
        } elseif (isset($filters['order_date_to'])) {
            $query->where('order_date', '<=', $filters['order_date_to']);
        }

        if (isset($filters['q']) || isset($filters['search'])) {
            $search = $filters['q'] ?? $filters['search'];
            $query->where(function (Builder $query) use ($search) {
                return $query
                    ->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn (Builder $q) => 
                        $q->where('name', 'like', "%{$search}%")
                    );
            });
        }

        
        return $this->applyFilters($query, $filters);

    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $customerId = $this->normalizeString($filters['customer_id'] ?? null);
        $status = $this->normalizeString($filters['status'] ?? null);
        $orderDateFrom = $this->normalizeString($filters['order_date_from'] ?? null);
        $orderDateTo = $this->normalizeString($filters['order_date_to'] ?? null);
        $search = $this->normalizeString($filters['q'] ?? ($filters['search'] ?? null));

        return $query
            ->when($customerId !== null, fn (Builder $q): Builder => $q->where('customer_id', $customerId))
            ->when($status !== null, fn (Builder $q): Builder => $q->where('status', $status))
            ->when($orderDateFrom !== null && $orderDateTo !== null, fn (Builder $q): Builder => $q->whereBetween('order_date', [$orderDateFrom, $orderDateTo]))
            ->when($orderDateFrom !== null && $orderDateTo === null, fn (Builder $q): Builder => $q->where('order_date', '>=', $orderDateFrom))
            ->when($orderDateTo !== null && $orderDateFrom === null, fn (Builder $q): Builder => $q->where('order_date', '<=', $orderDateTo))
            ->when($search !== null, fn (Builder $q): Builder => 
                $q->where(function (Builder $query) use ($search) {
                    return $query
                        ->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn (Builder $q) => 
                            $q->where('name', 'like', "%{$search}%")
                        );
                })
            );
    }

    private function normalizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }


}