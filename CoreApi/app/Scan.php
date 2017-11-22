<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ScanResult;

class Scan extends Model
{
    protected $fillable = ['url', 'dangerLevel', 'callbackurls'];
    protected $casts = [
        'callbackurls' => 'collection'
    ];

    
    /**
     * Returns an Eloquent Relationship for the ScanResults.
     *
     * @return void
     */
    public function results()
    {
        return $this->hasMany(ScanResult::class);
    }

    // TODO: Verify Token implementation
    /**
     * Returns an Eloquent Relationship for the belonging Token
     *
     * @return void
     */
    public function token()
    {
        return $this->belongsTo(Token::class);
    }
}
