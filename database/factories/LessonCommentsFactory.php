<?php

use Faker\Generator as Faker;
use App\Models\LessonComments;

$factory->define( LessonComments::class, function ( Faker $faker ) {
	return [
		'comment' => $faker->words( random_int(3, 50), true )
	];
} );
