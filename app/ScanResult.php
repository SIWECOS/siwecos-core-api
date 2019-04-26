<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanResult extends Model
{
    protected $guarded = [];

    protected $casts = [
        'result' => 'json'
    ];

    /**
     * A ScanResult belongsTo a Scan
     *
     * @return BelongsTo
     */
    public function scan()
    {
        return $this->belongsTo(Scan::class);
    }

    /**
     * Return the result attribute as collection.
     *
     * @return Collection
     */
    public function getResultAttribute()
    {
        return collect(json_decode($this->attributes['result']))->recursive();
    }
}
