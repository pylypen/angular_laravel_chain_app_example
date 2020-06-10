<?php

namespace Database\Seeds\Development;

use App\Models\Organisation;
use App\Models\SecretQuestions;
use App\Models\User;
use App\Models\UsersSecretAnswer;
use Illuminate\Database\Seeder;

use App\Models\UsersOrganisations;
use Illuminate\Support\Facades\Hash;


class ThirtyUserAndOrgAdmin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(User::class, 30)->create()->each(function ($u) {
            $org = Organisation::inRandomOrder()->first();
            $i = rand(1, 3);
            $user_org = new UsersOrganisations();
            $user_org->user_id = $u->id;
            $user_org->organisation_id = $org->id;
            $user_org->is_admin = ($i == 2) ? 1 : 0;
            $user_org->is_owner = 0;
            $user_org->save();

            UsersSecretAnswer::create([
                'secret_answer' => Hash::make('secret'),
                'user_id' => $u->id,
                'secret_questions_id' => SecretQuestions::inRandomOrder()->first()->id
            ]);

        });

}
}
