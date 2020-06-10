<?php

namespace App\Http\Controllers\API\v1\Settings;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\API\v1\Users\MessageCreateRequest;
use App\Http\Controllers\Controller;
use App\Models\Chats;
use App\Models\UsersChats;
use App\Models\UsersChatsMessages;
use App\Models\Messages;
use App\Models\User;

class ChatController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $usersChats = Auth::user()->userChats()->get()->toArray();
        $chat_ids = [];

        foreach ($usersChats as $chat) {
            @$chat_ids[] = $chat['chat_id'];
        }

        $usersChats = UsersChats::whereIn('chat_id', $usersChats)
            ->whereNotIn('user_id', [Auth::user()->id])
            ->with(['user'])->get();


        return $this->_set_success($usersChats);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  MessageCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store( MessageCreateRequest $request )
    {
        $is_new = false;
        if (empty($request->chat_id)) {
            $chat = UsersChatsMessages::select('chat_id')
                ->where('receiver_id' , $request->user_id)
                ->orWhere('receiver_id' , Auth::user()->id)
                ->groupBy('chat_id')->first();

            if (empty($chat)) {
                $is_new = true;
                $chat = Chats::create([])->id;

                UsersChats::create([
                    'user_id' => $request->user_id,
                    'chat_id' => $chat
                ]);

                UsersChats::create([
                    'user_id' => Auth::user()->id,
                    'chat_id' => $chat
                ]);
            } else {
                $chat = $chat->chat_id;
            }
        }

        $chat = (empty($chat)) ? $request->chat_id : $chat;

        if (!UsersChats::where(['user_id' => Auth::user()->id, 'chat_id' => $chat])->count()) {
            return $this->_set_error(['messages' => [__('chats.show_error')]]);
        };

        $nickname_receiver = User::find($request->user_id)->nickname;

        $force_view = substr_count($request->message, "@$nickname_receiver");

        $UserChat = UsersChats::where([
            'user_id' => $request->user_id,
            'chat_id' => $chat
        ])->first();

        $messages = Messages::create([
            'sender_id' => Auth::user()->id,
            'message' => $request->message
        ]);

        UsersChatsMessages::create([
            'receiver_id' => $request->user_id,
            'chat_id' => $chat,
            'message_id' => $messages->id,
            'viewed' => ($force_view) ? true : $UserChat->status,
        ]);

        UsersChatsMessages::create([
            'receiver_id' => Auth::user()->id,
            'chat_id' => $chat,
            'message_id' => $messages->id,
            'viewed' => 1,
        ]);

        return $this->_set_success([
            'is_new' => $is_new,
            'id' => $chat,
            'conversation' => null
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  string $subdomain
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($subdomain, $id)
    {
        $chat = UsersChatsMessages::where([
            'chat_id' => (int)$id,
            'receiver_id' => Auth::user()->id
        ])->first();

        if (!empty($chat)) {
            return $this->_set_success([
                'is_new' => false,
                'id' => null,
                'conversation' => UsersChatsMessages::where([
                    'chat_id' => $chat->chat_id,
                    'receiver_id' => Auth::user()->id
                ])->with(['message', 'user'])->get()
            ]);
        }

        return $this->_set_error(['messages' => [__('chats.show_error')]]);
    }
}
