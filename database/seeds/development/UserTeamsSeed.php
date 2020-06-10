<?php


namespace Database\Seeds\Development;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\UsersTeam;
use App\Models\User;
use App\Models\Team;
use App\Models\UsersSecretAnswer;
use App\Models\SecretQuestions;

class UserTeamsSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teams = Team::all();
        /* create creator teams */
        foreach ($teams as $team) {
            $user_rand = User::all()->random()->id;
            $usersTeam = new UsersTeam();
            $usersTeam->user_id = $user_rand;
            $usersTeam->team_id = $team->id;
            $usersTeam->creator = true;
            $usersTeam->is_lead = true;
            $usersTeam->save();
        }

        factory(User::class, 1000)->create()->each(function ($u) {
            $UsersSecretAnswer = new UsersSecretAnswer();
            $UsersSecretAnswer->user_id = $u->id;
            $UsersSecretAnswer->secret_questions_id = SecretQuestions::all()->random()->id;
            $UsersSecretAnswer->secret_answer = Hash::make('secret');
            $UsersSecretAnswer->save();
        });
        foreach ($teams as $team) {
	        $count        = rand( 1, 600 );
            $userExcept   = array();
            $userExcept[] = UsersTeam::where([
                ['team_id', '=', '1'],
                ['creator', '=', 1]
            ])->first()->user_id;

            for ($i = 0; $i < $count; $i++) {
                $user = User::whereNotIn('id', $userExcept)->first();

	            $userExcept[] = $user->id;
                UsersTeam::create([
                    'user_id' => $user->id,
                    'team_id' => $team->id,
                    'is_lead' => (rand(0, 100) > 10) ? false : true
                ]);
            }

        }
    }
}
