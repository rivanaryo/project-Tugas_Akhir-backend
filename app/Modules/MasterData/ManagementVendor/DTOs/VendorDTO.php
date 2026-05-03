<?php

namespace App\Modules\MasterData\ManagementVendor\DTOs;
use App\Modules\MasterData\ManagementVendor\Request\StoreVendorRequest;
use App\Modules\MasterData\ManagementVendor\Request\UpdateVendorRequest;

class VendorDTO
{
    public function __construct(
        public readonly ?string $vendor_name = null,
        public readonly ?string $contact_person = null,
        public readonly ?string $phone_number = null,
        public readonly ?string $email = null,
        public readonly ?string $address = null,
    ) {
    }

    public static function fromRequest(StoreVendorRequest $request): self
    {
        $validated = $request->validated();
        return new self(
            vendor_name:    $validated['vendor_name'],
            contact_person: $validated['contact_person'] ?? null,
            phone_number:   $validated['phone_number'] ?? null,
            email:          $validated['email'] ?? null,
            address:        $validated['address'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'vendor_name' => $this->vendor_name,
            'contact_person' => $this->contact_person,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'address' => $this->address,
        ], static fn ($value): bool => $value !== null);
    }

    public static function fromUpdateRequest(UpdateVendorRequest $request): self
    {
        $validated = $request->validated();
        return new self(
            vendor_name: $validated['vendor_name'] ?? null,
            contact_person: $validated['contact_person'] ?? null,
            phone_number: $validated['phone_number'] ?? null,
            email: $validated['email'] ?? null,
            address: $validated['address'] ?? null,
        );
    }
}