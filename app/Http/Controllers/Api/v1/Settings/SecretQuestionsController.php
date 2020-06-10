<?php

namespace App\Http\Controllers\API\v1\Settings;

use App\Http\Requests\API\v1\Users\SecretQuestionsCreateRequest;
use App\Http\Requests\API\v1\Users\SecretQuestionsUpdateRequest;
use App\Http\Controllers\Controller;
use App\Models\SecretQuestions;
use App\Models\UsersSecretAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SecretQuestionsController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return $this->_set_success( SecretQuestions::get() );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  SecretQuestionsCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store( SecretQuestionsCreateRequest $request )
    {
        if (empty(Auth::user()->secretAnswer->secretQuestion->question)) {
            $data = $request->all();
            $data['secret_answer'] = Hash::make($data['secret_answer']);
            Auth::user()->secretAnswer()->save(
                new UsersSecretAnswer($data)
            );

            return $this->_set_success(['secret_answer' => [__('secret_questions.store')]]);
        }

        return $this->_set_error(['secret_answer' => [__('secret_questions.store_error')]]);
    }

    /**
     * Display the specified resource.
     *
     * @param  string $subdomain
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show( $subdomain, $id ) {
        $data = SecretQuestions::find( (int)$id );
        if ( $data ) {
            return $this->_set_success( $data );
        } else {
            return $this->_set_error( [ 'secret_questions' => [__('secret_questions.show_error', ['id' => $id])] ] );
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  SecretQuestionsUpdateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update( SecretQuestionsUpdateRequest $request ) {
        if (!empty(Auth::user()->secretAnswer->secretQuestion->question)) {
            $data = $request->all();
            $data['secret_answer'] = Hash::make($data['secret_answer']);

            UsersSecretAnswer::where('id', Auth::user()->secretAnswer->id)->update($data);

            return $this->_set_success(['secret_answer' => [__('secret_questions.update')]]);
        }

        return $this->_set_error(['secret_answer' => [__('secret_questions.update_error')]]);
    }
}
