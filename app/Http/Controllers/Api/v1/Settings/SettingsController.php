<?php

namespace App\Http\Controllers\Api\v1\Settings;

use App\Http\Requests\API\v1\Settings\AccountSettingsUpdateRequest;
use App\Http\Requests\API\v1\Settings\ChangePasswordUpdateRequest;
use App\Http\Requests\API\v1\Settings\PersonalInfoUpdateRequest;
use App\Http\Requests\API\v1\Settings\AddDeveloperAccessRequest;
use App\Http\Requests\API\v1\Settings\DeleteDeveloperAccessRequest;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use App\Models\DevelopersAccounts;
use App\Models\DevelopersAccountsOrganisations;
use App\Models\Files;
use App\Http\Traits\UploadFileTrait;
use App\Jobs\ImageThumbnailProcess;
use Image;
use Hash;

class SettingsController extends Controller
{
    use UploadFileTrait;

    const MIMES = [
        'image/png',
        'image/jpeg',
        'image/jpg'
    ];

    /**
     * Get Config
     *
     * @return mixed
     */
    public function getConfig()
    {
        $config = array();

        $config['organisation'] = Auth::user()->usersOrganisations()->where([
            'organisation_id' => Auth::user()->last_seen_org_id
        ])->first();

        return $this->_set_success($config);

    }

    /**
     * Get Personal Info
     *
     * @return mixed
     */
    public function getPersonalInfo()
    {
        $user = Auth::user();
        $return_data = [
            'contact_email' => $user->contact_email,
            'first_name' => ucfirst($user->first_name),
            'last_name' => ucfirst($user->last_name),
            'birthday' => $user->birthday,
            'phone_number' => $user->phone_number,
            'avatar' => $user->avatar
        ];
        return $this->_set_success($return_data);

    }

    /**
     * Update Personal Info
     * 
     * @param PersonalInfoUpdateRequest $request
     *
     * @return mixed
     */
    public function updatePersonalInfo(PersonalInfoUpdateRequest $request)
    {
        $data = $request->only(['contact_email', 'first_name', 'last_name', 'birthday', 'phone_number']);
        $user = Auth::user();
        $user->update($data);

        $img = $request->avatar['src']; // String type : "data:image/png;base64,<valid base64>"

        if(substr_count($img, 'data:image')){
            $base64 = explode(',', $img)[1]; 
            // Get valid base64 from received string
            // [0] => "data:image/png;base64,
            // [1] => <valid base64>
            $image = Image::make(base64_decode($base64));
        }

        if (isset($image) && !is_string($image) && in_array($image->mime(), self::MIMES)) 
            {
            $s3Path = env('AWS_S3_PROJECT_PATH', false) . "/users/" . Auth::user()->id . "/avatar/";

            $img_name = $this->uploadFileBase64($img, $s3Path);


            $path = env('AWS_S3_PATH', false) . $s3Path . $img_name;

            $user = Auth::user();

            if (!empty($user->avatar->id)) {
                $user->avatar()->update(['src' => $path]);

                $file = Files::find($user->avatar->id);
            } else {
                $file = Files::create([
                    'src' => $path,
                    'user_id' => Auth::user()->id
                ]);
                $user->avatar()->associate($file);
                $user->save();
            }

            ImageThumbnailProcess::dispatch($file, $s3Path, 'avatar');
        }

        return $this->_set_success(Auth::user());
    }

    /**
     * Get Account Settings
     *
     * @return mixed
     */
    public function getAccountSettings()
    {
        $organisation = Auth::user()->organisation()->first();

        return $this->_set_success($organisation);
    }

