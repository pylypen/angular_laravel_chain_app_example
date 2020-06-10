<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use App\Http\Traits\ManageableTrait;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $author_id
 * @property string $name
 * @property string $subtitle
 * @property string $description
 * @property boolean $is_free
 * @property float $price
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property App/User $user
 * @property Lesson[] $lessons
 * @property UsersCourse[] $usersCourses
 * @property UsersSite[] $usersSites
 * @property Files $thumbnail
 * @property Files $featured_background
 */
class Course extends Model
{
    use ManageableTrait;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'courses';

    /**
     * @var array
     */
    protected $fillable = [
        'author_id',
        'original_author_id',
        'featured_background_id',
        'thumbnail_id',
        'name',
        'subtitle',
        'description'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo('App\Models\User', 'author_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function originalAuthor()
    {
        return $this->belongsTo('App\Models\User', 'original_author_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lessons()
    {
        return $this->hasMany('App\Models\Lesson');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function progress()
    {
        return $this->hasMany('App\Models\UsersCoursesProgress');
    }

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
    public function usersSites()
    {
        return $this->hasMany('App\Models\UsersSite', 'site_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function coursesContributors()
    {
        return $this->hasMany('App\Models\CoursesContributors');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thumbnail()
    {
        return $this->belongsTo('App\Models\Files', 'thumbnail_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function featured_background()
    {
        return $this->belongsTo('App\Models\Files', 'featured_background_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function users()
    {
        return $this->hasManyThrough(
            'App\Models\User',
            'App\Models\UsersCourse',
            'course_id',
            'id',
            'id',
            'user_id'
        );
    }

    /**
     * Get Assigned Users
     */
    public function getAssignedUsersIdsAttribute()
    {
        $teams = $this->getManageableTeams();
        
        return DB::select("
                        SELECT
                          DISTINCT u.id AS user_id
                        FROM courses c
                          JOIN marketplace m ON c.id = m.course_id AND m.marketplace_status_id = 2
                          JOIN users_courses uc ON m.id = uc.marketplace_id
                          JOIN users u ON u.id = uc.user_id AND u.deleted_at IS NULL
                        WHERE c.id = " . $this->id . " AND c.deleted_at IS NULL AND m.team_id IN (" . join(',', $teams) . ")
                   ");
    }
}
