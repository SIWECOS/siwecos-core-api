<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanResult extends Model
{
    protected $fillable = ['scanner_code', 'result'];

    protected $casts = [
        'result' => 'json'
    ];

    public function getHasErrorAttribute()
    {
        return $this->result->get('hasError');
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
