<?php

namespace App\Models;

use App\Models\Traits\Trunketable;
use App\Models\Mappers\DevelopersAccounts as BaseDevelopersAccounts;

class DevelopersAccounts extends BaseDevelopersAccounts
{
    use Trunketable;

  //  protected $visible = ['acc_key', 'issued_to', 'email', 'dev_token_issued_at'];

}
