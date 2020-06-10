<?php

namespace Database\Seeds\Development;

use App\Models\Course;
use App\Models\CoursesContributors;
use Illuminate\Database\Seeder;
use App\Models\User;

class CoursesContributorsSeed extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		for ( $i = 0; $i <= 200; $i ++ ) {
			$courses_contributor            = new CoursesContributors();
			$courses_contributor->course_id = Course::all()->random()->id;
			$courses_contributor->user_id   = User::all()->random()->id;
			$courses_contributor->save();
		}
	}
}
