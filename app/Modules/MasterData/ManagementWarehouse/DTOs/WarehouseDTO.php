<?php

namespace App\Modules\MasterData\ManagementWarehouse\DTOs;
use App\Modules\MasterData\ManagementWarehouse\Request\StoreWarehouseRequest;
use App\Modules\MasterData\ManagementWarehouse\Request\UpdateWarehouseRequest;

class WarehouseDTO
{
    public function __construct(
        public readonly ?string $code = null,
        public readonly ?string $name = null,
        public readonly ?string $location = null,
    ) {
    }

    public static function fromRequest(StoreWarehouseRequest $request): self
    {
        $validated = $request->validated();
        return new self(
            code: $validated['code'],
            name: $validated['name'],
            location: $validated['location'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'name' => $this->name,
            'location' => $this->location,
        ], static fn ($value): bool => $value !== null);
    }

    public static function fromUpdateRequest(UpdateWarehouseRequest $request): self
    {
        $validated = $request->validated();
        return new self(
            code: $validated['code'] ?? null,
            name: $validated['name'] ?? null,
            location: $validated['location'] ?? null,
        );
    }
}
