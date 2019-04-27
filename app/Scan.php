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

    protected $appends = [
        'hasError'
    ];

    public function getHasErrorAttribute()
    {
        foreach ($this->results as $result) {
            if ($result->hasError) {
                return true;
            }
        }

        return false;
    }

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
