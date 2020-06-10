<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $organisation_id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Organisation $organisation
 * @property Marketplace[] $marketplaces
 * @property Team[] $teams
 * @property UsersSite[] $usersSites
 * @property Files $logo
 */
class Site extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sites';

    /**
     * @var array
     */
    protected $fillable = ['organisation_id', 'name', 'logo_id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organisation()
    {
        return $this->belongsTo('App\Organisation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function marketplaces()
    {
        return $this->hasMany('App\Marketplace');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teams()
    {
        return $this->hasMany('App\Models\Team');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersSites()
    {
        return $this->hasMany('App\UsersSite');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function logo()
    {
        return $this->belongsTo('App\Models\Files', 'logo_id');
    }
}
