<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email
 * @property string $name
 * @property string $phone_number
 * @property string $state
 * @property string $city
 * @property string $street
 * @property string $zip
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Site[] $sites
 * @property Team[] $team
 * @property Files $logo
 * @property Files $cover_picture
 * @property UsersOrganisations[] $usersOrganisations
 */
class Organisation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organisations';

    /**
     * @var array
     */
    protected $fillable = [
        'email',
        'name',
        'phone_number',
        'state',
        'city',
        'street',
        'zip',
        'logo_id',
        'cover_picture_id'
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sites()
    {
        return $this->hasMany('App\Models\Site');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function teams()
    {
        return $this->hasMany('App\Models\Team');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersOrganisations()
    {
        return $this->hasMany('App\Models\UsersOrganisations');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function developersAccounts()
    {
        return $this->hasMany('App\Models\DevelopersAccounts', 'org_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function logo()
    {
        return $this->belongsTo('App\Models\Files', 'logo_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cover_picture()
    {
        return $this->belongsTo('App\Models\Files', 'cover_picture_id');
    }
}
