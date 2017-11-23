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
 */
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
