<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Available Scanner
    |--------------------------------------------------------------------------
    |
    | All scanner modules that are configured for the use with SIWECOS are
    | defined here.
    |
 */
    'available_scanners' => [
        // SIWECOS/HSHS-DOMXSS-Scanner: https://github.com/SIWECOS/HSHS-DOMXSS-Scanner
        'HEADER' => env('SCANNER_HEADER_URL'),
        // SIWECOS/HSHS-DOMXSS-Scanner: https://github.com/SIWECOS/HSHS-DOMXSS-Scanner
        'DOMXSS' => env('SCANNER_DOMXSS_URL'),
        // SIWECOS/InfoLeak-Scanner: https://github.com/SIWECOS/InfoLeak-Scanner
        'INFOLEAK' => env('SCANNER_INFOLEAK_URL'),
        // SIWECOS/WS-TLS-Scanner: https://github.com/SIWECOS/WS-TLS-Scanner
        'WS_TLS' => env('SCANNER_WS_TLS_URL'),
        // SIWECOS/Ini-S-Scanner: https://github.com/SIWECOS/Ini-S-Scanner
        'INI_S' => env('SCANNER_INI_S_URL'),
    ]

];
