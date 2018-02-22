<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ScanResult;

/**
 * App\Scan
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ScanResult[] $results
 * @property-read \App\Token $token
 * @property string $url
 * @property int|null $dangerLevel
 * @property \Illuminate\Support\Collection $callbackurls
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereCallbackurls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereDangerLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereUrl($value)
 * @property int $token_id
 * @property int $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereTokenId($value)
 */
class Scan extends Model
{
    protected $fillable = ['token_id', 'url', 'dangerLevel', 'callbackurls', 'status'];
    protected $casts = [
        'callbackurls' => 'collection'
    ];


	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function results()
    {
        return $this->hasMany(ScanResult::class);
    }


	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function token()
    {
        return $this->belongsTo(Token::class);
    }


    public function getProgress() {
        $allResults = $this->results()->count();
        $doneResults = $this->results()->whereNotNull('result')->count();
		Log::info('Progress: ' . $allResults . ' ' . $doneResults);
		Log::info(round(($doneResults / $allResults) * 100));
        return round(($doneResults / $allResults) * 100);
    }
}
