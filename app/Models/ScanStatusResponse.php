<?php

namespace App\Siweocs\Models;

use App\Scan;

class ScanStatusResponse extends SiwecosBaseReponse
{

    public $progress;
    public $status;

    public function __construct(Scan $scan)
    {
        parent::__construct("");
        $this->progress = $scan->getProgress();
        $this->status = $scan->status;
    }

}