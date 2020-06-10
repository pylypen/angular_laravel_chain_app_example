<?php

namespace App\Models;

use App\Models\Mappers\Team as BaseTeam;
use App\Models\Traits\Trunketable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends BaseTeam
{
    use Trunketable, SoftDeletes;

    public $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'logo_id',
    ];

    public $with = [
        'logo'
    ];

}