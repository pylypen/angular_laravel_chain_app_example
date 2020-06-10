<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_extensions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('media_type_id');
            $table->string('media_extension');
            $table->string('media_mime');
            $table->timestamps();
        });

        Schema::table('media_extensions', function (Blueprint $table) {
            $table->foreign('media_type_id')->references('id')->on('media_types')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media_extensions', function (Blueprint $table) {
            $table->dropForeign('media_extensions_media_type_id_foreign');
        });

        Schema::dropIfExists('media_extensions');
    }
}
