<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLessonCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lesson_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('lesson_id');
            $table->unsignedInteger('user_id');
            $table->text('comment');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('lesson_comments', function (Blueprint $table) {
            $table->foreign('lesson_id')->references('id')->on('lessons')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lesson_comments', function (Blueprint $table) {
            $table->dropForeign('lesson_comments_lesson_id_foreign');
            $table->dropForeign('lesson_comments_user_id_foreign');
        });
        
        Schema::dropIfExists('lesson_comments');
    }
}
