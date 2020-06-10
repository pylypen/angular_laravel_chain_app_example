<?php

namespace App\Models;

use App\Models\Mappers\Certificates as BaseCertificate;
use App\Models\Traits\Trunketable;

class Certificates extends BaseCertificate
{
    use Trunketable;

    public $hidden = [
        'created_at'
    ];
}