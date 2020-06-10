<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $lesson_id
 * @property int $file_id
 * @property int $media_extension_id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property Lesson $lesson
 * @property Files $file
 * @property MediaExtensions $media_extension
 */
class Media extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media';

	/**
	 * @var array
	 */
	protected $fillable = ['lesson_id', 'file_id', 'media_extension_id', 'name'];
	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['created_at', 'updated_at'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function lesson()
	{
		return $this->belongsTo('App\Models\Lesson');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function file()
	{
		return $this->belongsTo('App\Models\Files');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function media_extension()
	{
		return $this->belongsTo('App\Models\MediaExtensions');
	}

}
