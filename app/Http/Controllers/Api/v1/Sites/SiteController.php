<?php

namespace App\Http\Controllers\API\v1\Sites;

use App\Http\Requests\API\v1\Sites\SiteSettingsUpdateRequest;
use App\Http\Requests\API\v1\Sites\SiteMembersUpdateRequest;
use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\User;
use App\Models\UsersOrganisations;
use App\Models\UsersSite;
use App\Models\Files;
use Auth;
use Image;
use App\Jobs\ImageThumbnailProcess;
use App\Http\Traits\ManageableTrait;
use App\Http\Traits\UploadFileTrait;

class SiteController extends Controller
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

        if ($org_admin) {
            //get all sites if guy is current organisation admin
            $admin_sites = Site::where('organisation_id', Auth::user()->last_seen_org_id)->get();
        } else {
            //get all sites where current guy might be admin
            $admin_sites = Site::hydrate(
                UsersSite::select('sites.*')
                    ->join('sites', function ($join) {
                        $join->on('sites.id', '=', 'users_sites.site_id');
                    })
                    ->where('users_sites.user_id', Auth::user()->id)
                    ->where('users_sites.is_admin', 1)
                    ->where('sites.organisation_id', Auth::user()->last_seen_org_id)
                    ->get()->toArray()
            )->load('logo');
        }

        //get all sites where current guy might be a member
        $member_sites = Site::hydrate(
            UsersSite::select('sites.*')
                ->join('sites', function ($join) {
                    $join->on('sites.id', '=', 'users_sites.site_id');
                })
                ->where('users_sites.user_id', Auth::user()->id)
                ->where('sites.organisation_id', Auth::user()->last_seen_org_id)
                ->get()->toArray()
        )->load('logo');

        $merged_sites = array();

        foreach ($admin_sites as $admin_site) {
            $merged_sites['site_' . $admin_site->id] = [
                'site' => $admin_site,
                'is_member' => 0,
                'is_admin' => 1
            ];
        }

        foreach ($member_sites as $member_site) {
            if (isset($merged_sites['site_' . $member_site->id])) {
                $merged_sites['site_' . $member_site->id]['is_member'] = 1;
                continue;
            }
            $merged_sites['site_' . $member_site->id] = [
                'site' => $member_site,
                'is_member' => 1,
                'is_admin' => 0
            ];
        }

        //forging response
        $response = [];
        $response['can_create'] = $org_admin ? 1 : 0;
        $response['sites'] = array();

        foreach ($merged_sites as $ms) {
            $ms['count_members'] = UsersSite::where('site_id', $ms['site']->id)->count();
            $ms['member_preview'] = $ms['site']->users()
                ->inRandomOrder()->limit(9)->get()->toArray();
            $ms['site']['logo'] = $ms['site']->logo()->first();
            $response['sites'][] = $ms;
        }

        return $this->_set_success($response);
    }

    /**
     * Get Site Settings Config
     *
     * @param string $subdomain
     * @param integer|boolean $id
     *
     * @return \Illuminate\Http\Response
     */
    public function getSiteSettingsConfig($subdomain, $id = false)
    {
        $isOrgAdmin = UsersOrganisations::where([
            'organisation_id' => Auth::user()->last_seen_org_id,
            'user_id' => Auth::user()->id,
            'is_admin' => 1
        ])->count();

        $canEdit = UsersSite::where([
            'site_id' => (int)$id,
            'user_id' => Auth::user()->id,
            'is_admin' => 1
        ])->count();

        $site = Site::find((int)$id);

        if (!($isOrgAdmin || $canEdit)) {
            return $this->_set_error(['site' => [__('site.show_error')]], 422);
        }

        $response['site'] = [];
        $response['admins'] = [];
        $response['users'] = User::whereIn('id',
            UsersOrganisations::select('user_id')
                ->where(['organisation_id' => Auth::user()->last_seen_org_id])
                ->get()->makeHidden(['organisation'])
        )->get()->makeHidden(['avatar']);

        if ($site) {
            $response['site'] = Site::find((int)$id);
            $response['admins'] = User::whereIn('id',
                UsersSite::select('user_id')
                    ->where(['site_id' => $id, 'is_admin' => 1])
                    ->get()
            )->get()->makeHidden(['avatar']);
        }

        return $this->_set_success($response);
    }

    /**
     * Get Site Members Config
     *
     * @param string $subdomain
     * @param integer $id
     *
     * @return \Illuminate\Http\Response
     */
    public function getSiteMembersConfig($subdomain, $id)
    {
        $isOrgAdmin = UsersOrganisations::where([
            'organisation_id' => Auth::user()->last_seen_org_id,
            'user_id' => Auth::user()->id,
            'is_admin' => 1
        ])->count();

        $canEdit = UsersSite::where([
            'site_id' => (int)$id,
            'user_id' => Auth::user()->id,
            'is_admin' => 1
        ])->count();

        if (!Site::find((int)$id) || !($isOrgAdmin || $canEdit)) {
            return $this->_set_error(['site' => [__('site.show_error')]]);
        }
        
        $response = array();
        $users = [];
        $admins = [];

        foreach (UsersSite::select('user_id')
                     ->where(['site_id' => $id, 'is_admin' => 1])
                     ->get() as $admin) {
            @$admins[] = $admin['user_id'];
        }

        foreach (UsersOrganisations::select('user_id')
                     ->where(['organisation_id' => Auth::user()->last_seen_org_id])
                     ->get() as $user) {
            @$users[] = $user['user_id'];
        }
        $users = array_diff($users, $admins);

        $response['site'] = Site::where(['id' => $id])->first();
        $response['site_members'] = User::whereIn('id',
            UsersSite::select('user_id')
                ->where(['site_id' => $id, 'is_admin' => 0])
                ->get()
        )->get()->makeHidden(['avatar']);

        $response['org_members'] = User::whereIn('id', $users)->get();

        return $this->_set_success($response);
    }

    /**
     * Store or update Site Settings
     *
     * @param SiteSettingsUpdateRequest $request
     * @param string $subdomain
     * @param integer|boolean $id
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSiteSettings(SiteSettingsUpdateRequest $request, $subdomain, $id = false)
    {
        $users = [];
        $isOrgAdmin = UsersOrganisations::where([
            'organisation_id' => Auth::user()->last_seen_org_id,
            'user_id' => Auth::user()->id,
            'is_admin' => 1
        ])->count();

        if (!$isOrgAdmin) {
            return $this->_set_error(['site' => [__('site.update_error')]], 422);
        }

        $data = $request->only(['site_name', 'admins', 'logo']);

        foreach ($data['admins'] as $admin) {
            $user = User::where('email', $admin)->first();

            if (!$user) {
                $newUser['email'] = $admin;
                $newUser['contact_email'] = $admin;

                $user = $this->createNewUser($newUser, Auth::user()->last_seen_org_id);

                if (!$user) {
                    return $this->_set_error(['site' => [__('site.update_error')]], 422);
                }
            } else {
                $userOrg = UsersOrganisations::where([
                    'organisation_id' => Auth::user()->last_seen_org_id,
                    'user_id' => $user->id,
                ])->count();

                if (!$userOrg) {
                    return $this->_set_error(['site' => [__('site.update_error')]], 422);
                }
            }

            $users[] = $user->id;
        }

        $site = new Site();
        $site->organisation_id = Auth::user()->last_seen_org_id;

        if ((int)$id) {
            $site = Site::where([
                'id' => (int)$id,
                'organisation_id' => Auth::user()->last_seen_org_id
            ])->first();

            $org_admin = UsersOrganisations::where([
                'organisation_id' => Auth::user()->last_seen_org_id,
                'user_id' => Auth::user()->id,
                'is_admin' => 1
            ])->count();

            $site_admin = UsersSite::where([
                'site_id' => (int)$id,
                'user_id' => Auth::user()->id,
                'is_admin' => 1
            ])->count();

            if (!$site || (!$site_admin && !$org_admin)) {
                return $this->_set_error(['site' => [__('site.update_error')]]);
            }
        }

        $site->name = $data['site_name'];
        $site->save();

        // update site admins
        UsersSite::where('site_id', $site->id)->update(['is_admin' => 0]);

        if (!empty($data['admins'])) {
            foreach ($users as $user) {
                $us = UsersSite::firstOrNew(['site_id' => $site->id, 'user_id' => $user]);
                $us->is_admin = 1;
                $us->save();
            }
        } else {
            //set current user as admin if none were selected
            $us = UsersSite::firstOrNew(['site_id' => $site->id, 'user_id' => Auth::user()->id]);
            $us->is_admin = 1;
            $us->save();
        }

        // Update/Upload Logo
        $img = $request->logo['src'];

        if(substr_count($img, 'data:image')){
            $base64 = explode(',', $img)[1]; 
            // Get valid base64 from received string
            // [0] => "data:image/png;base64,
            // [1] => <valid base64>
            $image = Image::make(base64_decode($base64));
        }

        if (isset($image) && !is_string($image) && in_array($image->mime(), self::MIMES)) {
            $s3Path = env('AWS_S3_PROJECT_PATH', false) . "/sites/{$site->id}/logo/";

            $img_name = $this->uploadFileBase64($img, $s3Path);

            $path = env('AWS_S3_PATH', false) . $s3Path . $img_name;

            if (!empty($site->logo->id)) {
                $site->logo()->update(['src' => $path]);

                $file = Files::find($site->logo->id);
            } else {
                $file = Files::create([
                    'src' => $path,
                    'user_id' => Auth::user()->id
                ]);
                $site->logo()->associate($file);
                $site->save();
            }

            ImageThumbnailProcess::dispatch($file, $s3Path, 'logo');
        }

        $site = Site::where([
            'id' => (int)$id,
            'organisation_id' => Auth::user()->last_seen_org_id
        ])->first();

        return $this->_set_success($site);
    }

    /**
     * Store or update Site Members
     *
     * @param SiteMembersUpdateRequest $request
     * @param string $subdomain
     * @param integer $id
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSiteMembers(SiteMembersUpdateRequest $request, $subdomain, $id)
    {
        $users = [];
        $isOrgAdmin = UsersOrganisations::where([
            'organisation_id' => Auth::user()->last_seen_org_id,
            'user_id' => Auth::user()->id,
            'is_admin' => 1
        ])->count();

        $canEdit = UsersSite::where([
            'site_id' => (int)$id,
            'user_id' => Auth::user()->id,
            'is_admin' => 1
        ])->count();

        if (!Site::find((int)$id) || !($isOrgAdmin || $canEdit)) {
            return $this->_set_error(['members' => [__('site.update_error')]], 422);
        }

        foreach ($request->members as $member) {
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

        UsersSite::where(['site_id' => (int)$id, 'is_admin' => 0])->delete();

        foreach ($users as $user) {
            $us = UsersSite::firstOrNew(['site_id' => (int)$id, 'user_id' => $user]);
            $us->is_admin = 0;
            $us->save();
        }

        return $this->_set_success(Site::find((int)$id));
    }
}
