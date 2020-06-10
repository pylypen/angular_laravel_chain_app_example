<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCourseAddOriginalAuthorId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('original_author_id')->nullable()->default(null)->after('author_id');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreign('original_author_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
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
            $table->dropForeign('courses_original_author_id_foreign');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('original_author_id');
        });
    }
}
