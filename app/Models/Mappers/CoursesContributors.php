<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

/**
 * @property int $id
 * @property int $course_id
 * @property int $user_id
 * @property string $created_at
 * @property string $updated_at
 * @property Course $course
 * @property User $user
 */
class CoursesContributors extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table='courses_contributors';

	/**
	 * @var array
	 */
	protected $fillable = ['course_id', 'user_id'];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['created_at', 'updated_at'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function course() {
		return $this->belongsTo('App\Models\Course');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user() {
		return $this->belongsTo('App\Models\User');
	}
}
