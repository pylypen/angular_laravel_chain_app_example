<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $organisation_id
 * @property int $site_id
 * @property boolean $founder
 * @property string $created_at
 * @property string $updated_at
 * @property Organisation $organisation
 * @property Site $site
 * @property User $user
 */
class UsersOrganisations extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_organisations';

	/**
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'organisation_id',
		'is_admin',
		'is_owner',
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
	public function organisation()
	{
		return $this->belongsTo('App\Models\Organisation');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}
}
