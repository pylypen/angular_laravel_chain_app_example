<?php

namespace App\Http\Requests\Cms\Organisations;

use App\Http\Requests\Cms\BaseRequest;

class OrganisationCreateRequest extends BaseRequest {
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			'org_email' => 'required|email|unique:organisations,email',
			'org_name' => 'required|string|max:255',
			'user_email' => 'required|email|max:255',
			'user_contact_email' => 'required|email|max:255'
		];
	}
}
