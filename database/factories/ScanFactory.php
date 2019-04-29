<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\Scan;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Scan::class, function (Faker $faker) {
    return [
        'url' => 'https://example.org',
        'dangerLevel' => $faker->numberBetween(0, 10),
        'callbackurls' => [
            'http://127.0.0.1/callback'
        ],
    ];
});
