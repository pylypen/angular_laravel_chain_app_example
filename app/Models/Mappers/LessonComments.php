<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $lesson_id
 * @property int $user_id
 * @property string $comment
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property User $user
 * @property Lesson $lesson
 */
class LessonComments extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lesson_comments';

    /**
     * @var array
     */
    protected $fillable = [
        'lesson_id',
        'user_id',
        'comment'
    ];

    /**
     * @var array
     */
    protected $visible = [
        'id',
        'lesson_id',
        'user_id',
        'comment',
        'created_at',
        'user'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo( 'App\Models\User' );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lesson()
    {
        return $this->belongsTo( 'App\Models\Lesson' );
    }
}
