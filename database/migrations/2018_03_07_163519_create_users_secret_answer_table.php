<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersSecretAnswerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_secret_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('secret_questions_id');
            $table->string('secret_answer');
            $table->timestamps();
        });

        Schema::table('users_secret_answers', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('secret_questions_id')->references('id')->on('secret_questions')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_secret_answers', function (Blueprint $table) {
            $table->dropForeign('users_secret_answers_user_id_foreign');
        });
        
        Schema::table('users_secret_answers', function (Blueprint $table) {
            $table->dropForeign('users_secret_answers_secret_questions_id_foreign');
        });
        
        Schema::dropIfExists('users_secret_answers');
    }
}
