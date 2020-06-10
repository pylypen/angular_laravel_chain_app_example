<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $lesson_id
 * @property int $media_type_id
 * @property int $order
 * @property string $created_at
 * @property string $updated_at
 */
class LessonContentOrder extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lesson_content_order';

    /**
     * @var array
     */
    protected $fillable = ['id', 'lesson_id', 'media_type_id', 'order'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function lesson()
    {
        return $this->hasOne('App\Models\Lesson','id', 'lesson_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function media_type()
    {
        return $this->hasOne('App\Models\MediaTypes','id','media_type_id');
    }
}