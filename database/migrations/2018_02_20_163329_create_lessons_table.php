<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('course_id')->nullable()->default(null);
            $table->string('name')->nullable()->default(null);
	        $table->text( 'description' )->nullable()->default( null );
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('courses')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign('lessons_course_id_foreign');
        });

        Schema::dropIfExists('lessons');
    }
}
