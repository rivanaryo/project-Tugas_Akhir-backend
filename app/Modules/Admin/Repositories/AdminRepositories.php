<?php

namespace App\Modules\Admin\Repositories;

use App\Modules\Admin\Models\Admin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminRepositories
{
    public function __construct(
        private readonly Admin $model
    ) {
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

    public function create(array $payload): Admin
    {
        return DB::transaction(function () use ($payload): Admin {
            return $this->model->create($this->preparePayload($payload));
        });
    }

    public function findById(int|string $id): ?Admin
    {
        return $this->baseQuery()->find($id);
    }

    public function findByEmail(string $email): ?Admin
    {
        return $this->baseQuery()->where('email', strtolower(trim($email)))->first();
    }

    public function findOrFail(int|string $id): Admin
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function update(int|string $id, array $payload): Admin
    {
        return DB::transaction(function () use ($id, $payload): Admin {
            $admin = $this->findOrFail($id);
            $admin->update($this->preparePayload($payload));

            return $admin->fresh() ?? $admin;
        });
    }

    public function delete(int|string $id): bool
    {
        return DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
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
        $role = $this->normalizeString($filters['role'] ?? null);

        return $query
            ->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search))
            ->when($role !== null, fn (Builder $q): Builder => $q->where('role', $role));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where('email', 'like', "%{$search}%");
    }

    private function preparePayload(array $payload): array
    {
        if (array_key_exists('email', $payload) && $payload['email'] !== null) {
            $payload['email'] = strtolower(trim((string) $payload['email']));
        }

        return array_filter($payload, static fn ($value): bool => $value !== null);
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
