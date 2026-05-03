<?php 
namespace App\Modules\MasterData\ManagementCustomers\DTOs;

use App\Modules\MasterData\ManagementCustomers\Request\StoreCustomerRequest;
use App\Modules\MasterData\ManagementCustomers\Request\UpdateCustomerRequest;


class CustomerDTO
{
    public function __construct(
        public readonly ?string $customer_name = null,
        public readonly ?string $contact_person = null,
        public readonly ?string $phone_number = null,
        public readonly ?string $email = null,
        public readonly ?string $address = null,
        public readonly ?bool $is_active = null,
    ) {
    }

    public static function fromRequest(StoreCustomerRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            customer_name: $validated['customer_name'] ?? '',
            contact_person: $validated['contact_person'] ?? '',
            phone_number: $validated['phone_number'] ?? '',
            email: $validated['email'] ?? '',
            address: $validated['address'] ?? '',
            is_active: $validated['is_active'] ?? true,
        );
    }

    public function toArray(bool $preserveNull = false): array
    {
        $payload = [
            'customer_name' => $this->customer_name,
            'contact_person' => $this->contact_person,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'address' => $this->address,
            'is_active' => $this->is_active,
        ];

        if ($preserveNull) {
            return $payload;
        }

        return array_filter($payload, static fn ($value): bool => $value !== null);
    }

    public static function fromUpdateRequest(UpdateCustomerRequest $request): self
    {
        $validated = $request->validated();
        return new self(
            customer_name: $validated['customer_name'] ?? '',
            contact_person: $validated['contact_person'] ?? '',
            phone_number: $validated['phone_number'] ?? '',
            email: $validated['email'] ?? '',
            address: $validated['address'] ?? '',
            is_active: $validated['is_active'] ?? true,
        );
    }
}