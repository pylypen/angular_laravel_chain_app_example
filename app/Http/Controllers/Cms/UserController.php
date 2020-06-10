<?php

namespace App\Http\Controllers\Cms;

use App\Mail\NewUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Cms\Users\UserCreateRequest;
use App\Http\Requests\Cms\Users\UserUpdateRequest;
use App\Http\Requests\Cms\Users\UserUpdateOrgSettingRequest;
use App\Http\Requests\Cms\Users\UserAdminUpdateRequest;
use App\Http\Requests\Cms\Users\UserAdminCreateRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Files;
use App\Models\UsersOrganisations;
use App\Models\Organisation;
use Illuminate\Support\Facades\Hash;
use App\Models\UsersSecretAnswer;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ManageableTrait;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    use ManageableTrait;

    /**
     * Display a listing of the resource.
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchString = $request->get('searchString');

        $users = User::select('users.*', 'organisations.name as org_name', 'organisations.id as org_id', DB::raw('DATEDIFF(users.trial_ends_at,CURRENT_DATE()) as trial_remaining'))
            ->leftJoin('users_organisations', 'users_organisations.user_id', '=', 'users.id')
            ->leftJoin('organisations', 'organisations.id', '=', 'users_organisations.organisation_id')
            ->where(function ($query) use ($searchString) {
                $query->orWhere('users.first_name', 'like', '%' . $searchString . '%');
                $query->orWhere('users.last_name', 'like', '%' . $searchString . '%');
                $query->orWhere('organisations.name', 'like', '%' . $searchString . '%');
                $query->orWhere('users.email', 'like', '%' . $searchString . '%');
            })
            ->where('users.is_internal', 0)
            ->paginate(20);

        foreach ($users as $user) {
            if ($user->trial_remaining < 0) {
                $user->trial_remaining = 0;
            }
        }

        return $this->_set_success($users);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admins()
    {
        return $this->_set_success(User::where('is_internal', 1)->paginate(20));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param UserCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(UserCreateRequest $request)
    {
        $data = $request->only('email', 'contact_email');

        $user = $this->createNewUser($data, $request->organisation);

        if ($user) {
            return $this->_set_success($user);
        }

        return $this->_set_error(['user' => 'User not created']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserAdminCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function storeAdmin(UserAdminCreateRequest $request)
    {
        $data = $request->only('first_name', 'last_name', 'email', 'contact_email', 'password');
        $data_id = new User();

        foreach ($data as $key => $param) {
            $data_id[$key] = $param;
        }

        $data_id->password = Hash::make($data['password']);
        $data_id->is_internal = 1;
        $data_id->save();
        if ($data_id) {
            return $this->_set_success($data_id);
        } else {
            return $this->_set_error(['user' => 'User not created']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param string $subdomain
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(string $subdomain, int $id)
    {
        $data = [];
        $data['user'] = User::find((int)$id);

        if (!$data['user']->is_internal) {
            $data['orgs'] = UsersOrganisations::select('*', 'users_organisations.id')->join('organisations', function ($join) {
                $join->on('organisations.id', '=', 'users_organisations.organisation_id');
            })->where(['users_organisations.user_id' => $id])
                ->get()->toArray();

            foreach ($data['orgs'] as $k => $org) {
                $is = 1;
                if ($org['is_owner']) {
                    $is = UsersOrganisations::where([
                        'is_owner' => 1,
                        'organisation_id' => $org['organisation']['id'],
                    ])->where('user_id', '<>', (int)$id)->count();
                } else if ($org['is_admin']) {
                    $is = UsersOrganisations::where([
                        'is_owner' => 1,
                        'organisation_id' => $org['organisation']['id'],
                    ])->count();
                }

                $name = Organisation::find($org['organisation']['id']);

                $data['orgs'][$k]['can_exit'] = $is;
                $data['orgs'][$k]['name'] = $name->name;
            }
        }


        if (empty($data)) {
            return $this->_set_error($data);
        }

        return $this->_set_success($data);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param UserUpdateRequest $request
     * @param string $subdomain
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UserUpdateRequest $request, string $subdomain, int $id)
    {

        $data = $request->only(
            'first_name', 'last_name', 'contact_email', 'phone_number', 'birthday', 'nickname', 'trial_ends_at'
        );

        $user = User::find((int)$id);

        $validator = Validator::make($data, [
            'nickname' => Rule::unique('users')->ignore($user->id, 'id')
        ]);
        
        if ($validator->fails()) {
            return $this->_set_error(['nickname' => 'The nickname has already been taken.'], 422);
        }

        if ($user) {
            if ($request->email != $user->email && User::where('email', $request->email)->count()) {
                return $this->_set_error(['email' => ['This email has already been taken.']]);
            }

            foreach ($data as $key => $param) {
                $user[$key] = $param;
            }

            if(!empty($data['trial_ends_at']) ){
                $user->trial_ends_at = $data['trial_ends_at'];
            }else{
                $user->trial_ends_at = date('Y-m-d H:i:s');
            }


            $user->save();

            if ($request->has('avatar')) {
                $path = env('AWS_S3_PATH', false) . $request->file('avatar')
                        ->storePublicly(env('AWS_S3_PROJECT_PATH', false) . "/users/{$id}/avatar", 's3');

                if (!empty($data->avatar->id)) {
                    $user->avatar()->update(['src' => $path]);
                } else {
                    $file = Files::create([
                        'src' => $path,
                        'user_id' => $user->id
                    ]);
                    $user->avatar()->associate($file);
                    $user->save();
                }
            }

            return $this->_set_success($user);
        } else {
            return $this->_set_error(['user' => ['User not update']]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserAdminUpdateRequest $request
     * @param string $subdomain
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function updateAdmins(UserAdminUpdateRequest $request, string $subdomain, int $id)
    {
        $data = $request->only('first_name', 'last_name', 'email', 'contact_email');
        $user = User::find((int)$id);

        if ($user) {
            if ($request->email != $user->email && User::where('email', $request->email)->count()) {
                return $this->_set_error(['email' => ['This email has already been taken.']]);
            }

            foreach ($data as $key => $param) {
                $user[$key] = $param;
            }
            $user->save();

            return $this->_set_success($user);
        } else {
            return $this->_set_error(['user' => ['User not update']]);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param string $subdomain
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $subdomain, int $id)
    {
        $user = User::find((int)$id);

        if ($user && !$user->is_internal) {
            $userOrgs = UsersOrganisations::where('user_id', (int)$id)->get();
            foreach ($userOrgs as $uo) {
                if (UsersOrganisations::where('organisation_id', $uo->organisation_id)->count() <= 1) {
                    return $this->_set_error(['user' => 'You can\'t delete last user in organisation'], 422);
                }
            }
            $user->email .= '_deleted_' . time();
            $user->nickname .= '_deleted_' . time();
            $user->password = NULL;
            $user->save();
            $user = User::find((int)$id)->delete();

            if ($user) {
                return $this->_set_success(['user' => 'User deleted']);
            }
        } else {
            return $this->_set_error(['user' => 'User not found']);
        }
    }

    /**
     * Send Activate Link
     *
     * @param mixed $data_id
     *
     */

    public function sendActivateLink($data_id)
    {

        Mail::to($data_id->email)
            ->queue(new NewUser($data_id));

    }

    /**
     * Generate Confirm Code
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function generateConfirmCode(Request $request)
    {
        $id = $request->only('id');
        $data_id = User::find((int)$id);
        if ($data_id) {
            if ($user_sec_questions = UsersSecretAnswer::where('user_id', '=', $id)->get()) {
                foreach ($user_sec_questions as $user_sec_question)
                    $user_sec_question->delete();
            }
            $data_id->password = '';
            $data_id->remember_token = '';
            $data_id->confirm_code = str_random();
            $data_id->save();
            $response = $this->sendActivateLink($data_id);

            return $this->_set_success(['response' => $response]);
        } else {
            return $this->_set_error(['user ' => 'User  not found', ['id' => $id]]);
        }
    }

    /**
     * Delete User Organisation
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function deleteUserOrganisation(Request $request)
    {
        $id_org = $request->only('id_org_user');
        $id_user = $request->only('user_id');
        $count_org_on_user = UsersOrganisations::where('user_id', $id_user)->count();
        if ($count_org_on_user > 1) {
            UsersOrganisations::where('id', $id_org)->delete();
            return $this->_set_success(['response' => 'User from organisation success delete']);
        } else {
            return $this->_set_error(['user_organisation' => 'User organisation not found or can`t delete']);
        }
    }

    /**
     * Set organisation setting
     *
     * @param UserUpdateOrgSettingRequest $request
     * @return \Illuminate\Http\Response
     */
    public function setOrganisationSetting(UserUpdateOrgSettingRequest $request)
    {
        print_r($request->all());
        $exist = UsersOrganisations::where([
            'id' => $request->id_org_user,
            'organisation_id' => $request->org_id,
            'user_id' => $request->user_id
        ])->count();

        if (!$exist) {
            return $this->_set_error(['user_organisation' => "Can't set " . $request->type], 422);
        }

        if (!$request->set) {
            $count = 0;
            $type = '';
            switch ($request->type) {
                case 'is_owner':
                    $count = UsersOrganisations::where([
                        'organisation_id' => $request->org_id,
                        'is_owner' => 1
                    ])->where('user_id', '<>', $request->user_id)->count();
                    $type = 'owner';
                    break;
                case 'is_admin':
                    $count = UsersOrganisations::where([
                        'organisation_id' => $request->org_id,
                        'is_admin' => 1
                    ])->where('user_id', '<>', $request->user_id)->count();
                    $type = 'admin';
                    break;
            }

            if (!$count) {
                return $this->_set_error(['user_organisation' => "Organisation must have at least 1 " . $type], 422);
            }
        }

        UsersOrganisations::find($request->id_org_user)->update([$request->type => $request->set]);

        return $this->_set_success(['response' => 'User setting organisation success edited']);
    }

    /**
     * Get Users snapshot Csv file
     *
     * @return \Illuminate\Http\Response
     */
    public function usersSnapshotCsv()
    {

        $users = User::select(
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.nickname',
            'organisations.name as org_name',
            DB::raw('DATEDIFF(users.trial_ends_at,CURRENT_DATE()) as trial_remaining'))
            ->leftJoin('users_organisations', 'users_organisations.user_id', '=', 'users.id')
            ->leftJoin('organisations', 'organisations.id', '=', 'users_organisations.organisation_id')
            ->where('users.is_internal', 0)
            ->get();

        foreach ($users as $user) {
            if ($user->trial_remaining < 0) {
                $user->trial_remaining = "0";
            }
        }

        $users = json_decode(json_encode($users), true);

        $csv = Excel::create('users', function ($excel) use ($users) {
            $excel->sheet('mySheet', function ($sheet) use ($users) {
                $sheet->fromArray($users);
            });
        })->download('csv');

        return $this->_set_success($csv);
    }
}
