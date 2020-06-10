<?php

namespace App\Models;

use App\Models\Mappers\SecretQuestions as BaseSecretQuestions;
use App\Models\Traits\Trunketable;
use Illuminate\Database\Eloquent\SoftDeletes;

class SecretQuestions extends BaseSecretQuestions
{
    use Trunketable, SoftDeletes;

    public $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $timestamps = ['created_at', 'updated_at', 'deleted_at'];
}