<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\API\v1\Users\ForgotPasswordRequest;
use App\Http\Requests\API\v1\Users\GetQuestionByEmailRequest;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  ForgotPasswordRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        $response = $this->sendResetLink($request->only('email'));

        return $this->_set_success(['email' => $response]);
    }

    /**
     * Get User Question by Email.
     *
     * @param  GetQuestionByEmailRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function getQuestionByEmail(GetQuestionByEmailRequest $request)
    {

        $user = User::where('email', $request->email)->first();
        $question = null;

        if ($user) {
            $secretAnswer = $user->usersSecretAnswers()->first();

            if ($secretAnswer) {
                $question = $secretAnswer->secretQuestion()->first();
            } else {
                $this->sendResetLink($request->only('email'));
            }

            return $this->_set_success($question);
        }


        return $this->_set_error(['email' => [__('auth.forgot_not_exist')]]);
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  string $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse($response)
    {
        return $this->_set_success(['status' => [trans($response)]]);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  string $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse($response)
    {
        return $this->_set_error(['email' => [trans($response)]]);
    }

    /**
     * Check user Secret Question
     *
     * @param  ForgotPasswordRequest $request
     *
     * @return boolean
     */
    protected function checkSecretQuestion(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        $answer = $user->usersSecretAnswers()->first();
        return password_verify($request->answer, $answer->secret_answer);
    }

    /**
     * Check user Secret Question
     *
     * @param  array $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLink(array $request)
    {
        $response = $this->broker()->sendResetLink($request);

        $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($response)
            : $this->sendResetLinkFailedResponse($response);

        return $response;
    }
}
