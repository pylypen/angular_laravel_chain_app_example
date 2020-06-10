<?php

namespace App\Models;

use App\Models\Mappers\Messages as BaseMessages;
use App\Models\Traits\Trunketable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Messages extends BaseMessages
{
    use Trunketable, SoftDeletes;
}