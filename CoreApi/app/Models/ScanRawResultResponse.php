<?php

namespace App\Siweocs\Models;

use App\Scan;

class ScanRawResultResponse extends SiwecosBaseReponse
{

    public $scanStarted;
    public $scanFinished;
    public $scanners;

    public function __construct(Scan $scan)
    {
        parent::__construct("current state of requested token");
        $this->scanStarted = $scan->createdAt;
        $this->scanFinished = $scan->updatedAt;
        $this->scanners = $scan->results;
    }

}