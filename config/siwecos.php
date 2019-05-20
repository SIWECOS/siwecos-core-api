<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User-Agent
    |--------------------------------------------------------------------------
    |
    | You can define a special User-Agent for HTTP-Requests. This User-Agent
    | will be used by the Scanners to perform the tests.
    |
    */

    'userAgent' => env('USER_AGENT', 'Mozilla/5.0 (X11; Linux x86_64; rv:63.0) Gecko/20100101 Firefox/63.0'),


    /*
    |--------------------------------------------------------------------------
    | SIWECOS-Scanners
    |--------------------------------------------------------------------------
    |
    | Below you will find a list with all SIWECOS compatible scanners to use.
    | Feel free to extend this list as a new scanner was developed.
    |
    */

    'scanners' => [
        'DOMXSS' => env('DOMXSS_SCANNER_URL'),
        'INI_S' => env('INI_S_SCANNER_URL'),
        'INFOLEAK' => env('INFOLEAK_SCANNER_URL'),
        'HEADER' => env('HEADER_SCANNER_URL'),
        'PORT' => env('PORT_SCANNER_URL'),
        'HTTPS' => env('HTTPS_SCANNER_URL'),
        'POP3' => env('POP3_SCANNER_URL'),
        'POP3S' => env('POP3S_SCANNER_URL'),
        'IMAP' => env('IMAP_SCANNER_URL'),
        'IMAPS' => env('IMAPS_SCANNER_URL'),
        'SMTP' => env('SMTP_SCANNER_URL'),
        'SMTPS' => env('SMTPS_SCANNER_URL'),
        'VERSION' => env('VERSION_SCANNER_URL'),
    ],
];
