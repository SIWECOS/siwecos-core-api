<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    protected $dates = [
        'started_at', 'finished_at'
    ];

    protected $casts = [
        'callbackurls' => 'json'
    ];

    public function results()
    {
        return $this->hasMany(ScanResult::class);
    }
}
