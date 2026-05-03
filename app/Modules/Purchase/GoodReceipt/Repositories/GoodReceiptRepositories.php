<?php
namespace App\Modules\Purchase\GoodReceipt\Repositories;

use App\Modules\Purchase\GoodReceipt\Models\GoodReceipt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GoodReceiptRepositories
{
    public function __construct(
        private readonly GoodReceipt $model
    ) {
    }
    
    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model
            ->newQuery()
            ->with([
                'purchaseOrder',
                'vendor',
                'items.product.unit',
            ])
            ->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $grNumber = $this->normalizeString($filters['gr_number'] ?? null);
        $purchaseOrderId = $this->normalizeString($filters['purchase_order_id'] ?? null);
        $vendorId = $this->normalizeString($filters['vendor_id'] ?? null);
        $status = $this->normalizeString($filters['status'] ?? null);
        $receiptDate = $this->normalizeString($filters['receipt_date'] ?? null);
        $search = $this->normalizeString($filters['q'] ?? ($filters['search'] ?? null));

        return $query
            ->when($grNumber !== null, fn (Builder $q): Builder => $q->where('gr_number', $grNumber))
            ->when($purchaseOrderId !== null, fn (Builder $q): Builder => $q->where('purchase_order_id', $purchaseOrderId))
            ->when($vendorId !== null, fn (Builder $q): Builder => $q->where('vendor_id', $vendorId))
            ->when($status !== null, fn (Builder $q): Builder => $q->where('status', $status))
            ->when($receiptDate !== null, fn (Builder $q): Builder => $q->whereDate('receipt_date', $receiptDate))
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $innerQuery) use ($search): void {
            $innerQuery
                ->where('gr_number', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhereHas('purchaseOrder', function (Builder $purchaseOrderQuery) use ($search): void {
                    $purchaseOrderQuery->where('po_number', 'like', "%{$search}%");
                })
                ->orWhereHas('vendor', function (Builder $vendorQuery) use ($search): void {
                    $vendorQuery->where('vendor_name', 'like', "%{$search}%");
                })
                ->orWhereHas('items.product', function (Builder $productQuery) use ($search): void {
                    $productQuery->where('product_name', 'like', "%{$search}%");
                });
        });
    }

    private function normalizeString(mixed $string): ?string
    {
        if ($string === null) {
            return null;
        }

        $string = trim((string) $string);

        return $string === '' ? null : $string;
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

    public function findById(int|string $id): ?GoodReceipt
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(int|string $id): GoodReceipt
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): GoodReceipt
    {
        return DB::transaction(function () use ($payload): GoodReceipt {
            return $this->model->create($this->preparePayload($payload));
        });
    }

    public function update(int|string $id, array $payload): GoodReceipt
    {
        return DB::transaction(function () use ($id, $payload): GoodReceipt {
            $goodReceipt = $this->findOrFail($id);
            $goodReceipt->update($this->preparePayload($payload));
            return $goodReceipt->fresh() ?? $goodReceipt;
        });
    }

    public function delete(int|string $id): void
    {
        $this->findOrFail($id)->delete();
    }

    private function preparePayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_string($value)) {
                $payload[$key] = trim($value);
            }
        }

        return array_filter($payload, static fn ($value): bool => $value !== null);
    }

}
