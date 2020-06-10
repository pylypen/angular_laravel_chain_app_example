<?php

namespace App\Http\Requests\Cms\Users;

use Illuminate\Validation\Rule;
use App\Http\Requests\Cms\BaseRequest;

class UserAdminCreateRequest extends BaseRequest {
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
			'first_name' 	=> 'required|string|min:3|max:255',
			'last_name'  	=> 'required|string|min:3|max:255',
			'contact_email'	=> 'required|string|email|max:255',
			'password' 		=> 'required|string|min:6|max:255|confirmed'
		];
	}
}
