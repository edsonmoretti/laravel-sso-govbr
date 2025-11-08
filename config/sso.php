<?php

return [
    'govbr' => [
        'url_provider' => env('GOVBR_URL_PROVIDER'),
        'url_service' => env('GOVBR_URL_SERVICE'),
        'redirect_uri' => env('GOVBR_REDIRECT_URI'),
        'scopes' => env('GOVBR_SCOPES'),
        'client_id' => env('GOVBR_CLIENT_ID'),
        'client_secret' => env('GOVBR_CLIENT_SECRET'),
        'logout_uri' => env('GOVBR_LOGOUT_URI'),
        'auth_type' => env('GOVBR_AUTH_TYPE'),
    ],
];
