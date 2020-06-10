<?php


namespace App\Models;

use  App\Models\Mappers\UsersOrganisations as BaseUsersOrganisations;
use App\Models\Traits\Trunketable;

class UsersOrganisations extends BaseUsersOrganisations
{
    use Trunketable;

    public $hidden = [
        'created_at',
        'updated_at',
        'organisation_id'
    ];

    public $with = [
        'organisation'
    ];
}