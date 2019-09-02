<?php

namespace App;

use GuzzleHttp\Client;

class HTTPClient extends Client
{
    public function __construct(array $config = [])
    {
        parent::__construct(array_merge([
            'http_errors' => false,
            'timeout' => 30,
            'headers' => [
                'User-Agent' => config('siwecos.userAgent'),
            ],
        ], $config));
    }
}
