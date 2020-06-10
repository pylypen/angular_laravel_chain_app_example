<?php

namespace Database\Seeds\Development;

use App\Models\Media;
use Illuminate\Database\Seeder;

class MediaSeed extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		factory( Media::class, 1000 )->create();
	}
}
