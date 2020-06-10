<?php

namespace Database\Seeds\Development;

use Illuminate\Database\Seeder;
use App\Models\UsersSite;


class UsersSitesSeed extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		factory( UsersSite::class, 500 )->create();
	}
}
