<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scan extends Model
{
    protected $dates = [
        'started_at', 'finished_at'
    ];

    protected $casts = [
        'callbackurls' => 'json'
    ];

    /**
     * A Scan can have many ScanResults
     *
     * @return HasMany
     */
    public function results()
    {
        return $this->hasMany(ScanResult::class);
    }
}
