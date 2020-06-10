<?php

use Faker\Generator as Faker;

use App\Models\Media;
use App\Models\Lesson;

$factory->define( Media::class, function ( Faker $faker ) {

	return [
		'lesson_id'    => Lesson::inRandomOrder()->first()->id,
		'type'         => $faker->randomElement( [ 'text', 'video' ] ),
		'name'         => implode( ' ', $faker->words( 2 ) ),
		'display_name' => implode( ' ', $faker->words( 2 ) )
	];
} );
