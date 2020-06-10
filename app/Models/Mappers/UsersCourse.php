<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $organisation_id
 * @property int $user_id
 * @property int $course_id
 * @property int $assigned_by_id
 * @property int $team_id
 * @property boolean $assigned_by_team
 * @property boolean $obligatory
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 * @property Course $course
 * @property Team $team
 */
class UsersCourse extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_courses';

	/**
	 * @var array
	 */
	protected $fillable = [
		'organisation_id',
		'user_id',
		'course_id',
		'assigned_by_id',
		'team_id',
		'site_id',
		'team',
		'course',
		'user',
		'marketplace_id',
		'is_obligatory',
		'is_team_assigned'
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['created_at', 'updated_at'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user_assigned_by()
	{
		return $this->belongsTo('App\Models\User', 'assigned_by_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function course()
	{
		return $this->belongsTo('App\Models\Course');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function team()
	{
		return $this->belongsTo('App\Models\Team');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function organisation()
	{
		return $this->belongsTo('App\Models\Organisations');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function marketplace()
	{
		return $this->belongsTo('App\Models\Marketplace');
	}
}
