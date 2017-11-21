<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ScanResult;

class Scan extends Model
{
    protected $fillable = ['token', 'url', 'dangerLevel', 'callbackurls'];

    public function results()
    {
        return $this->hasMany(ScanResult::class);
    }
}
