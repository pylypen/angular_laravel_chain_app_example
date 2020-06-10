<?php

namespace App\Http\Traits;

use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth as Identity;
use App\Models\UsersOrganisations;

trait AuthTrait
{
    /**
     * Login Action By Credentials
     *
     * @param array $credentials
     *
     * @return bool
     */
    private function loginByCredentials(array $credentials)
    {
        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return [
                    'success' => false,
                    'data' => ['email' => [__('auth.login_credentials_error')]],
                    'code' => 401
                ];
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return [
                'success' => false,
                'data' => ['email' => [__('auth.login_token_error')]],
                'code' => 500
            ];
        }

        //Trying to find out what org user wants to use now
        $organisation = Identity::getUser()->organisation()->first();
        $user = Identity::getUser();
        if (!$organisation) {
            $relation = Identity::getUser()->usersOrganisations()->first();

            if (!$relation) {
                JWTAuth::invalidate($token);
                
                return [
                    'success' => false,
                    'data' => ['email' => [__('auth.no_organisation_assigned')]],
                    'code' => 401
                ];
            }
            $user->last_seen_org_id = $relation->organisation_id;
        }

        $user->login_at = Carbon::now();
        $user->save();

        $org_owner = UsersOrganisations::where([
            'organisation_id' => $user->last_seen_org_id,
            'is_owner' => 1
        ])->first();

        return [
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => Identity::getUser(),
                'organisation' => Identity::getUser()->organisation()->first(),
                'subscription' => [
                    'is_trial' => $org_owner->user->trial_ends_at >= Carbon::now(),
                    'trial_ends' => $org_owner->user->trial_ends_at,
                ]
            ],
            'code' => 200
        ];
    }
}