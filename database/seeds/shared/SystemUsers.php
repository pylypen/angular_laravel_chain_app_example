<?php

namespace Database\Seeds\Shared;

use App\Models\SecretQuestions;
use App\Models\User;
use App\Models\Files;
use App\Models\UsersOrganisations;
use App\Models\UsersSecretAnswer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemUsers extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();
        $user->first_name = 'learnhub';
        $user->last_name = 'learnhub';
        $user->email = 'learnhub@learnhub.com';
        $user->contact_email = 'learnhub@learnhub.com';
        $user->password = Hash::make('learnhub');
        $user->is_internal = '1';
        $user->remember_token = str_random(10);
        $user->save();

        UsersSecretAnswer::create([
            'secret_answer' => Hash::make('secret'),
            'user_id' => $user->id,
            'secret_questions_id' => SecretQuestions::inRandomOrder()->first()->id
        ]);

        //internal
        $user = new User();
        $user->first_name = 'currentstack';
        $user->last_name = 'currentstack';
        $user->email = 'currentstack@currentstack.com';
        $user->contact_email = 'currentstack@currentstack.com';
        $user->password = Hash::make('currentstack');
        $user->is_internal = '1';
        $user->remember_token = str_random(10);
        $user->save();

        UsersSecretAnswer::create([
            'secret_answer' => Hash::make('secret'),
            'user_id' => $user->id,
            'secret_questions_id' => SecretQuestions::inRandomOrder()->first()->id
        ]);
    }
}
