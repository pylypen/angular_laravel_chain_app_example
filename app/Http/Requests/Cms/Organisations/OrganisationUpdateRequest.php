<?php

namespace App\Http\Requests\Cms\Organisations;

use App\Http\Requests\Cms\BaseRequest;

class OrganisationUpdateRequest extends BaseRequest {
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			'email' => 'required|email',
			'name' => 'required|string|max:255',
			'phone_number' => 'nullable',
			'state' => 'nullable|string|max:255',
			'city' => 'nullable|string|max:255',
			'street' => 'nullable|string|max:255',
			'zip' => 'nullable|string|max:255',
			'_logo' => 'nullable|file|mimes:jpeg,jpg,png|dimensions:min_width=150,min_height=150',
			'_cover_picture' => 'nullable|file|mimes:jpeg,jpg,png|dimensions:min_width=1300'
		];
	}
}
