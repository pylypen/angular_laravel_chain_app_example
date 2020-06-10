<?php

namespace App\Models\Mappers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string name
 * @property string stripe_id
 * @property string stripe_plan
 * @property int quantity
 * @property string trial_ends_at
 * @property string ends_at
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class Subscriptions extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'stripe_id',
        'stripe_plan',
        'quantity',
        'trial_ends_at',
        'ends_at',
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
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
