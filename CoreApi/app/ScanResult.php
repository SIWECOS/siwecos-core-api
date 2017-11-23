<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scan;

class ScanResult extends Model
{
    protected $fillable = ['result', 'scanner_type'];

    protected $casts = [
        'result' => 'collection'
    ];
    
    /**
     * Returns an Eloquent Relationship for the belonging Scan
     *
     * @return void
     */
    public function scan()
    {
        return $this->belongsTo(Scan::class);
    }
}
