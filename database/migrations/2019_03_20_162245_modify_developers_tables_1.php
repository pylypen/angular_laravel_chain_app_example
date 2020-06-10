<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyDevelopersTables1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('developers_accounts', function (Blueprint $table) {
            $table->timestamp('dev_token_issued_at')->after('email')->nullable()->default(null);
            $table->dropColumn('deleted_at');
        });

        Schema::table('developers_accounts_organisations', function (Blueprint $table) {
            $table->timestamp('org_token_issued_at')->after('org_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('developers_accounts', function (Blueprint $table) {
            $table->softDeletes();
            $table->dropColumn('dev_token_issued_at');
        });

        Schema::table('developers_accounts_organisations', function (Blueprint $table) {
            $table->dropColumn('org_token_issued_at');
        });
    }
}
