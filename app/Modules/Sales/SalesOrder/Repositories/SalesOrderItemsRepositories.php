<?php 

namespace App\Modules\Sales\SalesOrder\Repositories;
use App\Modules\Sales\SalesOrder\Reference\Models\SalesOrderItems;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SalesOrderItemsRepositories
{
    public function __construct(private readonly SalesOrderItems $model)
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

    public function findById(string $id): ?SalesOrderItems
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(string $id): SalesOrderItems
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): SalesOrderItems
    {
        return DB::transaction(function () use ($payload): SalesOrderItems {
            return $this->model->create($payload);
        });
    }

    public function update(string $id, array $payload): SalesOrderItems
    {
        return DB::transaction(function () use ($id, $payload): SalesOrderItems {
            $salesOrderItems = $this->findOrFail($id);
            $salesOrderItems->update($payload);
            return $salesOrderItems->fresh() ?? $salesOrderItems;
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
    }

    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model->newQuery();

        if (isset($filters['sales_order_id'])) {
            $query->where('sales_order_id', $filters['sales_order_id']);
        }

        // Add more filters as needed

        return $query;
    }
}
