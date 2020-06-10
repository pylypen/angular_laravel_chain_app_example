<?php

namespace App\Models\Mappers;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Cashier\Billable;

/**
 * @property int $id
 * @property int $last_seen_org_id
 * @property string $email
 * @property string $contact_email
 * @property string $first_name
 * @property string $last_name
 * @property string $phone_number
 * @property string $birthday
 * @property string $password
 * @property string $confirm_code
 * @property string $avatar
 * @property boolean $is_internal
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Organisation $organisation
 * @property Course[] $courses
 * @property CoursesContributors[] $coursesContributors
 * @property LessonComments[] $lessonComments
 * @property Marketplace[] $marketplaces
 * @property Messages[] $messages
 * @property UsersChats[] $usersChats
 * @property UsersChatsMessages[] $usersChatsMessages
 * @property User[] $usersCoursesAssigned
 * @property UsersCourse[] $usersCourses
 * @property UsersOrganisations[] $usersOrganisations
 * @property UsersSecretAnswer[] $usersSecretAnswers
 * @property UsersSite[] $usersSites
 * @property UsersTeam[] $usersTeams
 */
class User extends Authenticatable
{
    use Billable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * @var array
     */
    protected $fillable = [
        'last_seen_org_id',
        'email',
        'contact_email',
        'first_name',
        'last_name',
        'phone_number',
        'birthday',
        'password',
        'confirm_code',
        'is_internal',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public $timestamps = ['login_at', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organisation()
    {
        return $this->belongsTo('App\Models\Organisation', 'last_seen_org_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function avatar()
    {
        return $this->belongsTo('App\Models\Files', 'avatar_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courses()
    {
        return $this->hasMany('App\Models\Course', 'author_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function coursesContributors()
    {
        return $this->hasMany('App\Models\CoursesContributor');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lessonComments()
    {
        return $this->hasMany('App\Models\LessonComment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function marketplaces()
    {
        return $this->hasMany('App\Models\Marketplace', 'reviewed_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany('App\Models\Message', 'sender_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersChats()
    {
        return $this->hasMany('App\Models\UsersChat');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersChatsMessages()
    {
        return $this->hasMany('App\Models\UsersChatsMessage', 'receiver_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersCoursesAssigned()
    {
        return $this->hasMany('App\Models\UsersCourse', 'assigned_by_id');
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
    public function usersOrganisations()
    {
        return $this->hasMany('App\Models\UsersOrganisations');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersSecretAnswers()
    {
        return $this->hasMany('App\Models\UsersSecretAnswer');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersSites()
    {
        return $this->hasMany('App\Models\UsersSite');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersTeams()
    {
        return $this->hasMany('App\Models\UsersTeam');
    }

}
