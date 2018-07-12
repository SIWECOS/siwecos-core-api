<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;

/**
 * App\Scan.
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ScanResult[] $results
 * @property-read \App\Token $token
 * @property string $url
 * @property int|null $dangerLevel
 * @property \Illuminate\Support\Collection $callbackurls
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereCallbackurls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereDangerLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereUrl($value)
 *
 * @property int $token_id
 * @property int $status
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereTokenId($value)
 *
 * @property int $freescan
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereFreescan($value)
 *
 * @property int $recurrentscan
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereRecurrentscan($value)
 */
class Scan extends Model
{
    protected $fillable = ['token_id', 'url', 'dangerLevel', 'callbackurls', 'status'];
    protected $casts = [
        'callbackurls' => 'collection',
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

    public function getProgress()
    {
        $allResults = $this->results()->count();
        if ($allResults > 0) {
          $doneResults = $this->results()
            ->whereNotNull('result')->count();
          $errResults = $this->results()
            ->whereNull('result')->where('has_error','=','true')->count();
          Log::info('Progress: '.$allResults.' '.$doneResults.' '.$errResults);
          Log::info(round((($doneResults + $errResults) / $allResults) * 100));

          return round((($doneResults + $errResults)  / $allResults) * 100);        }

        return 0;
    }
}
