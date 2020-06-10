<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCoursesAddThumbnailAndFeaturedBackgroundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('thumbnail_id')->nullable()->after('author_id')->default(null);
            $table->unsignedInteger('featured_background_id')->nullable()->after('thumbnail_id')->default(null);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreign('thumbnail_id')->references('id')->on('files')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('featured_background_id')->references('id')->on('files')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign('courses_thumbnail_id_foreign');
            $table->dropForeign('courses_featured_background_id_foreign');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('thumbnail_id');
            $table->dropColumn('featured_background_id');
        });
    }
}
