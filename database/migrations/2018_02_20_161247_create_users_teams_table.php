<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_teams', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('team_id');
            $table->boolean('is_admin')->default(0);
            $table->boolean('is_owner')->default(0);
            $table->timestamps();
        });

        Schema::table('users_teams', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('users_teams', function (Blueprint $table) {
            $table->dropForeign('users_teams_user_id_foreign');
            $table->dropForeign('users_teams_team_id_foreign');
        });

        Schema::dropIfExists('users_teams');
    }
}
