<?php

namespace App\Models;

use App\Models\Mappers\DevelopersAccountsOrganisations as BaseDevAccOrgs;
use App\Models\Traits\Trunketable;


class DevelopersAccountsOrganisations extends BaseDevAccOrgs
{
    use Trunketable;

    protected $visible = ['id', 'dev_acc_id', 'org_id'];

}