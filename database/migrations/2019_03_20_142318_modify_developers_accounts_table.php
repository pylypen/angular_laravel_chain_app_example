<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyDevelopersAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('developers_accounts', function (Blueprint $table) {
            $table->string('email', 255)->after('issued_to')->nullable()->default(null);
        });

        Schema::table('developers_accounts', function (Blueprint $table) {
            $table->index('email');
            $table->index('acc_key');
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
            $table->dropIndex('developers_accounts_email_index');
            $table->dropIndex('developers_accounts_acc_key_index');
        });

        Schema::table('developers_accounts', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
}
