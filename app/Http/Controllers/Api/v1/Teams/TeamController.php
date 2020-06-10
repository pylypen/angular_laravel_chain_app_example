<?php

namespace App\Http\Controllers\API\v1\Teams;

use Image;
use App\Models\Marketplace;
use App\Models\User;
use App\Models\UsersCourse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\API\v1\Teams\TeamsUpdateSettingsRequest;
use App\Http\Requests\API\v1\Teams\TeamRenewSettingsRequest;
use App\Http\Requests\API\v1\Teams\TeamUpdateMembersRequest;
use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\UsersSite;
use App\Models\Team;
use App\Models\UsersTeam;
use App\Models\UsersOrganisations;
use App\Models\Files;
use App\Jobs\ImageThumbnailProcess;
use App\Http\Traits\ManageableTrait;
use App\Http\Traits\UploadFileTrait;

class TeamController extends Controller
{
    use ManageableTrait;

    use UploadFileTrait;

    const MIMES = [
        'image/png',
        'image/jpeg',
        'image/jpg'
    ];
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $org_admin = UsersOrganisations::where([
            'user_id' => Auth::user()->id,
            'organisation_id' => Auth::user()->last_seen_org_id,
            'is_admin' => 1])->count();
        $teams_data = [];

        //check is user is admin of current org
        if ($org_admin) {
            $admin_teams = Team::where('organisation_id', Auth::user()->last_seen_org_id)->get();
        } else {
            // get teams where guy might be site_admin
            $admin_teams = Team::hydrate(Site::select('teams.*')
                ->join('users_sites', function ($join) {
                    $join->on('users_sites.site_id', '=', 'sites.id')
                        ->where([
                            'users_sites.user_id' => Auth::user()->id,
                            'users_sites.is_admin' => 1
                        ]);
                })
                ->join('teams', function ($join) {
                    $join->on('teams.site_id', '=', 'users_sites.site_id');
                })
                ->where([
                    'sites.organisation_id' => Auth::user()->last_seen_org_id
                ])->get()->toArray())->load('logo');
        }

        //Get teams where user might be a member
        $member_teams = Team::hydrate(UsersTeam::select('teams.*')
            ->join('teams', function ($join) {
                $join->on('teams.id', '=', 'users_teams.team_id');
            })
            ->where([
                'users_teams.user_id' => Auth::user()->id,
                'teams.organisation_id' => Auth::user()->last_seen_org_id
            ])->get()->toArray())->load('logo');

        $merged_teams = [];

        //merge teams into one array with saving of adminity
        foreach ($admin_teams as $admin_team) {
            $merged_teams['team_' . $admin_team->id] = [
                'is_admin' => 1,
                'is_member' => 0,
                'team' => $admin_team
            ];
        }

        foreach ($member_teams as $member_team) {
            if (isset($merged_teams['team_' . $member_team->id])) {
                // team appears in both collections. Keep the Admin one
                // but track membership
                $merged_teams['team_' . $member_team->id]['is_member'] = 1;
                continue;
            }
            $merged_teams['team_' . $member_team->id] = [
                'is_admin' => UsersTeam::where([
                    'user_id' => Auth::user()->id,
                    'team_id' => $member_team->id
                ])->first()->is_admin,
                'is_member' => 1,
                'team' => $member_team
            ];
        }

        // forging response
        $index = 0;
        foreach ($merged_teams as $merged) {
            $team = $merged['team'];

            $teams_data[$index]['team'] = $team;
            $teams_data[$index]['belongs_to'] = [
                'organisation' => $team->organisation()->first(),
                'site' => $team->site()->first()
            ];
            $teams_data[$index]['member_count'] = $team->users()->count();
            $teams_data[$index]['member_preview'] = $team->users()
                ->inRandomOrder()->limit(9)->get()->toArray();
            $teams_data[$index]['is_admin'] = (bool)$merged['is_admin'];
            $teams_data[$index]['is_member'] = (bool)$merged['is_member'];
            $index++;
        }

        $data = [
            'allow_create' => ($org_admin || count($admin_teams)) ? true : false,
            'teams' => $teams_data
        ];

