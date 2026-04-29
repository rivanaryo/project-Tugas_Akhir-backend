<?php

$envBool = static function (string $key, bool $default = false): bool {
    return filter_var(env($key, $default), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
};

$envNullable = static function (string $key, mixed $fallback = null): mixed {
    $value = env($key, $fallback);

    if (! is_string($value)) {
        return $value;
    }

    $normalized = trim($value);

    if ($normalized === '' || strtolower($normalized) === 'null') {
        return null;
    }

    return $normalized;
};

return [
    'secret' => env('JWT_SECRET', env('APP_KEY')),
    'issuer' => env('JWT_ISSUER', env('APP_NAME', 'laravel')),
    'ttl' => (int) env('JWT_TTL', 60),
    'algo' => env('JWT_ALGO', 'HS256'),
    'cookie_name' => env('JWT_COOKIE_NAME', 'access_token'),
    'cookie_path' => env('JWT_COOKIE_PATH', '/'),
    'cookie_domain' => $envNullable('JWT_COOKIE_DOMAIN', env('SESSION_DOMAIN')),
    'cookie_secure' => $envBool('JWT_COOKIE_SECURE', env('APP_ENV') === 'production'),
    'cookie_http_only' => $envBool('JWT_COOKIE_HTTP_ONLY', true),
    'cookie_same_site' => env('JWT_COOKIE_SAME_SITE', 'lax'),
    'revoke_enabled' => $envBool('JWT_REVOKE_ENABLED', true),
    'revoke_key_prefix' => env('JWT_REVOKE_KEY_PREFIX', 'jwt:revoked:'),
];