<?php

namespace App\Http\Controllers\API\v1\Organisations;

use Auth;
use App\Http\Requests\API\v1\Organisations\OrganisationsCreateRequest;
use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\UsersOrganisations;
use App\Models\User;
use App\Mail\SelfSignUp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Http\Traits\UploadFileTrait;

class OrganisationController extends Controller
{
    use UploadFileTrait;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->_set_success(Organisation::with(['sites', 'usersAdmins'])->get());
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  OrganisationsCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(OrganisationsCreateRequest $request)
    {
        $data = $request->only('first_name', 'last_name', 'email');
        $data['password'] = Hash::make(time() . $data['email']);
        $data['confirm_code'] = md5($data['email'] . time());
        $data['trial_ends_at'] = Carbon::now()->addDays(30);

        $user = User::create($data);

        if ($user) {
            $organisation = new Organisation();
            $organisation->name = $request->org_name;
            $organisation->email = $request->email;
            $organisation->save();

            if ($organisation) {
                UsersOrganisations::create([
                    'user_id' => $user->id,
                    'organisation_id' => $organisation->id,
                    'is_admin' => 1,
                    'is_owner' => 1,
                ]);
                
                Mail::to($user->email)
                    ->queue(new SelfSignUp($user));
                
                return $this->_set_success($user);
            }
        }

        return $this->_set_error(['organisation' => [__('organisation.store_error')]]);
    }

    /**
     * Display the specified resource.
     *
     * @param  string $subdomain
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($subdomain, $id)
    {
        $data = Organisation::find($id);
        if ($data) {
            return $this->_set_success($data);
        } else {
            return $this->_set_error(['organisation' => [__('organisation.show_error', ['id' => $id])]]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data_id = Organisation::find($id);
        if ($data_id) {
            $data_del = Organisation::find($id)->delete();
            if ($data_del) {
                return $this->_set_success(['organisation' => [__('organisation.destroy')]]);
            }
        } else {
            return $this->_set_error(['organisation' => [__('organisation.destroy_error')]]);
        }
    }
}
