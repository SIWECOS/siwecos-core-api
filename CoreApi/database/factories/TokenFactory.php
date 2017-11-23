<?php

use Faker\Generator as Faker;

$factory->define(App\Token::class, function (Faker $faker) {
    return [
        'token' => str_random(24),
        'credits' => 100,
    ];
});
