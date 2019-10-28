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
        'MAIL' => env('MAIL_SCANNER_URL'),
        'PORT' => env('PORT_SCANNER_URL'),
        'TLS' => env('TLS_SCANNER_URL'),
        'VERSION' => env('VERSION_SCANNER_URL'),
        'POP3_TLS' => env('POP3_TLS_SCANNER_URL'),
        'POP3S_TLS' => env('POP3S_TLS_SCANNER_URL'),
        'IMAP_TLS' => env('IMAP_TLS_SCANNER_URL'),
        'IMAPS_TLS' => env('IMAPS_TLS_SCANNER_URL'),
        'SMTP_TLS' => env('SMTP_TLS_SCANNER_URL'),
        'SMTPS_TLS' => env('SMTPS_TLS_SCANNER_URL'),
        'SMTP_MSA_TLS' => env('SMTP_MSA_TLS_SCANNER_URL'),
    ],
];
