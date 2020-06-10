<?php

namespace App\Http\Requests\Cms\Users;

use Illuminate\Validation\Rule;
use App\Http\Requests\Cms\BaseRequest;

class UserCreateRequest extends BaseRequest {
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			'email'   => ['required', 'string', 'email', 'max:255', Rule::unique('users')->where(function ($query) {
				return $query->where(['deleted_at' => NULL]);
			})],
			'contact_email'	=> 'required|email',
			'organisation'	=> 'required|exists:organisations,id'
		];
	}
}
