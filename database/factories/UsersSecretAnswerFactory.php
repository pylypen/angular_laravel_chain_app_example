<?php

use Faker\Generator as Faker;

use App\Models\UsersSite;
use App\Models\User;
use App\Models\Site;
use App\Models\Team;

$factory->define( UsersSite::class, function ( Faker $faker ) {
    $team_id = $faker->boolean ? Team::inRandomOrder()->first()->id : null;

    return [
        'user_id'          => User::inRandomOrder()->first()->id,
        'site_id'          => Site::inRandomOrder()->first()->id,
        'team_id'          => $team_id,
        'assigned_by_team' => $team_id ? $faker->boolean : false

    ];
} );
