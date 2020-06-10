<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyMarketplaceAddWildcardAssignment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketplace', function (Blueprint $table) {
            $table->boolean('is_wildcard_assigned')->after('is_published')->default(0);
            $table->boolean('is_wildcard_obligatory')->after('is_wildcard_assigned')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketplace', function (Blueprint $table) {
            $table->dropColumn('is_wildcard_assigned');
            $table->dropColumn('is_wildcard_obligatory');
        });
    }
}
