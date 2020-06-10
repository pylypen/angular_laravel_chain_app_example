<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $site_id
 * @property int $team_id
 * @property boolean $assigned_by_team
 * @property string $created_at
 * @property string $updated_at
 * @property Course $course
 * @property Team $team
 * @property User $user
 */
class UsersSite extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_sites';

	/**
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'site_id',
		'is_admin',
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
	public function course()
	{
		return $this->belongsTo('App\Models\Course', 'site_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function site()
	{
		return $this->belongsTo('App\Models\Site');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}
}
