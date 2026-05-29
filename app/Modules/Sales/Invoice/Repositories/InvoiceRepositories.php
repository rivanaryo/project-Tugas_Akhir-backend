<?php 

namespace App\Modules\Sales\Invoice\Repositories;

use App\Modules\Sales\Invoice\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceRepositories
{
    public function __construct(private readonly Invoice $model)
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

    public function findById(string $id): ?Invoice
    {
        return $this->baseQuery()->find($id)?->load(['items.salesOrderItem.product']);
    }

    public function findOrFail(string $id): Invoice
    {
        return $this->baseQuery()->findOrFail($id)->load(['items.salesOrderItem.product']);
    }

    public function create(array $payload): Invoice
    {
        return DB::transaction(function () use ($payload): Invoice {
            $payload['subtotal'] = $payload['subtotal'] ?? 0;
            $payload['tax_total'] = $payload['tax_total'] ?? 0;
            $payload['grand_total'] = $payload['grand_total'] ?? 0;

            return $this->model->create($payload);
        });
    }

    public function update(string $id, array $payload): Invoice
    {
        return DB::transaction(function () use ($id, $payload): Invoice {
            $invoice = $this->findOrFail($id);
            $invoice->update($payload);
            return $invoice->fresh() ?? $invoice;
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
            ->with(['customer', 'items.salesOrderItem.product'])
            ->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $customerId = $this->normalizeString($filters['customer_id'] ?? null);
        $status = $this->normalizeString($filters['status'] ?? null);
        $invoiceDateFrom = $this->normalizeString($filters['invoice_date_from'] ?? null);
        $invoiceDateTo = $this->normalizeString($filters['invoice_date_to'] ?? null);
        $search = $this->normalizeString($filters['q'] ?? ($filters['search'] ?? null));

        return $query
            ->when($customerId !== null, fn (Builder $q): Builder => $q->where('customer_id', $customerId))
            ->when($status !== null, fn (Builder $q): Builder => $q->where('status', $status))
            ->when($invoiceDateFrom !== null, fn (Builder $q): Builder => $q->whereDate('invoice_date', '>=', $invoiceDateFrom))
            ->when($invoiceDateTo !== null, fn (Builder $q): Builder => $q->whereDate('invoice_date', '<=', $invoiceDateTo))
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $innerQuery) use ($search): void {
            $innerQuery
                ->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('customer', function (Builder $customerQuery) use ($search): void {
                    $customerQuery->where('customer_name', 'like', "%{$search}%");
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
