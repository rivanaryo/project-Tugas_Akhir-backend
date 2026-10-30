<?php 

namespace App\Modules\Sales\SalesQuotation\Repositories;
use App\Modules\Sales\SalesQuotation\Reference\Models\SalesQuotationItems;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SalesQuotationItemsRepositories
{
    public function __construct(private readonly SalesQuotationItems $model)
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

    public function findById(string $id): ?SalesQuotationItems
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(string $id): SalesQuotationItems
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): SalesQuotationItems
    {
        return DB::transaction(function () use ($payload): SalesQuotationItems {
            return $this->model->create($payload);
        });
    }

    public function update(string $id, array $payload): SalesQuotationItems
    {
        return DB::transaction(function () use ($id, $payload): SalesQuotationItems {
            $salesQuotationItems = $this->findOrFail($id);
            $salesQuotationItems->update($payload);
            return $salesQuotationItems->fresh() ?? $salesQuotationItems;
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
    }

    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model->newQuery();

        if (isset($filters['sales_quotation_id'])) {
            $query->where('sales_quotation_id', $filters['sales_quotation_id']);
        }

        // Add more filters as needed

        return $query;
    }
}
