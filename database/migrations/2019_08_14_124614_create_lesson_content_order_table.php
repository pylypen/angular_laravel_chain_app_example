<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLessonContentOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lesson_content_order', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('lesson_id')->nullable()->default(null);
            $table->unsignedInteger('media_type_id')->nullable()->default(null);
            $table->unsignedInteger('order');
            $table->timestamps();
        });

        Schema::table('lesson_content_order', function (Blueprint $table) {
            $table->foreign('lesson_id')->references('id')->on('lessons')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('lesson_content_order', function (Blueprint $table) {
            $table->dropForeign('lesson_content_order_lesson_id_foreign');
            $table->dropForeign('lesson_content_order_media_type_id_foreign');
        });

        Schema::dropIfExists('lesson_content_order');
    }
}
