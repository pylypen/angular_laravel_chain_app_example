<?php

namespace Database\Seeds\Development;

use Illuminate\Database\Seeder;

use App\Models\Organisation;

class OrganisationSeed extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		factory( Organisation::class, 20 )->create();
	}
}
