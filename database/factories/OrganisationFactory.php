<?php

use Faker\Generator as Faker;
use App\Models\Organisation as Organisation;
use App\Models\Files;

$factory->define(Organisation::class, function (Faker $faker) {
   /* $logo = Files::create([
        'src' => $faker->imageUrl(200, 200, 'abstract', false)
    ]);
    $cover_picture = Files::create([
        'src' => $faker->imageUrl(1920, 300, 'nature', false)
    ]);*/
    
    return [
        'email' => $faker->companyEmail,
        'name' => $faker->company,
        'phone_number' => $faker->phoneNumber,
        'state' => $faker->word,
        'city' => $faker->word,
        'street' => $faker->streetAddress,
        'zip' => rand(10000,99999)/*,
        'logo_id' => $logo->id,
        'cover_picture_id' => $cover_picture->id*/
    ];
});
