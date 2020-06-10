<?php

namespace App\Http\Controllers\Api\v1\People;

use App\Http\Requests\API\v1\People\PeopleCreateUserRequest;
use App\Http\Requests\API\v1\People\PeopleUpdateUserRequest;
use App\Http\Controllers\Controller;
use App\Mail\NewUser;
use App\Models\Course;
use App\Models\CoursesContributors;
use App\Models\Files;
use App\Models\Marketplace;
use App\Models\User;
use App\Models\UsersCourse;
use App\Models\UsersOrganisations;
use App\Models\UsersSite;
use App\Models\UsersTeam;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\API\v1\People\PeopleToggleAdminRequest;
use Illuminate\Support\Facades\Mail;
use App\Http\Traits\ManageableTrait;
use Carbon\Carbon;

class PeopleController extends Controller
{
    use ManageableTrait;
    
    /**
     * List.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        if ($this->isAdminOrganisation()) {
            $list = self::getPeopleList();
            $list = $list->toArray();

            foreach ($list as $k=>$l) {
                $list[$k]['login_at'] = !empty($list[$k]['login_at']) ? Carbon::parse($list[$k]['login_at'])->format('Y/m/d H:i:s') : $list[$k]['login_at'];
            }
            
            return $this->_set_success($list);
        }
        
        return $this->_set_success([]);
    }

    /**
     * Toggle Admin
     *
     * @param  PeopleToggleAdminRequest $request
     * @param  string $subdomain
     * @param  int $user_id
     *
     * @return \Illuminate\Http\Response
     */
    public function toggleAdmin(PeopleToggleAdminRequest $request, $subdomain, $user_id)
    {
        $data = $request->only(['is_admin']);

        $user = User::where('id', $user_id)->first();
        if ($user) {
            if (in_array($user->id, self::getManageableUsers())) {

                if ($user_id != Auth::user()->id) {

                    $uo = UsersOrganisations::where([
                        'user_id' => $user->id,
                        'organisation_id' => Auth::user()->last_seen_org_id
                    ])->first();

                    if (!(int)$uo->is_owner) {

                        $uo->is_admin = (int)$data['is_admin'];
                        $uo->save();

                        return $this->_set_success(self::getPeopleList());
                    }
                }
            }

        }

        return $this->_set_error(['user_id' => [__('people.update_error')]], 422);
    }

    /**
     * Update User
     *
     * @param  PeopleUpdateUserRequest $request
     * @param  string $subdomain
     * @param  int $user_id
     *
     * @return \Illuminate\Http\Response
     */
    public function updateUser(PeopleUpdateUserRequest $request, $subdomain, $user_id)
    {
        $user = $user = User::where('id', $user_id)->first();
        if ($user) {
            if (in_array($user->id, self::getManageableUsers())) {

                if ($user_id != Auth::user()->id) {

                    $data = $request->only(['contact_email', 'first_name', 'last_name', 'phone_number', 'birthday']);

                    User::where(['id' => $user_id])->update($data);

                    return $this->_set_success(self::getPeopleList());
                }
            }
        }

        return $this->_set_error(['user_id' => [__('people.update_error')]], 422);
    }

    /**
     * Create User
     *
     * @param  PeopleCreateUserRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function createUser(PeopleCreateUserRequest $request)
    {

        $data = $request->only(['email', 'contact_email', 'first_name', 'last_name', 'phone_number', 'birthday']);

        $data['confirm_code'] = md5($data['email'] . time());

        if (!isset($data['contact_email']) || !strlen($data['contact_email'])) {
            $data['contact_email'] = $data['email'];
        }

        $user = User::onlyTrashed()->where([
            'email' => $data['email']
        ])->first();

        if ($user) {
            $user->restore();
            $user->update($data);
        } else {
            $user = User::create($data);
        }

        if ((int)$user->id) {

            UsersOrganisations::create([
                'user_id' => $user->id,
                'organisation_id' => Auth::user()->last_seen_org_id,
                'is_admin' => 0,
                'is_owner' => 0
            ]);

            Mail::to($user->email)
                ->queue(new NewUser($user));

            return $this->_set_success(self::getPeopleList());
        }

        return $this->_set_error(['user_id' => [__('people.store_error')]], 422);
    }

    /**
     * Re-invite User
     *
     * @param  string $subdomain
     * @param  int $user_id
     *
     * @return \Illuminate\Http\Response
     */
    public function reinvite($subdomain, $user_id)
    {
        $user= User::find((int)$user_id);

        if ($user) {
            if (in_array($user->id, self::getManageableUsers())) {
                $user->password = '';
                $user->remember_token = '';
                $user->confirm_code = str_random();
                $user->save();
                
                Mail::to($user->email)
                    ->queue(new NewUser($user));

                return $this->_set_success([]);
            }
        }

        return $this->_set_error(['user_id' => [__('people.update_error')]], 422);
    }

