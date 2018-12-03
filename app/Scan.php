<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;

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

    /**
     * Returns the scan's progress as a percent integer.
     */
    public function getProgress()
    {
        $amountScans = self::getAvailableScanners()->count();

        if($amountScans) {
            $doneResults = $this->results()
                ->whereNotNull('result')
                ->where('has_error', '=', '0')
                ->where('result', '!=', '')->count();
            $errResults = $this->results()
                ->where('result', '=', '[]')
                ->where('has_error', '=', '1')->count();

            $progress = round((($doneResults + $errResults) / $amountScans) * 100);

            Log::info('Progress: ' . $progress . ' % : Amount Scans: '.$amountScans.' / Done: '.$doneResults.' / Errors:'.$errResults);

            return $progress;
        }

        throw new \Exception("NO SCANNER URLs ARE SET!");
    }

    /**
     * Returns all configured scanners with name and URL.
     */
    public static function getAvailableScanners() {
        $scanners = collect();

        foreach (config('siwecos.available_scanners') as $name => $url) {
            if($url) {
                $scanners->push(['name' => $name, 'url' => $url]);
            }
        }

        return $scanners;
    }
}
