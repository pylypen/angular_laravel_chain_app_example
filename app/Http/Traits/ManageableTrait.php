<?php

namespace App\Http\Traits;

use App\Models\Marketplace;
use App\Models\UsersCourse;
use App\Models\UsersTeam;
use App\Models\Team;
use App\Models\UsersSite;
use App\Models\UsersOrganisations;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Mail\NewUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

trait ManageableTrait
{
    /**
     * Get Manageable Teams
     *
     * @return array
     */
    private function getManageableTeams()
    {
        $teams = [];
        $user_org = UsersOrganisations::where([
            'user_id' => Auth::user()->id,
            'organisation_id' => Auth::user()->last_seen_org_id
        ])->first();

        if ($user_org->is_admin) {
            //user is Org Admin, pick all Org related teams
            $teams = Team::select('id')->where([
                'organisation_id' => Auth::user()->last_seen_org_id
            ])->get()->makeHidden('logo')->toArray();

        } else {
            // user in not org admin, pick all teams related to sites where user is admin.
            $sites = UsersSite::select('site_id')->where([
                'user_id' => Auth::user()->id,
                'is_admin' => 1
            ])->get();

            if (count($sites)) {
                $teams = Team::select('id')->whereIn('site_id', $sites)->get()->makeHidden('logo')->toArray();
            }
            
            // user is neither org admin neither site admin, pick teams where user is team admin
            $inTeams = UsersTeam::select('team_id')->where([
                'user_id' => Auth::user()->id,
                'is_admin' => 1
            ])->get()->toArray();

            $teams = array_merge($teams, $inTeams);
        }

        $ret = [];

        if (!empty($teams)) {
            foreach ($teams as $t) {
                $ret[] = (isset($t['id'])) ? $t['id'] : $t['team_id'];
            }
        }

        return $ret;
    }

    /**
     * Create New User
     *
     * @param array $data
     * @return boolean|object
     */
    private function createNewUser($data, $organisation_id)
    {
        $user = new User();
        $data['password'] = Hash::make(md5(time() . $data['email']));
        $data['confirm_code'] = md5($data['email'] . time());

        foreach ($data as $key => $param) {
            $user[$key] = $param;
        }
        $user->save();

        if ($user) {
            UsersOrganisations::create([
                'user_id' => $user->id,
                'organisation_id' => $organisation_id,
            ]);

            Mail::to($user->email)
                ->queue(new NewUser($user));

            return $user;
        }

        return false;
    }

    /**
     * Check is Admin Organisation
     *
     * @return boolean
     */
    private function isAdminOrganisation()
    {
        return UsersOrganisations::where([
            'user_id' => Auth::user()->id,
            'organisation_id' => Auth::user()->last_seen_org_id,
            'is_admin' => 1
        ])->count();
    }

    /**
     * Check is Admin Organisation
     *
     * @return boolean
     */
    private function isOwnerOrganisation()
    {
        return UsersOrganisations::where([
            'user_id' => Auth::user()->id,
            'organisation_id' => Auth::user()->last_seen_org_id,
            'is_owner' => 1
        ])->count();
    }
}