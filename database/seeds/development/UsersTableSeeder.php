<?php

namespace Database\Seeds\Development;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UsersSecretAnswer;
use App\Models\SecretQuestions;

class UsersTableSeeder extends Seeder {

	public function run() {
		$user                 = new User();
		$user->first_name     = 'learnhub';
		$user->last_name      = 'learnhub';
		$user->title          = null;
		$user->email          = 'learnhub@learnhub.com';
		$user->password       = Hash::make('learnhub');
		$user->is_internal    = '1';
		$user->remember_token = 'Lp9M7KdTgq';
		$user->save();
		
		$UsersSecretAnswer = new UsersSecretAnswer();
		$UsersSecretAnswer->user_id = $user->id;
		$UsersSecretAnswer->secret_questions_id = SecretQuestions::all()->random()->id;
		$UsersSecretAnswer->secret_answer = Hash::make('secret');
		$UsersSecretAnswer->save();
		
		$user                 = new User();
		$user->first_name     = 'currentstack';
		$user->last_name      = 'currentstack';
		$user->title          = null;
		$user->email          = 'currentstack@currentstack.com';
		$user->password       = Hash::make('learnhub');
		$user->is_internal    = '1';
		$user->remember_token = 'oR5C4HkTgq';
		$user->save();

		$UsersSecretAnswer = new UsersSecretAnswer();
		$UsersSecretAnswer->user_id = $user->id;
		$UsersSecretAnswer->secret_questions_id = SecretQuestions::all()->random()->id;
		$UsersSecretAnswer->secret_answer = Hash::make('secret');
		$UsersSecretAnswer->save();

		factory( User::class, 50 )->create()->each(function ($u) {
			$UsersSecretAnswer = new UsersSecretAnswer();
			$UsersSecretAnswer->user_id = $u->id;
			$UsersSecretAnswer->secret_questions_id = SecretQuestions::all()->random()->id;
			$UsersSecretAnswer->secret_answer = Hash::make('secret');
			$UsersSecretAnswer->save();
		});


	}
}
