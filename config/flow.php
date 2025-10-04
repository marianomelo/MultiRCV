<?php

return [
    'env' => env('FLOW_ENV', 'sandbox'),
    'api_key' => env('FLOW_API_KEY'),
    'secret_key' => env('FLOW_SECRET_KEY'),
    'url_confirmation' => env('FLOW_URL_CONFIRMATION'),
    'url_return' => env('FLOW_URL_RETURN'),

    'api_url' => env('FLOW_ENV') === 'production'
        ? 'https://www.flow.cl/api'
        : 'https://sandbox.flow.cl/api',
];
