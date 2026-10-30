<?php 
namespace App\Modules\Sales\SalesQuotation\Repositories;
use App\Modules\Sales\SalesQuotation\Models\SalesQuotation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;



class SalesQuotationRepositories
{
    public function __construct(private readonly SalesQuotation $model)
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

    public function findById(string $id): ?SalesQuotation
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(string $id): SalesQuotation
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): SalesQuotation
    {
        return DB::transaction(function () use ($payload): SalesQuotation {
            
            return $this->model->create($payload);
        });
    }

    public function update(string $id, array $payload): SalesQuotation
    {
        return DB::transaction(function () use ($id, $payload): SalesQuotation {
            $salesQuotation = $this->findOrFail($id);
            $salesQuotation->update($payload);
            return $salesQuotation->fresh() ?? $salesQuotation;
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
            ->with(['customer', 'items.product'])
            ->latest('created_at');

        return $this->applyFilters($query, $filters);

    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $customerId = $this->normalizeString($filters['customer_id'] ?? null);
        $status = $this->normalizeString($filters['status'] ?? null);
        $quotationDateFrom = $this->normalizeString($filters['quotation_date_from'] ?? null);
        $quotationDateTo = $this->normalizeString($filters['quotation_date_to'] ?? null);
        $search = $this->normalizeString($filters['q'] ?? ($filters['search'] ?? null));

        return $query
            ->when($customerId !== null, fn (Builder $q): Builder => $q->where('customer_id', $customerId))
            ->when($status !== null, fn (Builder $q): Builder => $q->where('status', $status))
            ->when($quotationDateFrom !== null && $quotationDateTo !== null, fn (Builder $q): Builder => $q->whereBetween('quotation_date', [$quotationDateFrom, $quotationDateTo]))
            ->when($quotationDateFrom !== null && $quotationDateTo === null, fn (Builder $q): Builder => $q->where('quotation_date', '>=', $quotationDateFrom))
            ->when($quotationDateTo !== null && $quotationDateFrom === null, fn (Builder $q): Builder => $q->where('quotation_date', '<=', $quotationDateTo))
            ->when($search !== null, fn (Builder $q): Builder => 
                $q->where(function (Builder $query) use ($search) {
                    return $query
                        ->where('quotation_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn (Builder $q) => 
                            $q->where('customer_name', 'like', "%{$search}%")
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
