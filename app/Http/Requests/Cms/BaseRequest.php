<?php

namespace App\Http\Requests\Cms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		return true;
	}

	/**
	 * Return Validations Error
	 *
	 * @param \Illuminate\Contracts\Validation\Validator $validator
	 *
	 * @return \Illuminate\Http\Exceptions\HttpResponseException
	 */
	protected function failedValidation( Validator $validator ) {
		throw new HttpResponseException( response()->json( $validator->errors(), 422 ) );
	}
}