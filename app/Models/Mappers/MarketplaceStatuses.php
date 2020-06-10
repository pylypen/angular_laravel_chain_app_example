<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class MarketplaceStatuses extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'marketplace_statuses';

    /**
     * @var array
     */
    protected $fillable = ['status'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
