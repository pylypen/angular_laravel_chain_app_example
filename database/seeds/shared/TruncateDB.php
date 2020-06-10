<?php

namespace Database\Seeds\Shared;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TruncateDB extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('chats')->truncate();
        DB::table('courses')->truncate();
        DB::table('courses_contributors')->truncate();
        DB::table('files')->truncate();
        DB::table('lessons')->truncate();
        DB::table('lessons_progress_status')->truncate();
        DB::table('lesson_comments')->truncate();
        DB::table('marketplace')->truncate();
        DB::table('marketplace_statuses')->truncate();
        DB::table('media')->truncate();
        DB::table('media_extensions')->truncate();
        DB::table('media_types')->truncate();
        DB::table('messages')->truncate();
        DB::table('organisations')->truncate();
        DB::table('password_resets')->truncate();
        DB::table('secret_questions')->truncate();
        DB::table('sites')->truncate();
        DB::table('teams')->truncate();
        DB::table('users')->truncate();
        DB::table('users_chats')->truncate();
        DB::table('users_chats_messages')->truncate();
        DB::table('users_courses')->truncate();
        DB::table('users_courses_progress')->truncate();
        DB::table('users_organisations')->truncate();
        DB::table('users_secret_answers')->truncate();
        DB::table('users_sites')->truncate();
        DB::table('users_teams')->truncate();
    }
}
