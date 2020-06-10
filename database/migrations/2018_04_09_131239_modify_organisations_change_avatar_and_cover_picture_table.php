<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyOrganisationsChangeAvatarAndCoverPictureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('cover_picture');
            $table->dropColumn('logo');
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->unsignedInteger('logo_id')->nullable()->after('id')->default(null);
            $table->unsignedInteger('cover_picture_id')->after('logo_id')->nullable()->dafault(null);
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->foreign('logo_id')->references('id')->on('files')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('cover_picture_id')->references('id')->on('files')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropForeign('organisations_logo_id_foreign');
            $table->dropForeign('organisations_cover_picture_id_foreign');
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('logo_id');
            $table->dropColumn('cover_picture_id');
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('zip')->default(null);
            $table->string('cover_picture')->after('logo')->nullable()->dafault(null);
        });
    }
}
