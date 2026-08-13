<?php

return [
    'app_name' => env('ORGANIZATION_APP_NAME', env('APP_NAME', 'Summary')),
    'name' => env('ORGANIZATION_NAME', 'LPRL Sorong'),
    'short_name' => env('ORGANIZATION_SHORT_NAME', 'LPRL Sorong'),
    'logo_path' => env('ORGANIZATION_LOGO_PATH'),
    'favicon_path' => env('ORGANIZATION_FAVICON_PATH'),
    'address' => env('ORGANIZATION_ADDRESS'),
    'organizer_name' => env('ORGANIZATION_ORGANIZER_NAME'),
    'organizer_input_mode' => env('ORGANIZATION_ORGANIZER_INPUT_MODE', 'locked'),
    'monev_enabled' => env('MONEV_ENABLED', false),
];
