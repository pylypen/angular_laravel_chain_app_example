<?php

namespace Database\Seeds\Development;

use App\Models\User;
use App\Models\UsersOrganisations;
use App\Models\UsersSite;
use App\Models\UsersTeam;
use Illuminate\Database\Seeder;
use App\Models\Team;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TeamSeed extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		factory( Team::class, 50 )->create()->each( function ( $team ) {
			if ( is_numeric( $team->site_id ) ) {
				$admin = UsersSite::where( [ 'site_id' => $team->site_id, 'is_admin' => 1 ] )
				                  ->inRandomOrder()->first()->user_id;
			} else {
				$admin = UsersOrganisations::where(['organisation_id' => $team->organisation_id, 'is_admin' => 1])
				                           ->inRandomOrder()->first()->user_id;
			}

			UsersTeam::create( [
				'user_id'  => $admin,
				'team_id'  => $team->id,
				'is_admin' => 1,
				'is_owner' => 1
			] );

			if ( $team->site_id ) {
				$collections = UsersSite::where('site_id', $team->site_id)->get();
			} else {
				$collections = UsersOrganisations::where('organisation_id', $team->organisation_id)->get();
			}

			foreach ( $collections as $collection ) {
				if ( $collection->user_id == $admin || rand(1, 100) > 80 ) {
					continue;
				}	
				// Very high value of user_id is unclear from where
				// Artem's nuclear code
//				if ($user->id > User::orderBy( 'id', 'desc' )->first()->id) {
//					break;
//				}
				UsersTeam::create( [
					'user_id'  => $collection->user_id,
					'team_id'  => $team->id,
					'is_admin' => ( rand(1, 100) > 95 ) ? 1 : 0,
					'is_owner' => 0
				] );


			}

		} );
	}
}
