<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsers1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('avatar_id')->after('last_seen_org_id')->nullable()->dafault(null);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('avatar_id')->references('id')->on('files')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_avatar_id_foreign');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_id');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->after('confirm_code')->nullable()->dafault(null);
        });
    }
}
