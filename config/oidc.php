<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OIDC / SSO Placeholder
    |--------------------------------------------------------------------------
    |
    | Stub configuration for an environment-driven OIDC login flow. When
    | disabled (default), the application uses Laravel Breeze auth and does
    | not require an external identity provider for local runs or tests.
    |
    */

    'enabled' => (bool) env('OIDC_ENABLED', false),

    'client_id' => env('OIDC_CLIENT_ID'),

    'client_secret' => env('OIDC_CLIENT_SECRET'),

    'redirect_uri' => env('OIDC_REDIRECT_URI'),

    'issuer' => env('OIDC_ISSUER'),

    'authorization_endpoint' => env('OIDC_AUTHORIZATION_ENDPOINT'),

    'token_endpoint' => env('OIDC_TOKEN_ENDPOINT'),

    'userinfo_endpoint' => env('OIDC_USERINFO_ENDPOINT'),

];
