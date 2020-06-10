<?php

namespace App\Http\Controllers\Cms;

use App\Mail\NewUser;
use App\Models\User;
use App\Models\Files;
use App\Models\Organisation;
use App\Models\UsersOrganisations;
use App\Models\Subscriptions;
use App\Http\Requests\Cms\Organisations\OrganisationUpdateRequest;
use App\Http\Requests\Cms\Organisations\OrganisationCreateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use function foo\func;

class OrganisationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchString = $request->get('searchString');

        $organisations = Organisation::where('name', 'like', '%' . $searchString . '%')
            ->orWhere('email', 'like', '%' . $searchString . '%')->paginate(20);

        return $this->_set_success($organisations);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        return $this->_set_success(Organisation::select('id', 'name')->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OrganisationCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(OrganisationCreateRequest $request)
    {
        $user = User::where('email', $request->user_email)->first();

        if (!$user) {
            $user = new User();

            $data['email'] = $request->user_email;
            $data['contact_email'] = $request->user_contact_email;
            $data['password'] = Hash::make(md5(time() . $data['email']));
            $data['confirm_code'] = md5($data['email'] . time());

            foreach ($data as $key => $param) {
                $user[$key] = $param;
            }

            $user->save();
            Mail::to($user->email)
                ->queue(new NewUser($user));
        }

        if ($user) {
            $organisation = Organisation::create([
                'email' => $request->org_email,
                'name' => $request->org_name
            ]);

            UsersOrganisations::create([
                'user_id' => $user->id,
                'organisation_id' => $organisation->id,
                'is_admin' => true,
                'is_owner' => true,
            ]);

            return $this->_set_success($organisation);
        } else {
            return $this->_set_error(['user' => 'Organisation not created']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param string $subdomain
     * @param int $id
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function show(string $subdomain, int $id)
    {
        return $this->_set_success(Organisation::find((int)$id));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param OrganisationUpdateRequest $request
     * @param string $subdomain
     * @param integer $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(OrganisationUpdateRequest $request, string $subdomain, int $id)
    {
        $data = $request->only(
            'email',
            'name',
            'phone_number',
            'state',
            'city',
            'street',
            'zip'
        );
        $data_id = Organisation::find((int)$id);

        if ($data_id) {

            foreach ($data as $key => $param) {
                $data_id[$key] = $param;
            }
            $data_id->save();

            if ($request->has('_cover_picture')) {
                $path = env('AWS_S3_PATH', false) . $request->file('_cover_picture')->storePublicly(
                        env('AWS_S3_PROJECT_PATH', false) . "/organisations/{$id}/cover_picture", 's3'
                    );

                if (!empty($data_id->cover_picture->id)) {
                    $data_id->cover_picture()->update(['src' => $path]);
                } else {
                    $file = Files::create(['src' => $path]);
                    $data_id->cover_picture()->associate($file);
                    $data_id->save();
                }
            }

            if ($request->has('_logo')) {
                $path = env('AWS_S3_PATH', false) . $request->file('_logo')->storePublicly(
                        env('AWS_S3_PROJECT_PATH', false) . "/organisations/{$id}/logo", 's3'
                    );

                if (!empty($data_id->logo->id)) {
                    $data_id->logo()->update(['src' => $path]);
                } else {
                    $file = Files::create(['src' => $path]);
                    $data_id->logo()->associate($file);
                    $data_id->save();
                }
            }

            return $this->_set_success(Organisation::find((int)$id));
        } else {
            return $this->_set_error(['user' => 'Organisation not update']);

        }
    }

    /**
     * Get users of organisation
     *
     * @param string $subdomain
     * @param int $org_id
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function usersOrg(string $subdomain, int $org_id)
    {
        if (empty($org_id)) {
            return $this->_set_error(['user' => 'Organisation not found']);
        }
        
        $users_data = User::select('users.*', 'users_organisations.is_owner')
            ->join('users_organisations', function ($join) use ($org_id) {
                $join->where('users_organisations.organisation_id', (int)$org_id);
            })->where('users.id', DB::raw('users_organisations.user_id'))->get();
        

        return $this->_set_success($users_data);
    }

    /**
     * Get organisations snapshot
     *
     * @return \Illuminate\Http\Response
     */
    public function orgSnapshotCsv()
    {
        $organisations = Organisation::select('name', 'email', 'phone_number', 'state', 'city', 'street')->get();

        $organisations = json_decode(json_encode($organisations), true);

        $csv = Excel::create('users', function ($excel) use ($organisations) {
            $excel->sheet('mySheet', function ($sheet) use ($organisations) {
                $sheet->fromArray($organisations);
            });
        })->download('csv');

        return $this->_set_success($csv);
    }

    /**
     * Update organisation`s owner
     * @param string $subdomain
     * @param Request $request
     * @param int $org_id
     *
     * @return \Illuminate\Http\Response
     */

    public function updateOwner(string $subdomain, Request $request, int $org_id )
    {
        if (empty($org_id)) {
            return $this->_set_error(['user' => 'Organisation not found']);
        }
        
        $old_owner = [];
        $sub_user_id = [];
        $user = $request->all();

        $subscriptions = Subscriptions::select('user_id')->orderBy('updated_at')->get();

        foreach ($subscriptions as $sub) {
            $sub_user_id[] = $sub['user_id'];
        }

        //Get data of old owner if he had a subscription
        if (!empty($sub_user_id)) {
            $old_owner = UsersOrganisations::select('users_organisations.user_id', 'subscriptions.id as sub_id')
                ->join('subscriptions', function ($join) {
                    $join->on('subscriptions.user_id', '=', 'users_organisations.user_id');
                })
                ->whereIn('users_organisations.user_id', $sub_user_id)
                ->where('users_organisations.organisation_id', $org_id)
                ->where('users_organisations.is_owner', '1')->first();
        }

        //Update owner of organisation and delete other owners from this organisation
        UsersOrganisations::where('user_id', $user['id'])->where('organisation_id', $org_id)->update(['is_owner' => $user['is_owner']]);
        UsersOrganisations::where('user_id', $user['id'])->where('organisation_id', $org_id)->update(['is_admin' => 1 ]);
        UsersOrganisations::where('user_id', '!=', $user['id'])->where('organisation_id', $org_id)->update(['is_owner' => 0]);

        //If the old owner had a subscription , transfer it to the new owner
        if (!empty($old_owner)) {

            Subscriptions::where('id', $old_owner->sub_id)->update(['user_id' => $user['id']]);

            $old_owner_data = User::where('id', $old_owner->user_id)->first()->toArray();

            User::where('id', $old_owner->user_id)->update(['stripe_id' => null, 'card_brand' => null, 'card_last_four' => null]);

            User::where('id', $user['id'])
                ->update(['stripe_id' => $old_owner_data['stripe_id'],
                    'card_brand' => $old_owner_data['card_brand'],
                    'card_last_four' => $old_owner_data['card_last_four'],
                    'trial_ends_at' => $old_owner_data['trial_ends_at']]);
        }

        return $this->_set_success($request->all());
    }

}
