<?php

use Faker\Generator as Faker;
use App\Models\Site;
use App\Models\Organisation;
use App\Models\files;

$factory->define(Site::class, function (Faker $faker) {
   /* $logo = Files::create([
        'src' => $faker->imageUrl(200, 200, 'business', false)
    ]);
    */
    return [
        'organisation_id' => Organisation::inRandomOrder()->first()->id,
        'name' => $faker->sentence(rand(1, 3))/*,
        'logo_id' => $logo->id*/
    ];
});
