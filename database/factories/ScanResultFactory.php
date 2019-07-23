<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\ScanResult;
use Faker\Generator as Faker;

$factory->define(ScanResult::class, function (Faker $faker) {
    return [
        'scanner_code' => 'INI_S',
        'result' => json_decode(file_get_contents(base_path('tests/sampleScanResult.json')))
    ];
});
