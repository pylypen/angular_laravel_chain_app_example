<?php

namespace App\Models;

use App\Models\Mappers\Organisation as BaseOrganisation;
use App\Models\Traits\Trunketable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends BaseOrganisation
{
    use Trunketable, SoftDeletes;

    public $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'logo_id',
        'cover_picture_id'
    ];

    public $with = [
        'logo',
        'cover_picture'
    ];


}