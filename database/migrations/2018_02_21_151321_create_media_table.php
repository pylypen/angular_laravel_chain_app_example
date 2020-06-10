<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('lesson_id')->nullable()->default(null);
            $table->string('type')->nullable()->default(null);
            $table->string('name')->nullable()->default(null);
            $table->string('display_name')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('media', function (Blueprint $table) {
            $table->foreign('lesson_id')->references('id')->on('lessons')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign('media_lesson_id_foreign');
        });

        Schema::dropIfExists('media');
    }
}
