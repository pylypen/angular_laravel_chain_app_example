<?php

namespace Database\Seeds\Development;

use Illuminate\Database\Seeder;
use App\Models\UsersCourse;

class UsersCoursesSeed extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		factory( UsersCourse::class, 1000 )->create();
	}
}
