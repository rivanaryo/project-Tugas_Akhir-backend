<?php 

namespace App\Modules\MasterData\ManagementAccount\Repositories;

use App\Modules\MasterData\ManagementAccount\Models\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AccountRepositories
{
    public function __construct(private readonly Account $model)
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

    public function findById(string $id): ?Account
    {
        return $this->baseQuery()->find($id);
    }

    public function findOrFail(string $id): Account
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $payload): Account
    {
        return DB::transaction(function () use ($payload): Account {
            return $this->model->create($payload);
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(fn (): bool => (bool) $this->findOrFail($id)->delete());
    }

    public function update(string $id, array $payload): Account
    {
        return DB::transaction(function () use ($id, $payload): Account {
            $account = $this->findOrFail($id);
            $account->update($payload);

            return $account->fresh() ?? $account;
        });
    }

    private function baseQuery(array $filters = []): Builder
    {
        $query = $this->model->newQuery();

        return $this->applyFilters($query, $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $search = $this->normalizeString($filters['search'] ?? null);

        return $query->when($search !== null, fn (Builder $q): Builder => $this->applySearch($q, $search));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search): void {
            $q->where('account_name', 'like', "%{$search}%")
                ->orWhere('account_number', 'like', "%{$search}%")
                ->orWhere('bank_name', 'like', "%{$search}%");
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
