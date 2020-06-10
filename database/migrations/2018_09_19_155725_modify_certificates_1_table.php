<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCertificates1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->unsignedInteger('org_id')->after('course_id')->nullable()->default(null);
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->foreign('org_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign('certificates_org_id_foreign');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('org_id');
        });
    }
}
