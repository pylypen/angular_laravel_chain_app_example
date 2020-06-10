<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifySitesAddLogoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->unsignedInteger('logo_id')->nullable()->after('organisation_id')->default(null);
        });

        Schema::table('sites', function (Blueprint $table) {
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
        Schema::table('sites', function (Blueprint $table) {
            $table->dropForeign('sites_logo_id_foreign');
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('logo_id');
        });
    }
}
