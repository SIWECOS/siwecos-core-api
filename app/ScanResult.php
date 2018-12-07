<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ScanResult extends Model
{
    protected $fillable = ['result', 'total_score', 'scanner_type', 'complete_request', 'has_error', 'error_message'];

    protected $casts = [
        'result'           => 'collection',
        'complete_request' => 'collection',
        'has_error'        => 'bool',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function scan()
    {
        return $this->belongsTo(Scan::class);
    }

    public function setTimeout()
    {
        $this->result = [];
        $this->save();
    }
}
