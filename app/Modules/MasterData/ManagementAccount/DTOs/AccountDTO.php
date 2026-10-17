<?php 
namespace App\Modules\MasterData\ManagementAccount\DTOs;
use App\Modules\MasterData\ManagementAccount\Request\StoreAccountRequest;
use App\Modules\MasterData\ManagementAccount\Request\UpdateAccountRequest;


class AccountDTO
{
    public readonly string $account_name;
    public readonly string $account_number;
    public readonly ?string $type;
    public readonly ?float $balance;
    public readonly ?string $bank_name;
    public readonly ?bool $is_active;

    public function __construct(array $data)
    {
        $this->account_name = $data['account_name'];
        $this->account_number = $data['account_number'];
        $this->type = $data['type'] ?? null;
        $this->balance = isset($data['balance']) ? (float) $data['balance'] : null;
        $this->bank_name = $data['bank_name'] ?? null;
        $this->is_active = $data['is_active'] ?? null;
    }

    public static function fromRequest(StoreAccountRequest $request): self
    {
        $validated = $request->validated();

        return new self([
            'account_name' => $validated['account_name']?? '',
            'account_number' => $validated['account_number']?? '',
            'type' => $validated['type'] ?? 'bank',
            'balance' => $validated['balance'] ?? 0,
            'bank_name' => $validated['bank_name'] ?? '',
            'is_active' => $validated['is_active'] ?? null,
        ]);  
    }

    public static function fromRequestUpdate(UpdateAccountRequest $request): self
    {
        $validated = $request->validated();

        return new self([
            'account_name' => $validated['account_name']?? '',
            'account_number' => $validated['account_number']?? '',
            'type' => $validated['type'] ?? 'bank',
            'balance' => $validated['balance'] ?? 0,
            'bank_name' => $validated['bank_name'] ?? '',
            'is_active' => $validated['is_active'] ?? null,
        ]);
    }

    public function toArray(bool $preserveNull = false): array
    {
        $payload = [
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'type' => $this->type,
            'balance' => $this->balance,
            'bank_name' => $this->bank_name,
            'is_active' => $this->is_active,
        ];

        if ($preserveNull) {
            return $payload;
        }

        return array_filter($payload, static fn ($value): bool => $value !== null);
    }
}
