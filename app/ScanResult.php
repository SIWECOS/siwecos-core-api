<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanResult extends Model
{
    protected $fillable = ['scanner_code', 'result', 'is_failed'];

    protected $hidden = [
        'id', 'scan_id'
    ];

    protected $casts = [
        'result' => 'json',
        'is_failed' => 'boolean',
    ];

    public function getIsFinishedAttribute()
    {
        if ($this->result->isNotEmpty() || $this->is_failed === true) {
            return true;
        }
        return false;
    }

    public function getHasErrorAttribute()
    {
        if ($this->is_failed || $this->result->get('hasError') == true) {
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
