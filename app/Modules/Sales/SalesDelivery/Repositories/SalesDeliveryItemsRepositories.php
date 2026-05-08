<?php 
namespace App\Modules\Sales\SalesDelivery\Repositories;
use App\Modules\Sales\SalesDelivery\Reference\Models\SalesDeliveryItems;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SalesDeliveryItemsRepositories
{
    public function __construct(private readonly SalesDeliveryItems $model)
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

    public function findById(string $id): ?SalesDeliveryItems
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(string $id): SalesDeliveryItems
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): SalesDeliveryItems
    {
        return DB::transaction(function () use ($payload): SalesDeliveryItems {
            return $this->model->create($payload);
        });
    }

    public function update(string $id, array $payload): SalesDeliveryItems
    {
        return DB::transaction(function () use ($id, $payload): SalesDeliveryItems {
            $salesDeliveryItems = $this->findOrFail($id);
            $salesDeliveryItems->update($payload);
            return $salesDeliveryItems->fresh() ?? $salesDeliveryItems;
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
    }

    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model->newQuery();

        if (isset($filters['sales_delivery_id'])) {
            $query->where('sales_delivery_id', $filters['sales_delivery_id']);
        }

        if (isset($filters['sales_order_item_id'])) {
            $query->where('sales_order_item_id', $filters['sales_order_item_id']);
        }

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['q']) || isset($filters['search'])) {
            $search = $filters['q'] ?? $filters['search'];
            $query = $this->applySearch($query, $search);
        }

        return $query;
    }
    
    private function applySearch(Builder $query, string $search): Builder
    {
        $search = $this->normalizeString($search);
    
        return $query->where(function (Builder $q) use ($search) {
            $q->where('id', 'like', "%{$search}%")
                ->orWhere('sales_delivery_id', 'like', "%{$search}%")
                ->orWhere('sales_order_item_id', 'like', "%{$search}%");
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