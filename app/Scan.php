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
        $envString = json_encode(getenv());
        // preg_match hit: "SCANNER_IRGENDWAS_URL:":"http://Irgendwas.de"
        // preg match miss: "SCANNER_IRGENDWAS_URL:":""
        $amountScans = preg_match_all('/"SCANNER_\w+_URL":"[^"]+"/', $envString);

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
}
