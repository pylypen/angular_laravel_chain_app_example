<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUsersAddLastSeenOrgId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('last_seen_org_id')->after('id')->nullable()->default(null);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('last_seen_org_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('set null');
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
            $table->dropForeign('users_last_seen_org_id_foreign');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_seen_org_id');
        });
    }
}
