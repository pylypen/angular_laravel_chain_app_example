<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_organisations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('organisation_id');
            $table->boolean('is_admin')->default(0);
            $table->boolean('is_owner')->default(0);
            $table->timestamps();
        });

        Schema::table('users_organisations', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('users_organisations', function (Blueprint $table) {
            $table->dropForeign('users_organisations_user_id_foreign');
            $table->dropForeign('users_organisations_organisation_id_foreign');
        });

        Schema::dropIfExists('users_organisations');
    }
}
