<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('organisation_id')->nullable()->default(null);
            $table->unsignedInteger('site_id')->nullable()->default(null);
            $table->unsignedInteger('team_id')->nullable()->default(null);
            $table->unsignedInteger('course_id')->nullable()->default(null);
            $table->unsignedInteger('marketplace_status_id')->nullable()->default(null);
            $table->unsignedInteger('reviewed_by')->nullable()->default(null);
            $table->boolean('is_published')->default(0);
            $table->boolean('review_completed')->default(0);
            $table->text('review_message')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('marketplace', function (Blueprint $table) {
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('site_id')->references('id')->on('sites')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('marketplace_status_id')->references('id')->on('marketplace_statuses')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
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
            $table->dropForeign('marketplace_organisation_id_foreign');
            $table->dropForeign('marketplace_site_id_foreign');
            $table->dropForeign('marketplace_team_id_foreign');
            $table->dropForeign('marketplace_course_id_foreign');
            $table->dropForeign('marketplace_marketplace_status_id_foreign');
            $table->dropForeign('marketplace_reviewed_by_foreign');
        });

        Schema::dropIfExists('marketplace');
    }
}
