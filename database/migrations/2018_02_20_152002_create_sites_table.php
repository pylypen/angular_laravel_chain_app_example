<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('organisation_id')->nullable()->dafault(null);
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('sites', function (Blueprint $table) {
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

        Schema::table('sites', function (Blueprint $table) {
            $table->dropForeign('sites_organisation_id_foreign');
        });

        Schema::dropIfExists('sites');
    }
}
