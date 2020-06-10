<?php

use Faker\Generator as Faker;
use App\Models\Team as Team;
use App\Models\Organisation;
use App\Models\Files;

$factory->define(Team::class, function (Faker $faker) {
    $org = Organisation::inRandomOrder()->first();
    $site = $org->sites()->inRandomOrder()->first();
   /* $logo = Files::create([
        'src' => $faker->imageUrl(200, 200, 'business', false)
    ]);*/
    
    return [
        'name' => $faker->sentence(rand(1, 5), true),
        'description' => $faker->slug(rand(6, 15)),
       // 'logo_id' => $logo->id,
        'organisation_id' => $org->id,
        'site_id' => (rand(1, 100) > 40 && $site) ? $site->id : null
    ];
});
