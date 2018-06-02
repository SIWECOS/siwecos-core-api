<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * App\ScanResult.
 *
 * @property int $id
 * @property int $scan_id
 * @property string $scanner_type
 * @property \Illuminate\Support\Collection $result
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Scan $scan
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereScanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereScannerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property int $total_score
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereTotalScore($value)
 *
 * @property string|null $complete_request
 * @property int $has_error
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereCompleteRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereHasError($value)
 */
class ScanResult extends Model
{
    protected $fillable = ['result', 'scanner_type', 'complete_request', 'has_error'];

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
