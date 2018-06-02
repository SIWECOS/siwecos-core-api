<?php

use Faker\Generator as Faker;

$factory->define(App\Scan::class, function (Faker $faker) {
    return [
        'token_id' => factory(App\Token::class)->create()->id,
        'url'      => 'https://example.org',
    ];
});
