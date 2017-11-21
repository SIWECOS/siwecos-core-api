<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scan;

class ScanResult extends Model
{
    protected $fillable = ['result', 'scanner_type'];

    public function scan()
    {
        return $this->belongsTo(Scan::class);
    }
}
