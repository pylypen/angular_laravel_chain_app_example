<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $acc_key
 * @property string $acc_secret
 * @property string $issued_to
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property \App\Models\DevelopersAccountsOrganisations[] $developersAccountsOrganisations
 */
class DevelopersAccounts extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'developers_accounts';

    /**
     * @var array
     */
    protected $fillable = ['acc_key', 'acc_secret', 'issued_to', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function developersAccountsOrganisations()
    {
        return $this->hasMany('App\Models\DevelopersAccountsOrganisations', 'dev_acc_id');
    }
}