        return $this->_set_success($data);
    }

    /**
     * Get Team Settings Config
     *
     * @param  string $subdomain
     * @param  integer|boolean $id
     *
     * @return \Illuminate\Http\Response
     */
    public function getTeamSettingsConfig($subdomain, $id = false)
    {
        $isSiteAdmin = false;
        $team_admin = false;
        $team = UsersTeam::where(['team_id' => (int)$id])->first();

        $is_org_admin = (bool)UsersOrganisations::where([
            'organisation_id' => Auth::user()->last_seen_org_id,
            'user_id' => Auth::user()->id,
            'is_admin' => 1
        ])->count();

        if ($team) {
            $team_admin = (bool)UsersTeam::where(['user_id' => Auth::user()->id, 'team_id' => (int)$id, 'is_admin' => 1])->count();

            if (!empty($team->team->site)) {
                $isSiteAdmin = (bool)UsersSite::where([
                    'site_id' => $team->team->site->id,
                    'user_id' => Auth::user()->id,
                    'is_admin' => 1
                ])->count();
            }
        }

        if ($isSiteAdmin || $is_org_admin || $team_admin) {

            $response = [];
            $response['admins'] = [];       // guys who are admins of current team
            $response['team'] = new Team(); // team_data itself
            $response['users'] = [];

            // any org admin might become any team admin
            $response['users'] = User::whereIn('id',
                UsersOrganisations::select('user_id')
                    ->where(['organisation_id' => Auth::user()->last_seen_org_id, 'is_admin' => 1])
                    ->get()->makeHidden(['organisation'])
            )->get()->makeHidden(['avatar']);

            $response['belongs_to'] = new Site();

            if ($is_org_admin) {
                // if user is org admin then he can pick any site of his team
                $response['can_belongs_to'] = Site::where('organisation_id', Auth::user()->last_seen_org_id)->get();
            } else {
                // else he can pick only sites where he is an admin
                $response['can_belongs_to'] = Site::whereIn('id',
                    UsersSite::select('site_id')
                        ->where(['user_id' => Auth::user()->id, 'is_admin' => 1])
                        ->get()
                )->get()->makeHidden(['logo']);
            }

            // end of default data, getting now what we have in db if we do

            if ((int)$id) {
                $response['admins'] = User::whereIn('id',
                    UsersTeam::select('user_id')
                        ->where(['team_id' => $id, 'is_admin' => 1])
                        ->get()
                )->get()->makeHidden(['avatar']);

                $response['team'] = Team::where('id', $id)->first();

                if ($response['team']->site_id) {
                    // adding site data if needed
                    $response['belongs_to'] = Site::where('id', $response['team']->site_id)->first();

                    // site Admins might also become team admin
                    $response['users'] = User::whereIn('id',
                        UsersSite::select('user_id')
                            ->where(['site_id' => $response['team']->site_id, 'is_admin' => 1])
                            ->get()
                    )->get()->makeHidden(['avatar']);

                    $response['users'] = User::whereIn('id',
                        UsersSite::select('user_id')
                            ->where(['site_id' => $response['team']->site_id, 'is_admin' => 0])
                            ->get()
                    )->get()->makeHidden(['avatar']);

                } else {
                    // if team belongs to org, then pick org members
                    $response['users'] = User::whereIn('id',
                        UsersOrganisations::select('user_id')
                            ->where(['organisation_id' => Auth::user()->last_seen_org_id, 'is_admin' => 0])
                            ->get()->makeHidden(['organisation'])
                    )->get()->makeHidden(['avatar']);
                }
            } else {
                // by default selection is from org members
                $response['users'] = User::whereIn('id',
                    UsersOrganisations::select('user_id')
                        ->where(['organisation_id' => Auth::user()->last_seen_org_id, 'is_admin' => 0])
                        ->get()->makeHidden(['organisation'])
                )->get()->makeHidden(['avatar']);
            }

            return $this->_set_success($response);
        }

        return $this->_set_error(['team' => [__('team.show_error', ['id' => $id])]]);
    }

    /**
     * Renew Team Settings Config
     *
     * @param  TeamRenewSettingsRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function renewTeamSettingsConfig(TeamRenewSettingsRequest $request)
    {
        $isOrgAdmin = (bool)UsersOrganisations::where([
            'organisation_id' => Auth::user()->last_seen_org_id,
            'user_id' => Auth::user()->id,
            'is_admin' => 1
        ])->count();

        $isSiteAdmin = (bool)UsersSite::where([
            'site_id' => $request->belongs_to,
            'user_id' => Auth::user()->id,
            'is_admin' => 1
        ])->count();

        if ($isSiteAdmin || $isOrgAdmin) {
            $data = $request->only(['belongs_to']);

            $response['users'] = [];

            $response['users'] = User::whereIn('id',
                UsersOrganisations::select('user_id')
                    ->where(['organisation_id' => Auth::user()->last_seen_org_id, 'is_admin' => 1])
                    ->get()->makeHidden(['organisation'])
            )->get()->makeHidden(['avatar']);

            if ((int)$data['belongs_to']) {
                //team now belongs to site
                $response['users'] = User::whereIn('id',
                    UsersSite::select('user_id')
                        ->where(['site_id' => $data['belongs_to'], 'is_admin' => 1])
                        ->get()
                )->get()->makeHidden(['avatar']);

                $response['users'] = User::whereIn('id',
                    UsersSite::select('user_id')
                        ->where(['site_id' => $data['belongs_to'], 'is_admin' => 0])
                        ->get()
                )->get()->makeHidden(['avatar']);

            } else {
                //team now belongs to organisation
                $response['users'] = User::whereIn('id',
                    UsersOrganisations::select('user_id')
                        ->where(['organisation_id' => Auth::user()->last_seen_org_id, 'is_admin' => 0])
                        ->get()->makeHidden(['organisation'])
                )->get()->makeHidden(['avatar']);
            }

            return $this->_set_success($response);
        }

        return $this->_set_error(['team' => [__('team.update_error')]]);
    }

    /**
     * getTeamMembersConfig
     *
     * @param  string $subdomain
     * @param  integer $id
     *
     * @return \Illuminate\Http\Response
     */
    public function getTeamMembersConfig($subdomain, $id)
    {
        $isSiteAdmin = false;
        $team_admin = false;
        $team = Team::find((int)$id);

        $isOrgAdmin = (bool)UsersOrganisations::where([
            'organisation_id' => Auth::user()->last_seen_org_id,
            'user_id' => Auth::user()->id,
            'is_admin' => 1
        ])->count();

        if ($team) {
            $team_admin = (bool)UsersTeam::where(['user_id' => Auth::user()->id, 'team_id' => (int)$id, 'is_admin' => 1])->count();

            if (!empty($team->site_id)) {
                $isSiteAdmin = (bool)UsersSite::where([
                    'site_id' => $team->site_id,
                    'user_id' => Auth::user()->id,
                    'is_admin' => 1
                ])->count();
            }
        }

        if ($team && ($isSiteAdmin || $isOrgAdmin || $team_admin)) {
            $users = [];
            $admins = [];
            foreach (UsersTeam::select('user_id')
                         ->where(['team_id' => $id, 'is_admin' => 1])
                         ->get() as $admin) {
                @$admins[] = $admin['user_id'];
            }

            $response = [
                'team' => $team,
                'members' => User::whereIn('id',
                    UsersTeam::select('user_id')
                        ->where(['team_id' => $id, 'is_admin' => 0])
                        ->get()
                )->get()->makeHidden(['avatar']),
                'users' => []
            ];

            if ($team->site_id) {
                foreach (UsersSite::select('user_id')
                             ->where(['site_id' => $team->site_id])
                             ->get() as $user) {
                    @$users[] = $user['user_id'];
                }
                $users = array_diff($users, $admins);
                $response['users'] = User::whereIn('id', $users)->get()->makeHidden(['avatar']);
            } else {
                foreach (UsersOrganisations::select('user_id')
                             ->where(['organisation_id' => Auth::user()->last_seen_org_id])
                             ->get() as $user) {
                    @$users[] = $user['user_id'];
                }
                $users = array_diff($users, $admins);
                $response['users'] = User::whereIn('id', $users)->get()->makeHidden(['avatar']);
            }

            return $this->_set_success($response);

        }

        return $this->_set_error(['team' => [__('team.update_error')]]);
    }

    /**
     * Update Team Settings
     *
     * @param  TeamsUpdateSettingsRequest $request
     * @param  string $subdomain
     * @param  integer|boolean $id
     *
     * @return \Illuminate\Http\Response
     */
    public function updateTeamSettings(TeamsUpdateSettingsRequest $request, $subdomain, $id = false)
    {
        $users = [];
        $isSiteAdmin = false;
        $team_admin = false;
        $team = Team::where(['id' => (int)$id])->first();
        $data = $request->only(['admins', 'belongs_to', 'team_name']);

        foreach ($data['admins'] as $admin) {
            $user = User::where('email', $admin)->first();

            if (!$user) {
                $newUser['email'] = $admin;
                $newUser['contact_email'] = $admin;

                $user = $this->createNewUser($newUser, Auth::user()->last_seen_org_id);

                if (!$user) {
                    return $this->_set_error(['team' => [__('team.update_error')]], 422);
                }
            } else {
                $userOrg = UsersOrganisations::where([
                    'organisation_id' => Auth::user()->last_seen_org_id,
                    'user_id' => $user->id,
                ])->count();

                if (!$userOrg) {
                    return $this->_set_error(['team' => [__('team.update_error')]], 422);
                }
            }

            $users[] = $user->id;
        }

        $isOrgAdmin = (bool)UsersOrganisations::where([
            'organisation_id' => Auth::user()->last_seen_org_id,
            'user_id' => Auth::user()->id,
            'is_admin' => 1
        ])->count();

        if ($request->belongs_to) {
            $isSiteAdmin = (bool)UsersSite::where([
                'site_id' => $request->belongs_to,
                'user_id' => Auth::user()->id,
                'is_admin' => 1
            ])->count();
        }

        if ($team) {
            $team_admin = (bool)UsersTeam::where(['user_id' => Auth::user()->id, 'team_id' => (int)$id, 'is_admin' => 1])->count();

            if (!empty($team->site_id) && !$isSiteAdmin) {
                $isSiteAdmin = (bool)UsersSite::where([
                    'site_id' => $team->site_id,
                    'user_id' => Auth::user()->id,
                    'is_admin' => 1
                ])->count();
            }
        }

        if ($isSiteAdmin || $isOrgAdmin || $team_admin) {
            //process team:
            if (!$team) {
                $team = new Team();
                $team->organisation_id = Auth::user()->last_seen_org_id;
            }

            $team->name = $data['team_name'];
            if (!empty($data['belongs_to'])) {
                $team->site_id = $data['belongs_to'];
            } else {
                $team->site_id = null;
            }
            $team->save();

            //process admins
            UsersTeam::where('team_id', $team->id)->update(['is_admin' => 0]);

            if ($team->site_id) {
                $oldUsers = [];
                $newUsers = [];
                foreach (UsersTeam::where(['team_id' => $team->id, 'is_admin' => 0])->select('user_id')->get() as $ou) {
                    $oldUsers[] = $ou->user_id;
                }

                $survivors = UsersSite::select('user_id')->where(['site_id' => $team->site_id])->get();

                UsersTeam::where('team_id', $team->id)->whereNotIn('user_id', $survivors)->delete();

                foreach (UsersTeam::where(['team_id' => $team->id, 'is_admin' => 0])->select('user_id')->get() as $ou) {
                    $newUsers[] = $ou->user_id;
                }

                $this->removeUserFromCourses(array_diff($oldUsers, $newUsers), $team->id);
            }

            if (!empty($data['admins'])) {
                foreach ($users as $user) {
                    $ut = UsersTeam::firstOrNew(['team_id' => $team->id, 'user_id' => $user]);
                    $ut->is_admin = 1;
                    $ut->save();
                }
            } else {
                // if no admins been set then current user will be admin
                $ut = UsersTeam::firstOrNew(['team_id' => $team->id, 'user_id' => Auth::user()->id]);
                $ut->is_admin = 1;
                $ut->save();
            }

            $img = $request->logo['src'];

            if(substr_count($img, 'data:image')){
                $base64 = explode(',', $img)[1]; 
                // Get valid base64 from received string
                // [0] => "data:image/png;base64,
                // [1] => <valid base64>
                $image = Image::make(base64_decode($base64));
            }

            if (isset($image) && !is_string($image) && in_array($image->mime(), self::MIMES)) {
                $s3Path = env('AWS_S3_PROJECT_PATH', false) . "/sites/{$team->id}/logo/";

                $img_name = $this->uploadFileBase64($img, $s3Path);

                $path = env('AWS_S3_PATH', false) . $s3Path . $img_name;

                if (!empty($team->logo->id)) {
                    $team->logo()->update(['src' => $path]);

                    $file = Files::find($team->logo->id);
                } else {
                    $file = Files::create([
                        'src' => $path,
                        'user_id' => Auth::user()->id
                    ]);
                    $team->logo()->associate($file);
                    $team->save();
                }

                ImageThumbnailProcess::dispatch($file, $s3Path, 'logo');
            }

            return $this->_set_success(Team::where(['id' => (int)$id])->first());
        }

        return $this->_set_error(['team' => [__('team.update_error')]]);
    }

    /**
     * Update Team Members
     *
     * @param  TeamUpdateMembersRequest $request
     * @param  string $subdomain
     * @param  integer $id
     *
     * @return \Illuminate\Http\Response
     */
    public function updateTeamMembers(TeamUpdateMembersRequest $request, $subdomain, $id)
    {
        $isSiteAdmin = false;
        $team_admin = false;
        $team = Team::find((int)$id);
        $data = $request->only('members');
        $users = [];
        $oldUsers = [];

        $isOrgAdmin = (bool)UsersOrganisations::where([
            'organisation_id' => Auth::user()->last_seen_org_id,
            'user_id' => Auth::user()->id,
            'is_admin' => 1
        ])->count();

        foreach ($data['members'] as $member) {
            $user = User::where('email', $member)->first();

            if (!$user) {
                $newUser['email'] = $member;
                $newUser['contact_email'] = $member;

                $user = $this->createNewUser($newUser, Auth::user()->last_seen_org_id);

                if (!$user) {
                    return $this->_set_error(['members' => [__('site.update_error')]], 422);
                }
            } else {
                $userOrg = UsersOrganisations::where([
                    'organisation_id' => Auth::user()->last_seen_org_id,
                    'user_id' => $user->id,
                ])->count();

                if (!$userOrg) {
                    return $this->_set_error(['members' => [__('site.update_error')]], 422);
                }
            }

            $users[] = $user->id;
        }

        if ($team) {
            $team_admin = UsersTeam::where(['user_id' => Auth::user()->id, 'team_id' => (int)$id, 'is_admin' => 1])->count();

            if (!empty($team->site_id)) {
                $isSiteAdmin = UsersSite::where([
                    'site_id' => $team->site_id,
                    'user_id' => Auth::user()->id,
                    'is_admin' => 1
                ])->count();
            }
        }

        if ($team && ($isSiteAdmin || $isOrgAdmin || $team_admin)) {
            foreach (UsersTeam::where(['team_id' => $team->id, 'is_admin' => 0])->select('user_id')->get() as $ou) {
                $oldUsers[] = $ou->user_id;
            }

            $this->removeUserFromCourses(array_diff($oldUsers, $users), $team->id);

            $this->addUsersToCourses(array_diff($users, $oldUsers), $team->id);

            UsersTeam::where(['team_id' => $team->id, 'is_admin' => 0])->delete();

            foreach ($users as $user) {
                $ut = UsersTeam::firstOrNew([
                    'user_id' => $user,
                    'team_id' => $team->id
                ]);
                $ut->is_admin = 0;
                $ut->save();
            }

            return $this->_set_success($team);

        }

        return $this->_set_error(['team' => [__('team.update_error')]]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $isSiteAdmin = false;
        $isOrgAdmin = false;

        $team_admin = (bool)UsersTeam::where(['user_id' => Auth::user()->id, 'team_id' => (int)$id, 'is_admin' => 1])->count();
        $team = UsersTeam::where(['team_id' => (int)$id])->first();

        if ($team->team->organisation->id == Auth::user()->last_seen_org_id) {
            $isOrgAdmin = (bool)UsersOrganisations::where([
                'organisation_id' => Auth::user()->last_seen_org_id,
                'user_id' => Auth::user()->id,
                'is_admin' => 1
            ])->count();
        }

        if (!empty($team->team->site)) {
            $isSiteAdmin = (bool)UsersSite::where([
                'site_id' => $team->team->site->id,
                'user_id' => Auth::user()->id,
                'is_admin' => 1
            ])->count();
        }

        if ($isSiteAdmin || $isOrgAdmin || $team_admin) {
            $data_del = Team::find($id)->delete();
            if ($data_del) {
                return $this->_set_success(['team' => [__('team.destroy')]]);
            }
        } else {
            return $this->_set_error(['team' => [__('team.destroy_error')]]);
        }

    }

    /**
     * Remove User From Courses
     *
     * @param  array $usersIds
     * @param  int $teamId
     */
    private function removeUserFromCourses($usersIds, $teamId)
    {
        foreach ($usersIds as $user) {
            UsersCourse::where([
                'team_id' => $teamId,
                'user_id' => $user,
                'is_team_assigned' => 1
            ])->delete();
        }
    }

    /**
     * Add Users to Courses
     *
     * @param  array $usersIds
     * @param  int $teamId
     */
    private function addUsersToCourses($usersIds, $teamId)
    {
        $marketplaces = Marketplace::where([
            'team_id' => $teamId,
            'is_wildcard_assigned' => 1,
            'organisation_id' => Auth::user()->last_seen_org_id
        ])->groupBy('course_id')->get();

        foreach ($marketplaces as $marketplace) {
            foreach ($usersIds as $user) {
                UsersCourse::firstOrCreate([
                    'organisation_id' => Auth::user()->last_seen_org_id,
                    'team_id' => $marketplace->team_id,
                    'course_id' => $marketplace->course_id,
                    'user_id' => $user,
                    'marketplace_id' => $marketplace->id,
                    'assigned_by_id' => Auth::user()->id,
                    'is_obligatory' => 1,
                    'is_team_assigned' => 1
                ]);
            }
        }
    }
}
