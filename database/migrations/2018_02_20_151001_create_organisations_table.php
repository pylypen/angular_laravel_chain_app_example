<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('organisations', function (Blueprint $table) {
            $table->increments('id');

            $table->string('email')->nullable()->default(null);
            $table->string('name')->nullable()->default(null);
            $table->string('phone_number')->nullable()->default(null);
            $table->string('state')->nullable()->default(null);
            $table->string('city')->nullable()->default(null);
            $table->string('street')->nullable()->default(null);
            $table->string('zip')->nullable()->default(null);
            $table->string('logo')->nullable()->default(null);

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
        Schema::dropIfExists('organisations');
    }
}
