<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int sender_id
 * @property string message
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Chats $chat
 * @property UsersChatsMessages $userChatsMessages
 */
class Messages extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * @var array
     */
    protected $fillable = [
        'sender_id',
        'message'
    ];

    /**
     * @var array
     */
    protected $visible = [
        'sender_id',
        'message',
        'created_at',
        'chat',
        'userChatsMessages'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function chat()
    {
        return $this->hasone('App\Models\Chats');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function userChatsMessages()
    {
        return $this->hasone('App\Models\UserChatsMessages');
    }
}
