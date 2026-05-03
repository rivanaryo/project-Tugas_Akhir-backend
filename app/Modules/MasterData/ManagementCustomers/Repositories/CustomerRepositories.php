<?php

namespace App\Modules\MasterData\ManagementCustomers\Repositories;

use App\Modules\MasterData\ManagementCustomers\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerRepositories
{
    public function __construct(private readonly Customer $model)
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

    public function findById(string $id): ?Customer
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(string $id): Customer
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): Customer
    {
        return DB::transaction(function () use ($payload): Customer {
            return $this->model->create($payload);
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
    }

    public function update(string $id, array $payload): Customer
    {
        return DB::transaction(function () use ($id, $payload): Customer {
            $customer = $this->findOrFail($id);
            $customer->update($payload);

            return $customer->fresh() ?? $customer;
        });
    }

    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model
            ->newQuery()
            ->latest('created_at');

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $search = $this->normalizeString($filters['search'] ?? null);
        $customerName = $this->normalizeString($filters['customer_name'] ?? null);

        return $query
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search))
            ->when($customerName !== null, fn (Builder $q): Builder => $q->where('customer_name', 'like', "%{$customerName}%"));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $builder) use ($search): void {
            $builder->where('customer_name', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
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
