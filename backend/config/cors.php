<?php

return [
    'paths'                => ['api/*'],
    'allowed_methods'      => ['*'],
    'allowed_origins' => [
        'https://central.monarcacore.com',
        'https://delivery.monarcacore.com',
        env('FRONTEND_URL', 'http://localhost:5173'),
        'http://localhost:5173',
        'http://localhost:5174',
        'http://localhost:5175',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
        'http://127.0.0.1:5175',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers'      => ['*'],
    'exposed_headers'      => [],
    'max_age'              => 0,
    'supports_credentials' => false,
];
