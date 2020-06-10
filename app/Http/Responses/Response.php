<?php

namespace App\Http\Responses;

class Response
{

    public $success = false;
    public $data = [];
    public $errors = [];

    protected function __construct($success = false, $data = [], $error = [])
    {
        $this->success = $success;
        $this->data = $data;
        $this->errors = $error;

    }

    static function success($data)
    {
        return new self(true, $data);

    }

    static function error($error)
    {
        return new self(false, [], $error);
    }
}