    /**
     * Update Account Settings
     *
     * @param AccountSettingsUpdateRequest $request
     *
     * @return mixed
     */
    public function updateAccountSettings(AccountSettingsUpdateRequest $request)
    {
        $organisation = Auth::user()->organisation()->first();
        $organisation->update($request->validated());

        // Update/Upload cover picture and logo
        $img_cp = $request->cover_picture['src'];
        $img_logo = $request->logo['src'];

        if(substr_count($img_cp, 'data:image')){
            $base64_cp = explode(',', $img_cp)[1]; 
            // Get valid base64 from received string
            // [0] => "data:image/png;base64,
            // [1] => <valid base64>
            $image_cp = Image::make(base64_decode($base64_cp));
        }

        if(substr_count($img_logo, 'data:image')){
            $base64_logo = explode(',', $img_logo)[1]; 
            // Get valid base64 from received string
            // [0] => "data:image/png;base64,
            // [1] => <valid base64>
            $image_logo = Image::make(base64_decode($base64_logo));
        }

        if (isset($image_cp) && !is_string($image_cp) && in_array($image_cp->mime(), self::MIMES)) {
            $s3Path = env('AWS_S3_PROJECT_PATH', false) . "/organisations/" . Auth::user()->last_seen_org_id . "/cover_picture/";

            $img_name = $this->uploadFileBase64($img_cp, $s3Path);

            $path = env('AWS_S3_PATH', false) . $s3Path . $img_name;

            if (!empty($organisation->cover_picture->id)) {
                $organisation->cover_picture()->update(['src' => $path]);

                $file = Files::find($organisation->cover_picture->id);
            } else {
                $file = Files::create([
                    'src' => $path,
                    'user_id' => Auth::user()->id
                ]);
                $organisation->cover_picture()->associate($file);
                $organisation->save();
            }

            ImageThumbnailProcess::dispatch($file, $s3Path, 'cover');
        }

        if (isset($image_logo) && !is_string($image_logo) && in_array($image_logo->mime(), self::MIMES)) {
            $s3Path = env('AWS_S3_PROJECT_PATH', false) . "/organisations/" . Auth::user()->last_seen_org_id . "/logo/";

            $img_name = $this->uploadFileBase64($img_logo, $s3Path);

            $path = env('AWS_S3_PATH', false) . $s3Path . $img_name;

            if (!empty($organisation->logo->id)) {
                $organisation->logo()->update(['src' => $path]);

                $file = Files::find($organisation->logo->id);
            } else {
                $file = Files::create([
                    'src' => $path,
                    'user_id' => Auth::user()->id
                ]);
                $organisation->logo()->associate($file);
                $organisation->save();
            }

            ImageThumbnailProcess::dispatch($file, $s3Path, 'avatar');
        }

        return $this->_set_success(Auth::user()->organisation()->first());
    }

    /**
     * Update Password
     *
     * @param ChangePasswordUpdateRequest $request
     *
     * @return mixed
     */
    public function updatePassword(ChangePasswordUpdateRequest $request)
    {
        $user = Auth::user();

        if (!Hash::check($request->password, $user->getAuthPassword())) {
            return $this->_set_error(['password' => [__('settings.password')]], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->_set_success([]);
    }

    /**
     * Get List Developers Accounts
     *
     * @return mixed
     */
    public function getListDevAcc()
    {
        $data = [];
        $org_id = Auth::user()->last_seen_org_id;
        $list = DevelopersAccountsOrganisations::where(['org_id' => $org_id])->get();

        foreach ($list as $k=>$l) {
            $data[$k]['acc_key'] = $l->developersAccount->acc_key;
            $data[$k]['issued_to'] = $l->developersAccount->issued_to;
            $data[$k]['email'] = $l->developersAccount->email;
            $data[$k]['org_token_issued_at'] = $l->org_token_issued_at;
        }

        return $this->_set_success($data);
    }

    /**
     * Add Developer Access
     *
     * @param AddDeveloperAccessRequest $request
     *
     * @return mixed
     */
    public function addDevAcc(AddDeveloperAccessRequest $request)
    {
        $org_id = Auth::user()->last_seen_org_id;
        $developer = DevelopersAccounts::where(['acc_key' => $request->acc_key])->first();

        if (!$developer) {
            return $this->_set_error(['acc_key' => [__('settings.err_developer')]], 422);
        }
        
        $count = DevelopersAccountsOrganisations::where([
            'org_id' => $org_id,
            'dev_acc_id' => $developer->id
        ])->count();

        if (!$count) {
            DevelopersAccountsOrganisations::create([
                'org_id' => $org_id,
                'dev_acc_id' => $developer->id
            ]);
        }

        return $this->_set_success([]);
    }

    /**
     * Delete Developer Access
     *
     * @param DeleteDeveloperAccessRequest $request
     *
     * @return mixed
     */
    public function deleteDevAcc(DeleteDeveloperAccessRequest $request)
    {
        $org_id = Auth::user()->last_seen_org_id;
        $developer = DevelopersAccounts::where(['acc_key' => $request->acc_key])->first();

        if (!$developer) {
            return $this->_set_error(['acc_key' => [__('settings.err_developer')]], 422);
        }

        $count = DevelopersAccountsOrganisations::where([
            'org_id' => $org_id,
            'dev_acc_id' => $developer->id
        ])->count();

        if ($count) {
            DevelopersAccountsOrganisations::where([
                'org_id' => $org_id,
                'dev_acc_id' => $developer->id
            ])->delete();
        }

        return $this->_set_success([]);
    }

    /**
     * Get Notification Settings
     *
     * @return mixed
     */
    public function getNotificationSettings()
    {

    }

    /**
     * Update Notification Settings
     *
     * @return mixed
     */
    public function updateNotificationSettings()
    {

    }

    /**
     * Get Chat Messages Settings
     *
     * @return mixed
     */
    public function getChatMessagesSettings()
    {

    }

    /**
     * Update Chat Messages Settings
     *
     * @return mixed
     */
    public function updateChatMessagesSettings()
    {

    }
}
