<?php

namespace Database\Seeds\Development;

use App\Models\Organisation;
use App\Models\SecretQuestions;
use App\Models\User;
use App\Models\UsersOrganisations;
use App\Models\UsersSecretAnswer;
use App\Models\UsersSite;
use App\Models\Files;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Generator as Faker;

class User2Seed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {

        factory(User::class, 10000)->create()->each(function ($u) use ($faker) {
            $file = Files::create([
                'src' => $faker->imageUrl(200, 200, 'people', false),
                'user_id' => $u->id
            ]);
            $u->avatar_id = $file->id;
            $u->save();

            UsersSecretAnswer::create([
                'secret_answer' => Hash::make('secret'),
                'user_id' => $u->id,
                'secret_questions_id' => SecretQuestions::inRandomOrder()->first()->id
            ]);

            $org = Organisation::inRandomOrder()->first();
            UsersOrganisations::create([
                'user_id' => $u->id,
                'organisation_id' => $org->id,
                'is_admin' => (rand(0, 100) > 99) ? 1 : 0
            ]);
            if (rand(0, 100) < 10) {
                $org_another = Organisation::inRandomOrder()->where('id','<>', $org->id)->first();
                UsersOrganisations::create([
                    'user_id' => $u->id,
                    'organisation_id' => $org_another->id,
                    'is_admin' => 0
                ]);
            }
            if ($org->sites()->count() && (rand(1, 100) > 30)) {
                UsersSite::create([
                    'user_id' => $u->id,
                    'site_id' => $org->sites()->inRandomOrder()->first()->id,
                    'is_admin' => (rand(0, 100) > 95) ? 1 : 0
                ]);
            }

        });

    }
}
