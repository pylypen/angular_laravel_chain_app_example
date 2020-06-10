<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $dev_acc_id
 * @property int $org_id
 * @property string $created_at
 * @property string $updated_at
 * @property \App\Models\DevelopersAccounts $developersAccount
 * @property \App\Models\Organisation $organisation
 */
class DevelopersAccountsOrganisations extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'developers_accounts_organisations';

    /**
     * @var array
     */
    protected $fillable = ['dev_acc_id', 'org_id', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function developersAccount()
    {
        return $this->belongsTo('App\Models\DevelopersAccounts', 'dev_acc_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organisation()
    {
        return $this->belongsTo('App\Models\Organisation', 'org_id');
    }
}
