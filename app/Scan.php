<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;

class Scan extends Model
{
    protected $fillable = ['token_id', 'url', 'dangerLevel', 'callbackurls', 'status', 'freescan'];
    protected $casts = [
        'callbackurls' => 'collection',
        'recurrentscan' => 'boolean',
        'freescan' => 'boolean'
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

        foreach (getenv() as $key => $value) {
            if(preg_match("/^SCANNER_(\w+)_URL$/", $key, $scanner_name)) {
                $url = env($scanner_name[0]);
                if ($url) {
                    $scanners->push(['name' => $scanner_name[1], 'url' => $url]);
                }
            }
        }

        return $scanners;
    }
}
