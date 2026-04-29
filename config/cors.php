<?php

$csv = static function (?string $value, array $fallback = []): array {
    if ($value === null || trim($value) === '') {
        return $fallback;
    }

    return array_values(array_filter(array_map('trim', explode(',', $value))));
};

$envBool = static function (string $key, bool $default = false): bool {
    return filter_var(env($key, $default), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
};

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => $csv(env('CORS_ALLOWED_METHODS'), ['*']),

    'allowed_origins' => $csv(env('CORS_ALLOWED_ORIGINS'), ['http://localhost:5173']),

    'allowed_origins_patterns' => $csv(env('CORS_ALLOWED_ORIGIN_PATTERNS'), []),

    'allowed_headers' => $csv(env('CORS_ALLOWED_HEADERS'), ['*']),

    'exposed_headers' => $csv(env('CORS_EXPOSED_HEADERS'), []),

    'max_age' => (int) env('CORS_MAX_AGE', 3600),

    // Keep false for JWT bearer token in Authorization header.
    // Set true only if auth uses cross-site cookies.
    'supports_credentials' => $envBool('CORS_SUPPORTS_CREDENTIALS', false),

];
