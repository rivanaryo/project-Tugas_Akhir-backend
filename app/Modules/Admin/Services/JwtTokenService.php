<?php

namespace App\Modules\Admin\Services;

use App\Modules\Admin\Models\Admin;
use App\Modules\Admin\Repositories\AdminRepositories;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class JwtTokenService
{
    public function __construct(private readonly AdminRepositories $adminRepositories)
    {
    }

    public function issueToken(Admin $admin): string
    {
        $now = time();
        $ttlInSeconds = $this->getTtlInSeconds();

        $payload = [
            'iss' => config('jwt.issuer'),
            'sub' => $admin->getKey(),
            'email' => $admin->email,
            'role' => $admin->role,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttlInSeconds,
            'jti' => (string) Str::uuid(),
        ];

        return JWT::encode($payload, $this->signingKey(), config('jwt.algo'));
    }

    public function decodeToken(string $token): array
    {
        $decoded = JWT::decode($token, new Key($this->signingKey(), config('jwt.algo')));

        return (array) $decoded;
    }

    public function findUserByEmail(string $email): ?Admin
    {
        return $this->adminRepositories->findByEmail($email);
    }

    public function findUserById(int|string|null $id): ?Admin
    {
        if ($id === null || $id === '') {
            return null;
        }

        return $this->adminRepositories->findById((string) $id);
    }

    public function getTtlInSeconds(): int
    {
        return (int) config('jwt.ttl') * 60;
    }

    public function revokeTokenPayload(array $payload): void
    {
        if (! (bool) config('jwt.revoke_enabled', true)) {
            return;
        }

        $jti = (string) ($payload['jti'] ?? '');
        $exp = (int) ($payload['exp'] ?? 0);

        if ($jti === '' || $exp <= time()) {
            return;
        }

        $ttlInSeconds = max(1, $exp - time());

        Cache::put($this->revokedCacheKey($jti), true, now()->addSeconds($ttlInSeconds));
    }

    public function isTokenRevoked(array $payload): bool
    {
        if (! (bool) config('jwt.revoke_enabled', true)) {
            return false;
        }

        $jti = (string) ($payload['jti'] ?? '');

        if ($jti === '') {
            return false;
        }

        return Cache::has($this->revokedCacheKey($jti));
    }

    private function signingKey(): string
    {
        $secret = (string) config('jwt.secret');

        if (str_starts_with($secret, 'base64:')) {
            $decoded = base64_decode(substr($secret, 7), true);

            return $decoded !== false ? $decoded : $secret;
        }

        return $secret;
    }

    private function revokedCacheKey(string $jti): string
    {
        return (string) config('jwt.revoke_key_prefix', 'jwt:revoked:').$jti;
    }
}