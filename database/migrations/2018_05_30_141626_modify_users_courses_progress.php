<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUsersCoursesProgress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_courses_progress', function (Blueprint $table) {
            $table->dropForeign('users_courses_progress_bind_id_foreign');
            $table->dropColumn('bind_id');
        });

        Schema::table('users_courses_progress', function (Blueprint $table) {
            $table->unsignedInteger('course_id')->after('id')->nullable()->default(null);
            $table->foreign('course_id')->references('id')->on('courses')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('user_id')->after('id')->nullable()->default(null);
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
        Schema::table('users_courses_progress', function (Blueprint $table) {
            $table->dropForeign('users_courses_progress_course_id_foreign');
            $table->dropColumn('course_id');

            $table->dropForeign('users_courses_progress_user_id_foreign');
            $table->dropColumn('user_id');
        });

        Schema::table('users_courses_progress', function (Blueprint $table) {
            $table->unsignedInteger('bind_id')->after('id')->nullable()->default(null);
            $table->foreign('bind_id')->references('id')->on('users_courses')->onUpdate('cascade')->onDelete('cascade');
        });
    }
}
