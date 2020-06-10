<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevelopersAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('developers_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('acc_key', 255)->nullable()->default(null);
            $table->string('acc_secret', 255)->nullable()->default(null);
            $table->string('issued_to', 255)->nullable()->defaule(null);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('developers_accounts');
    }
}
