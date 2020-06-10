<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $question
 * @property boolean $actual
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class SecretQuestions extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'secret_questions';

    /**
     * @var array
     */
    protected $fillable = ['question', 'actual'];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
