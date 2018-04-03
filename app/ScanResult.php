<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scan;
use Illuminate\Support\Collection;

/**
 * App\ScanResult
 *
 * @property int $id
 * @property int $scan_id
 * @property string $scanner_type
 * @property \Illuminate\Support\Collection $result
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Scan $scan
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereScanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereScannerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $total_score
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereTotalScore($value)
 */
class ScanResult extends Model
{
    protected $fillable = ['result', 'scanner_type'];

    protected $casts = [
        'result' => 'collection'
    ];


	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function scan()
    {
        return $this->belongsTo(Scan::class);
    }

    public function setTimeout(){
	    $this->result = [];
	    $this->save();
    }
}
