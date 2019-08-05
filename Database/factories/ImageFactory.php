<?php

use App\Modules\Image\Image;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

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

$factory->define(Image::class, function (Faker $faker) {
    return [
        'u_id' => uniqid('image_', false),
        'hash' => $faker->md5,
        'name' => $faker->word,
        'size' => $faker->randomNumber(4),
        'width' => $faker->randomNumber(4),
        'height' => $faker->randomNumber(4),
        'type' => $faker->fileExtension,
        'alt' => $faker->sentence,
        'owner' => uniqid('user_', false),
        'public' => true
    ];
});
