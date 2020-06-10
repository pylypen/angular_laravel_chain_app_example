<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $secret_questions_id
 * @property string $secret_answer
 * @property string $created_at
 * @property string $updated_at
 * @property SecretQuestions secretQuestion
 */
class UsersSecretAnswer extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_secret_answers';

    /**
     * @var array
     */
    protected $fillable = ['secret_questions_id', 'secret_answer', 'secretQuestion'];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function secretQuestion()
    {
        return $this->hasOne('App\Models\SecretQuestions', 'id', 'secret_questions_id');
    }
}
