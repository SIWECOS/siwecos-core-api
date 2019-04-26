<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScanResult extends Model
{
    protected $guarded = [];

    protected $casts = [
        'result' => 'json'
    ];

    public function scan()
    {
        return $this->belongsTo(Scan::class);
    }

    public function getResultAttribute()
    {
        return collect(json_decode($this->attributes['result']))->recursive();
    }
}
