<?php

namespace App\Modules\Finance\VendorPayment\Repositories;

use App\Modules\Finance\VendorPayment\Models\VendorPayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VendorPaymentRepositories
{
    public function __construct(private readonly VendorPayment $model) {}

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

    public function findById(string $id): ?VendorPayment
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(string $id): VendorPayment
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): VendorPayment
    {
        return DB::transaction(function () use ($payload): VendorPayment {
            $payload['status'] = $payload['status'] ?? 'paid';

            return $this->model->create($this->preparePayload($payload));
        });
    }

    public function update(string $id, array $payload): VendorPayment
    {
        return DB::transaction(function () use ($id, $payload): VendorPayment {
            $vendorPayment = $this->findOrFail($id);
            $vendorPayment->update($this->preparePayload($payload));

            return $vendorPayment->fresh() ?? $vendorPayment;
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
    }

    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model->newQuery()->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $search = $this->normalizeString($filters['search'] ?? null);

        return $query
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $builder) use ($search): void {
            $builder->where('payment_number', 'like', "%{$search}%")
                ->orWhere('vendor_id', 'like', "%{$search}%")
                ->orWhere('vendor_invoice_number', 'like', "%{$search}%")
                ->orWhere('payment_type', 'like', "%{$search}%")
                ->orWhere('account_id', 'like', "%{$search}%");
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

    private function preparePayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_string($value)) {
                $payload[$key] = trim($value);
            }
        }

        $totalPaid = $payload['total_paid'] ?? null;
        $taxRate = $payload['tax_rate'] ?? 0;

        if ($totalPaid !== null) {
            $payload['tax_rate'] = (string) $taxRate;
            $payload['tax_amount'] = $this->calculateTaxAmount((string) $totalPaid, (string) $taxRate);
        }

        return array_filter($payload, static fn ($value): bool => $value !== null);
    }

    private function calculateTaxAmount(string $totalPaid, string $taxRate): string
    {
        $totalPaid = trim($totalPaid);
        $taxRate = trim($taxRate);

        if ($totalPaid === '' || $taxRate === '') {
            return '0.00';
        }

        if ((float) $taxRate <= 0) {
            return '0.00';
        }

        if (function_exists('bcmul') && function_exists('bcdiv')) {
            $rate = (string) bcdiv($taxRate, '100', 6);

            return (string) bcmul($totalPaid, $rate, 2);
        }

        $taxAmount = round(((float) $totalPaid) * (((float) $taxRate) / 100), 2);

        return number_format($taxAmount, 2, '.', '');
    }
}
