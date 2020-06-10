<?php

use Faker\Generator as Faker;
use App\Models\User;
use App\Models\Files;

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

$factory->define(User::class, function (Faker $faker) {
    $faker->boolean() ? $gender = 'male' : $gender = 'female';
    $email = $faker->unique()->safeEmail;
    return [
        'first_name' => $faker->firstName($gender),
        'last_name' => $faker->lastName,
        'email' => $email,
        'contact_email' => (rand(1, 100) > 80) ? $faker->email : $email,
        'birthday' => (rand(1, 100) > 80) ? null : $faker->date(),
        'phone_number' => (rand(1, 100) > 70) ? null : $faker->phoneNumber,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
        'nickname'  => $faker->unique()->userName( $gender ),
        'is_internal' => 0
    ];
});
