<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTeamsChangeLogoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
        
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedInteger('logo_id')->nullable()->after('site_id')->default(null);
        });
        
        Schema::table('teams', function (Blueprint $table) {
            $table->foreign('logo_id')->references('id')->on('files')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign('teams_logo_id_foreign');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('logo_id');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->string('logo')->after('description')->nullable();
        });

    }
}
