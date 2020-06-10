<?php

namespace App\Http\Controllers\Api\v1\Auth;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\Users\ConfirmEmailRequest;
use App\Models\User;
use App\Http\Controllers\Traits\StrManipulationTrait;
use App\Http\Traits\AuthTrait;

class ConfirmEmailController extends Controller
{
    use StrManipulationTrait;
    use AuthTrait;
    
    /**
     * Update confirm code for user
     *
     * @param  ConfirmEmailRequest $request
     *
     * @return mixed
     */
    public function update(ConfirmEmailRequest $request)
    {

        $user = User::where(['confirm_code' => $request->confirm_code])->first();

        if ($user) {
            $user->confirm_code = null;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->nickname = md5(time() . rand(0, 999999999));
            $user->password = Hash::make($request->password);
            $user->save();

            /* Generate Nickname */
            $user->nickname = strtolower($user->first_name) . strtolower($user->last_name) . ".{$user->id}";
            $user->save();

            $credentials = [
                'email' => $user->email,
                'password' => $request->password
            ];

            $response = $this->loginByCredentials($credentials);

            if (!$response['success']) {
                return $this->_set_error($response['data'], $response['code']);
            }

            return $this->_set_success($response['data']);
        }

        return $this->_set_error(['email' => [__('auth.confirm_code_error')]], 422);
    }

    /**
     * Update confirm code for user
     *
     * @param  string $subdomain
     * @param  string $code
     *
     * @return mixed
     */
    public function getData(string $subdomain, string $code)
    {

        $user = User::where(['confirm_code' => $code])->first();

        if ($user) {

            return $this->_set_success([
                'confirm_code' => $code,
                'email' => $this->hideEmail($user->email),
                'first_name' => $user->first_name,
                'last_name' => $user->last_name
            ]);
        }

        return $this->_set_error(['email' => [__('auth.confirm_code_error')]], 422);
    }
}