<?php

namespace App\Siweocs\Models;

use App\Scan;

class ScanRawResultResponse extends SiwecosBaseReponse
{
    public $scanStarted;
    public $scanFinished;
    public $scanners;
    public $domain;

    public function __construct(Scan $scan)
    {
        parent::__construct('current state of requested token');
        $this->domain = $scan->url;
        $this->token = $scan->token->token;
        $this->scanStarted = $scan->created_at;
        $this->scanFinished = $scan->updated_at;
        $this->scanners = $scan->results;
    }
}
