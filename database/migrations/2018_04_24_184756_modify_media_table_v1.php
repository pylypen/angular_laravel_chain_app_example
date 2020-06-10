<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyMediaTableV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('name');
            $table->dropColumn('display_name');
            $table->dropColumn('deleted_at');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->unsignedInteger('file_id')->after('lesson_id')->nullable()->default(null);
            $table->unsignedInteger('media_extension_id')->after('file_id')->nullable()->default(null);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->foreign('file_id')->references('id')->on('files')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('media_extension_id')->references('id')->on('media_extensions')->onUpdate('cascade')->onDelete('cascade');
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
            $table->dropForeign('media_file_id_foreign');
            $table->dropForeign('media_media_extension_id_foreign');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('file_id');
            $table->dropColumn('media_extension_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->string('type')->after('lesson_id')->nullable()->default(null);
            $table->string('name')->after('type')->nullable()->default(null);
            $table->string('display_name')->after('name')->nullable()->default(null);
            $table->softDeletes();
        });
    }
}
