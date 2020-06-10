<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_courses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable()->default(null);
            $table->unsignedInteger('course_id')->nullable()->default(null);
            $table->unsignedInteger('assigned_by_id')->nullable()->default(null);
            $table->unsignedInteger('team_id')->nullable()->default(null);
            $table->boolean('assigned_by_team')->default(0);
            $table->boolean('obligatory')->default(0);
            $table->timestamps();
        });

        Schema::table('users_courses', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('assigned_by_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('team_id')->references('id')->on('teams')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('users_courses', function (Blueprint $table) {
            $table->dropForeign('users_courses_user_id_foreign');
            $table->dropForeign('users_courses_course_id_foreign');
            $table->dropForeign('users_courses_assigned_by_id_foreign');
            $table->dropForeign('users_courses_team_id_foreign');
        });

        Schema::dropIfExists('users_courses');
    }
}
