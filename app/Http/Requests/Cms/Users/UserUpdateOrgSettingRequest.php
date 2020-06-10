<?php

namespace App\Http\Requests\Cms\Users;

use App\Http\Requests\Cms\BaseRequest;

class UserUpdateOrgSettingRequest extends BaseRequest {
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			'id_org_user' 	=> 'required|exists:users_organisations,id',
			'org_id'	=> 'required|exists:organisations,id',
			'user_id'  	=> 'required|exists:users,id',
			'type'	=> 'required|in:is_admin,is_owner',
			'set'	=> 'required|boolean'
		];
	}
}
