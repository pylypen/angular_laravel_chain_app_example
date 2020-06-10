<?php

namespace App\Http\Controllers\Cms;

use App\Http\Requests\Cms\Users\InternalRequest;
use App\Models\User;
use App\Http\Controllers\Controller;
use JWTAuth;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(InternalRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (User::where([
            ['email', '=', $credentials['email']],
            ['is_internal', '=', '1']
        ])->first()) {

            if (!$token = JWTAuth::attempt($credentials)) {
                return response([
                    'status' => 'error',
                    'error' => 'invalid.credentials',
                    'msg' => 'Invalid Credentials.'
                ], 400);
            }
        } else {
            return response([
                'status' => 'error',
                'error' => 'invalid.access',
                'msg' => 'Invalid Access'
            ], 403);
        }

        return response([
            'status' => 'success'
        ])
            ->header('Authorization', $token);
    }

    public function user()
    {
        $user = User::find(Auth::user()->id);

        return response([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function refresh()
    {
        return response([
            'status' => 'success'
        ]);
    }

    public function check()
    {
        return response([
            'status' => 'success'
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate();

        return response([
            'status' => 'success',
            'msg' => 'Logged out Successfully.'
        ], 200);
    }
}
