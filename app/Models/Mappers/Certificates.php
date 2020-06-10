<?php

namespace App\Models\Mappers;

use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $course_id
 * @property int $organisation_id
 * @property string $cert_name
 * @property string $issued_user_name
 * @property string $issued_course_name
 * @property string $issued_course_author_name
 * @property string $issued_course_count_lessons
 * @property string $issued_org_name
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 * @property Course $course
 */
class Certificates extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table='certificates';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'org_id',
        'cert_name',
        'issued_user_name',
        'issued_course_name',
        'issued_course_author_name',
        'issued_course_count_lessons',
        'issued_org_name',
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo('App\Models\Course', 'course_id');
    }
}
