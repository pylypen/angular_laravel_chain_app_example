<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property UsersCourse[] $usersCourses
 * @property UsersTeam[] $usersTeams
 * @property Team $team
 * @property User $users
 * @property Files $logo
 */
class Team extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'teams';

    /**
     * @var array
     */
    protected $fillable = ['name', 'description', 'users', 'logo'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersCourses()
    {
        return $this->hasMany('App\Models\UsersCourse');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersTeams()
    {
        return $this->hasMany('App\Models\UsersTeam');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function marketplace()
    {
        return $this->hasMany('App\Models\Marketplace');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'users_teams', 'team_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function organisation()
    {
        return $this->belongsTo('App\Models\Organisation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function logo()
    {
        return $this->belongsTo('App\Models\Files', 'logo_id');
    }
}
