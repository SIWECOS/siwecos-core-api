<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\ScanResult;
use Faker\Generator as Faker;

$factory->define(ScanResult::class, function (Faker $faker) {
    return [
        'scanner_code' => 'HEADER',
        'result' => file_get_contents(base_path('tests/sampleScanResult.json'))
    ];
});
