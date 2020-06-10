<?php

namespace App\Http\Requests\Cms\Users;

use App\Http\Requests\Cms\BaseRequest;

class InternalRequest extends BaseRequest {

	public function rules() {
		return [
			'email'   => ['required', 'string', 'email', 'max:255'],
			'password'    => 'required',
			'is_internal' => 'not_in:0'
		];
	}
}