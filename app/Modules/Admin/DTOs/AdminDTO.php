<?php

namespace App\Modules\Admin\DTOs;

use App\Modules\Admin\Request\StoreAdminRequest;
use App\Modules\Admin\Request\UpdateAdminRequest;

class AdminDTO
{
    public function __construct(
        public readonly ?string $email = null,
        public readonly ?string $password = null,
        public readonly ?string $role = null,
    ) {
    }

    public static function fromRequest(StoreAdminRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            email: $validated['email'],
            password: $validated['password'],
            role: $validated['role'],
        );
    }

    public static function fromUpdateRequest(UpdateAdminRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            email: $validated['email'] ?? null,
            password: $validated['password'] ?? null,
            role: $validated['role'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
        ], static fn ($value): bool => $value !== null);
    }
}

