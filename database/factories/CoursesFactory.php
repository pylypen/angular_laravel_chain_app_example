<?php

use Faker\Generator as Faker;

use App\Models\Course;
use App\Models\User;

$factory->define(Course::class, function (Faker $faker) {
    $user = User::where('id', '>', 12)->inRandomOrder()->first();

    return [
        'author_id' => $user->id,
        'name' => $faker->sentence(rand(2, 5)),
        'subtitle' => $faker->sentence(rand(2, 10)),
        'description' => $faker->sentences(rand(2, 10), true)
    ];
});
