<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $organisation_id
 * @property int $site_id
 * @property int $team_id
 * @property int $course_id
 * @property int $marketplace_status_id
 * @property int $reviewed_by
 * @property bool $is_published
 * @property bool $review_completed
 * @property bool $is_wildcard_assignedLEFT JOIN `teams`
                       ON `teams`.`id` = `users_courses`.`team_id` and `teams`.`deleted_at` is NULL
 * @property bool $is_wildcard_obligatory
 * @property string $review_message
 * @property string $created_at
 * @property string $updated_at
 * @property Organisation $organisation
 * @property Site $site
 * @property Team $team
 * @property Course $course
 * @property MarketplaceStatuses $status
 * @property User $reviewedBy
 */
class Marketplace extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'marketplace';

    /**
     * @var array
     */
    protected $fillable = ['organisation_id', 'site_id', 'team_id', 'course_id', 'marketplace_status_id',
        'reviewed_by', 'is_published', 'is_wildcard_assigned', 'is_wildcard_obligatory', 'review_completed',
        'review_message'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersCourse()
    {
        return $this->hasMany('App\Models\UsersCourse', 'marketplace_id', 'id');
    }

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
    public function site()
    {
        return $this->belongsTo('App\Models\Site');
    }

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
    public function course()
    {
        return $this->belongsTo('App\Models\Course');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo('App\Models\MarketplaceStatuses', 'marketplace_status_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reviewedBy()
    {
        return $this->belongsTo('App\Models\User', 'reviewed_by', 'id');
    }
}
