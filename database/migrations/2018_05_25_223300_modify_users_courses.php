<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUsersCourses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_courses', function (Blueprint $table) {
            $table->dropForeign('users_courses_team_id_foreign');
            $table->dropColumn('team_id');

            $table->dropColumn('assigned_by_team');
            $table->dropColumn('obligatory');
        });

        Schema::table('users_courses', function (Blueprint $table) {
            $table->boolean('is_team_assigned')->after('assigned_by_id')->default(0);
            $table->boolean('is_obligatory')->after('is_team_assigned')->default(0);

            $table->unsignedInteger('site_id')->after('organisation_id')->nullable()->default(null);
            $table->foreign('site_id')->references('id')->on('sites')->onUpdate('cascade')->onDelete('set null');

            $table->unsignedInteger('team_id')->after('site_id')->nullable()->default(null);
            $table->foreign('team_id')->references('id')->on('teams')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('marketplace_id')->after('course_id')->nullable()->default(null);
            $table->foreign('marketplace_id')->references('id')->on('marketplace')->onUpdate('cascade')->onDelete('cascade');
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
            $table->dropForeign('users_courses_site_id_foreign');
            $table->dropForeign('users_courses_team_id_foreign');
            $table->dropForeign('users_courses_marketplace_id_foreign');

            $table->dropColumn('site_id');
            $table->dropColumn('team_id');
            $table->dropColumn('marketplace_id');

            $table->dropColumn('is_team_assigned');
            $table->dropColumn('is_obligatory');
        });


        Schema::table('users_courses', function (Blueprint $table) {
            $table->unsignedInteger('team_id')->after('assigned_by_id')->nullable()->default(null);
            $table->foreign('team_id')->references('id')->on('teams')->onUpdate('cascade')->onDelete('cascade');

            $table->boolean('assigned_by_team')->after('team_id')->default(0);
            $table->boolean('obligatory')->after('assigned_by_team')->default(0);
        });
    }
}
