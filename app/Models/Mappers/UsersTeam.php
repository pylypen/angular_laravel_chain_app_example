<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $team_id
 * @property boolean $creator
 * @property boolean $is_lead
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 * @property Team team
 */
class UsersTeam extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_teams';

	/**
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'team_id',
		'is_admin',
		'is_owner',
		'team'
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
}
