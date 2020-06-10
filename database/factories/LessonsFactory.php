<?php

use Faker\Generator as Faker;
use App\Models\Lesson;
use App\Models\Course;

$factory->define( Lesson::class, function ( Faker $faker ) {
	return [
		'name'        => $faker->words( 1, true ),
		'description' => $faker->words( 50, true ),
		'allow_comments' => $faker->boolean()
	];
} );
