<?php

namespace App\Http\Requests\Cms\Users;

use App\Http\Requests\Cms\BaseRequest;

class UserUpdateRequest extends BaseRequest {
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			'first_name' 	=> 'required|string|min:3|max:255',
			'last_name'  	=> 'required|string|min:3|max:255',
			'contact_email'	=> 'required|string|email|max:255',
			'phone_number'	=> 'nullable',
            'trial_ends_at' => 'required|string',
			'birthday'		=> 'nullable|date_format:Y-m-d',
			'nickname'		=> 'present|nullable|max:255',
			'avatar' 		=> 'nullable|file|mimes:jpeg,jpg,png|dimensions:min_width=150,min_height=150'
		];
	}
}
