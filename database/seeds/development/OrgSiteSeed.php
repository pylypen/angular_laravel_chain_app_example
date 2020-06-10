<?php

namespace Database\Seeds\Development;


use App\Models\UsersOrganisations;
use App\Models\UsersSite;
use Illuminate\Database\Seeder;
use App\Models\Organisation;
use App\Models\Site;
use Faker\Generator as Faker;

class OrgSiteSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Site::class, rand(Organisation::all()->count(), Organisation::all()->count() * rand(2, 5)))->create()->each(function ($site) {
            UsersSite::create([
                'site_id' => $site->id,
                'user_id' => UsersOrganisations::where('organisation_id', $site->organisation_id)->inRandomOrder()->first()->user_id,
                'is_admin' => 1
            ]);
        });
    }
}
