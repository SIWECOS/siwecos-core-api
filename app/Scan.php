<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scan extends Model
{
    protected $fillable = [
        'url', 'callbackurls', 'dangerLevel', 'started_at', 'finished_at'
    ];

    protected $dates = [
        'started_at', 'finished_at'
    ];

    protected $casts = [
        'callbackurls' => 'json',
        'result' => 'json',
    ];

    protected $appends = [
        'hasError'
    ];

    /**
     * Check if the scan is finished (all scan results were retrieved)
     *
     * @return boolean
     */
    public function getIsFinishedAttribute()
    {
        $amountFinishedScanResults = 0;

        foreach ($this->results as $result) {
            if ($result->isFinished) {
                $amountFinishedScanResults++;
            }
        }

        return count($this->results) === $amountFinishedScanResults;
    }

    /**
     * Check if the Scan has an error.
     *
     * @return boolean
     */
    public function getHasErrorAttribute()
    {
        if ($this->results->isEmpty()) {
            return true;
        }

        foreach ($this->results as $result) {
            if ($result->hasError || $result->is_failed) {
                return true;
            }
        }

        return false;
    }

    /**
     * A Scan can have many ScanResults
     *
     * @return HasMany
     */
    public function results()
    {
        return $this->hasMany(ScanResult::class);
    }

    /**
     * Cascade onDelete for ScanResult's
     *
     * @return boolean
     */
    public function delete()
    {
        $this->results->each(function ($result) {
            $result->delete();
        });

        return parent::delete();
    }
}
