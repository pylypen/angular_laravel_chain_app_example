<?php

namespace Database\Seeds\Development;

use App\Models\Files;
use App\Models\SecretQuestions;
use App\Models\UsersSecretAnswer;
use Illuminate\Database\Seeder;
use App\Models\User;
use Faker\Generator as Faker;
use App\Models\Organisation;
use App\Models\UsersOrganisations;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TenUserAndOrgStatic extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        factory(Organisation::class, 10)->create()->each(function ($o) use ($faker) {
            $gender = $faker->boolean() ? 'male' : 'female';
            $email = $faker->unique()->safeEmail;
            $user = new User();
            $user->first_name = 'org_admin' . $o->id;
            $user->last_name = 'org_admin' . $o->id;
            $user->email = $email;
            $user->contact_email = $email;
            $user->password = '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm';// secret
            $user->remember_token = str_random(10);
            $user->is_internal = 0;
            $user->nickname = $faker->unique()->userName( $gender );
            $user->trial_ends_at = Carbon::createFromTimestamp(strtotime("+60 month"));
            $user->save();

          /*  $file = Files::create([
                'src' => $faker->imageUrl(200, 200, 'people', false),
                'user_id' => $user->id
            ]);

            $user->avatar_id = $file->id;
            $user->save();
          */

            UsersSecretAnswer::create([
                'secret_answer' => Hash::make('secret'),
                'user_id' => $user->id,
                'secret_questions_id' => SecretQuestions::inRandomOrder()->first()->id
            ]);

            UsersOrganisations::create([
                'user_id' => $user->id,
                'organisation_id' => $o->id,
                'is_admin' => 1,
                'is_owner' => 1,
            ]);
        });
    }
}
