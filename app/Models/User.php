<?php

namespace App\Models;

use App\Models\Mappers\User as BaseUser;
use App\Models\Traits\Trunketable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Mail\ForgotPassword;
use Illuminate\Support\Facades\Mail;

class User extends BaseUser
{
    use Trunketable, SoftDeletes;

    public $fillable = [
        'id',
        'last_seen_org_id',
        'first_name',
        'last_name',
        'email',
        'contact_email',
        'phone_number',
        'birthday',
        'password',
        'confirm_code',
        'avatar',
        'nickname',
        'login_at',
        'trial_ends_at'
    ];

    public $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'password',
        'avatar_id'
    ];


    public $with = [
        'avatar'
    ];

    public function sendPasswordResetNotification($token)
    {
        Mail::to($this->email)
            ->queue(new ForgotPassword($token));
    }
}

