<?php 

namespace App\Modules\MasterData\DataProduk\DTOs;

use App\Modules\MasterData\DataProduk\Request\Units\StoreUnitRequest;
use App\Modules\MasterData\DataProduk\Request\Units\UpdateUnitRequest;

class UnitsDTO
{
    public function __construct(
        public readonly ?string $unit_code = null,
        public readonly ?string $unit_name = null,
        public readonly ?bool $is_active = true,
    ) {
    }

    public static function fromRequest(StoreUnitRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            unit_code: $validated['unit_code'],
            unit_name: $validated['unit_name'],
            is_active: $validated['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'unit_code' => $this->unit_code,
            'unit_name' => $this->unit_name,
            'is_active' => $this->is_active,
        ], static fn ($value): bool => $value !== null);
    }

    public static function fromUpdateRequest(UpdateUnitRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            unit_code: $validated['unit_code'] ?? null,
            unit_name: $validated['unit_name'] ?? null,
            is_active: $validated['is_active'] ?? null,
        );
    }
}