<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScanResult extends Model
{
    protected $guarded = [];

    public function scan()
    {
        return $this->belongsTo(Scan::class);
    }
}
