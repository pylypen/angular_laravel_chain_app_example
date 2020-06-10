<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $media_type_id
 * @property string $media_extension
 * @property string $media_mime
 * @property string $created_at
 * @property string $updated_at
 * @property MediaTypes $media_type
 */
class MediaExtensions extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media_extensions';

	/**
	 * @var array
	 */
	protected $fillable = ['media_type_id', 'media_extension', 'media_mime'];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['created_at', 'updated_at'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function media_type()
	{
		return $this->belongsTo('App\Models\MediaTypes');
	}
}
