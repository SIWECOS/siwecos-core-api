<?php

namespace App\Siweocs\Models;

use App\Scan;
use App\Token;

class ScanRawResultResponse extends SiwecosBaseReponse
{
    public $scanStarted;
    public $scanFinished;
    public $scanners;
    public $domain;
    public $token;

    public function __construct(Scan $scan)
    {
        parent::__construct('current state of requested token');
        $this->domain = $scan->url;
        if ($scan->token instanceof Token) {
            $this->token = $scan->token->token;
        } else {
            $this->token = "NOTAVAILABLE";
        }
        $this->scanStarted = $scan->created_at;
        $this->scanFinished = $scan->updated_at;
        $this->scanners = $scan->results;
    }
}s