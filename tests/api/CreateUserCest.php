<?php

use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Organisation;
use App\Models\UsersOrganisations;

class CreateUserCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
    }

    // tests
    public function tryToTest(ApiTester $I)
    {
        $user = $this->createUserInDB();

        $I->sendPOST('/confirm_code', [
            "confirm_code" => $user->confirm_code,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "password" => "secret",
            "password_confirmation"=> "secret"
        ]);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }

    private function createUserInDB() {
        $faker = Faker::create();

        $faker->boolean() ? $gender = 'male' : $gender = 'female';
        $email = $faker->unique()->safeEmail;

        
        $user =  [
            'first_name' => $faker->firstName($gender),
            'last_name' => $faker->lastName,
            'email' => $email,
            'contact_email' => (rand(1, 100) > 80) ? $faker->email : $email,
            'birthday' => (rand(1, 100) > 80) ? null : $faker->date(),
            'phone_number' => (rand(1, 100) > 70) ? null : $faker->phoneNumber,
            'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
            'remember_token' => str_random(10),
            'confirm_code' => md5($email . $gender . time()),
            'nickname'  => $faker->unique()->userName( $gender ),
            'is_internal' => 0
        ];

        $user = User::create($user);

        $org = Organisation::inRandomOrder()->first();
        UsersOrganisations::create([
            'user_id' => $user->id,
            'organisation_id' => $org->id,
            'is_admin' => 0
        ]);

        return $user;
    }
}
