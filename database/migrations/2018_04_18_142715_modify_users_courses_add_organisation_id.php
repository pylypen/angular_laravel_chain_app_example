<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUsersCoursesAddOrganisationId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_courses', function (Blueprint $table) {
            $table->unsignedInteger('organisation_id')->after('id')->nullable()->default(null);
        });
        
        Schema::table('users_courses', function (Blueprint $table) {
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');
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
            $table->dropForeign('users_courses_organisation_id_foreign');
        });

        Schema::table('users_courses', function (Blueprint $table) {
            $table->dropColumn('organisation_id');
        });
    }
}
