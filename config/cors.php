<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    // Support a single origin or a comma-separated list in .env: CORS_ALLOWED_ORIGIN=http://localhost:5173,http://localhost:3000
    'allowed_origins' => (static function () {
        $val = env('CORS_ALLOWED_ORIGIN', '*');
        if ($val === '*') return ['*'];
        $parts = array_map('trim', explode(',', $val));
        return $parts;
    })(),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
