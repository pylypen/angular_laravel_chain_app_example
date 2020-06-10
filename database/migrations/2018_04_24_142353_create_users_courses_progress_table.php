<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersCoursesProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_courses_progress', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('bind_id');
            $table->unsignedInteger('lesson_id');
            $table->unsignedInteger('progress_status_id');
            $table->timestamps();
        });

        Schema::table('users_courses_progress', function (Blueprint $table) {
            $table->foreign('bind_id')->references('id')->on('users_courses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('lesson_id')->references('id')->on('lessons')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('progress_status_id')->references('id')->on('lessons_progress_status')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_courses_progress', function (Blueprint $table) {
            $table->dropForeign('users_courses_progress_bind_id_foreign');
            $table->dropForeign('users_courses_progress_lesson_id_foreign');
            $table->dropForeign('users_courses_progress_progress_status_id_foreign');
        });

        Schema::dropIfExists('users_courses_progress');
    }
}
