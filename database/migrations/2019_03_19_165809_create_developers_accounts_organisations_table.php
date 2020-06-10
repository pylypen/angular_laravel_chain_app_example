<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevelopersAccountsOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('developers_accounts_organisations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('dev_acc_id')->nullable()->default(null);
            $table->unsignedInteger('org_id')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table('developers_accounts_organisations', function (Blueprint $table) {
            $table->foreign('dev_acc_id')->references('id')->on('developers_accounts')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('org_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('developers_accounts_organisations');
    }
}
