<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanResult extends Model
{
    protected $fillable = ['scanner_code', 'result', 'has_error'];

    protected $hidden = [
        'id', 'scan_id'
    ];

    protected $casts = [
        'result' => 'json',
        'has_error' => 'boolean'
    ];

    public function getIsFinishedAttribute()
    {
        if ($this->result->isNotEmpty() || $this->has_error) {
            return true;
        }
        return false;
    }

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
