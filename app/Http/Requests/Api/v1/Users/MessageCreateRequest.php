<?php

namespace App\Http\Requests\API\v1\Users;

use App\Http\Requests\API\v1\BaseRequest;

class MessageCreateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' =>    'required|exists:users,id',
            'chat_id' =>    'exists:chats,id',
            'message' =>    'required|string'
        ];
    }
}
