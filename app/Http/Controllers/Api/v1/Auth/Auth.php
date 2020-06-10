<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\API\v1\Users\UserGetRequest;
use App\Http\Traits\AuthTrait;

class Auth extends Controller
{
    use AuthTrait;

    /**
     * Login
     *
     * @param  UserGetRequest $request
     *
     * @return mixed
     */
    public function login(UserGetRequest $request)
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $response = $this->loginByCredentials($credentials);
        
        if (!$response['success']) {
            return $this->_set_error($response['data'], $response['code']);
        }

        return $this->_set_success($response['data']);
    }

    /**
     * Logout
     *
     * @param  Request $request
     *
     * @return mixed
     */
    public function logout(Request $request)
    {
        $this->validate($request, ['token' => 'required']);

        try {
            JWTAuth::invalidate($request->input('token'));
            return response()->json(['success' => true]);

        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['success' => false, 'error' => [__('auth.login_logout_error')]], 500);
        }
    }

    /**
     * Refresh
     *
     *
     * @return void
     */
    public function refresh()
    {
        die('refresh');
    }
}