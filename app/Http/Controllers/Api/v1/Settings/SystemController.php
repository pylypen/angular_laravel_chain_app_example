<?php

namespace App\Http\Controllers\Api\v1\Settings;

use App\Http\Controllers\Controller;
use App\Models\SecretQuestions;

class SystemController extends Controller
{
    public function secret_questions()
    {
        return $this->_set_success(SecretQuestions::where(['actual' => true])->get());
    }

}