    // todo: Redo when will switch to multi-organisation. Post MVP.

    /**
     * Delete User
     *
     * @param  string $subdomain
     * @param  int $user_id
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteUser($subdomain, $user_id)
    {
        $user = User::where('id', $user_id)->first();

        if ($user) {
            if (in_array($user->id, self::getManageableUsers())) {

                if ($user_id != Auth::user()->id) {

                    /* check if deleting guy is an author of any courses */
                    $courses_count = Course::where(['author_id' => $user->id])->count();

                    if ($courses_count) {
                        Course::where(['author_id' => $user->id])
                            ->update([
                                'author_id' => Auth::user()->id,
                                'original_author_id' => $user->id
                            ]);
                    }

                    CoursesContributors::where(['user_id' => $user->id])->delete();

                    Files::where(['user_id' => $user->id])
                        ->update(['user_id' => Auth::user()->id]);

                    Marketplace::where(['reviewed_by' => $user->id])
                        ->update(['reviewed_by' => Auth::user()->id]);

                    UsersCourse::where([
                        'organisation_id' => Auth::user()->last_seen_org_id,
                        'user_id' => $user->id
                    ])->delete();

                    // keep user progress just in case

                    $uts = UsersTeam::where(['user_id' => $user->id, 'is_admin' => 1])->get();
                    if (count($uts)) {
                        foreach ($uts as $ut) {
                            $nut = UsersTeam::firstOrNew([
                                'user_id' => Auth::user()->id,
                                'team_id' => $ut->team_id
                            ]);
                            $nut->is_admin = 1;
                            $nut->is_owner = $ut->is_owner;
                            $nut->save();
                        }
                    }
                    UsersTeam::where(['user_id' => $user->id])->delete();

                    $uss = UsersSite::where(['user_id' => $user->id, 'is_admin' => 1])->get();
                    if (count($uss)) {
                        foreach ($uss as $us) {
                            $nut = UsersSite::firstOrNew([
                                'user_id' => Auth::user()->id,
                                'site_id' => $us->site_id
                            ]);
                            $nut->is_admin = 1;
                            $nut->save();
                        }
                    }
                    UsersSite::where(['user_id' => $user->id])->delete();

                    UsersOrganisations::where([
                        'user_id' => $user->id,
                        'organisation_id' => Auth::user()->last_seen_org_id,
                    ])->delete();

                    $user->nickname .= '_deleted_' .  time();
                    $user->password = NULL;
                    $user->save();

                    User::find((int)$user->id)->delete();

                    return $this->_set_success(self::getPeopleList());
                }
            }
        }

        return $this->_set_error(['user_id' => [__('people.destroy_error')]], 422);
    }

    /**
     * Get People List
     *
     * @return User
     */
    private static function getPeopleList()
    {
        return User::whereIn('id', self::getManageableUsers())
            ->with(
                [
                    'usersOrganisations' => function ($query) {
                        $query->select(['is_admin', 'is_owner', 'user_id']);
                        $query->where('organisation_id', Auth::user()->last_seen_org_id);
                    },
                    'usersSites' => function ($query) {
                        $query->select('site_id', 'user_id', 'is_admin');
                        $query->whereHas('site', function ($query) {
                            $query->where('organisation_id', Auth::user()->last_seen_org_id);
                        });
                    }, 'usersSites.site',
                    'usersTeams' => function ($query) {
                        $query->select('team_id', 'user_id', 'is_admin');
                        $query->whereHas('team', function ($query) {
                            $query->where('organisation_id', Auth::user()->last_seen_org_id);
                        });
                    }, 'usersTeams.team'
                ]
            )
            ->get();
    }

    /**
     * Get Manageable Users
     *
     * @return array
     */
    private static function getManageableUsers()
    {

        $users = UsersOrganisations::select('user_id')
            ->where([
                'organisation_id' => Auth::user()->last_seen_org_id,
            ])
            ->get()->toArray();

        $ret = [];

        if (!empty($users)) {
            foreach ($users as $u) {
                $ret[] = $u['user_id'];
            }
        }

        return $ret;
    }
}