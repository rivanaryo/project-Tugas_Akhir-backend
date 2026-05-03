<?php

namespace App\Modules\MasterData\ManagementPricelist\DTOs;

use App\Modules\MasterData\ManagementPricelist\Request\StorePricelistRequest;
use App\Modules\MasterData\ManagementPricelist\Request\UpdatePricelistRequest;

class PricelistDTO
{
    public function __construct(
        public readonly ?string $vendor_id = null,
        public readonly ?string $product_id = null,
        public readonly ?float  $price = null,
        public readonly ?string $currency = null,
        public readonly ?bool   $is_active = null,
    ) {}

    public static function fromRequest(StorePricelistRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            vendor_id:  $validated['vendor_id'],
            product_id: $validated['product_id'],
            price:      $validated['price'],
            currency:   $validated['currency'],
            is_active:  $validated['is_active'] ?? true,
        );
    }

    public static function fromUpdateRequest(UpdatePricelistRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            vendor_id:  $validated['vendor_id'] ?? null,
            product_id: $validated['product_id'] ?? null,
            price:      $validated['price'] ?? null,
            currency:   $validated['currency'] ?? null,
            is_active:  $validated['is_active'] ?? null,
        );
    }

    public function toArray(bool $preserveNull = false): array
    {
        $payload = [
            'vendor_id'  => $this->vendor_id,
            'product_id' => $this->product_id,
            'price'      => $this->price,
            'currency'   => $this->currency,
            'is_active'  => $this->is_active,
        ];

        if ($preserveNull) {
            return $payload;
        }

        return array_filter($payload, static fn ($value): bool => $value !== null);
    }
}
