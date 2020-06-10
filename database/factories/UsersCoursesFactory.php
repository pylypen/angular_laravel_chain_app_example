<?php

use Faker\Generator as Faker;
use App\Models\UsersCourse;
use App\Models\User;
use App\Models\Team;
use App\Models\Course;

$factory->define( UsersCourse::class, function ( Faker $faker ) {
	$user_id     = User::inRandomOrder()->first()->id;
	$team_id     = Team::inRandomOrder()->first()->id;
	$course_id    = Course::inRandomOrder()->first()->id;
	$assigned_id = User::inRandomOrder()->first()->id;

	return [
		'user_id'          => $user_id,
		'course_id'        => $course_id,
		'assigned_by_id'   => $assigned_id,
		'team_id'          => $team_id,
		'is_team_assigned' => $faker->boolean(),
		'is_obligatory'    => $faker->boolean()
	];
} );
