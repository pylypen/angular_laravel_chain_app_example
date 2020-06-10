<?php

namespace App\Models;

use App\Models\Mappers\Site as BaseSite;
use App\Models\Traits\Trunketable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends BaseSite
{
    use Trunketable, SoftDeletes;

    public $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'logo_id',
    ];

    public $with = [
        'logo',
    ];

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'users_sites', 'site_id', 'user_id');
    }

}