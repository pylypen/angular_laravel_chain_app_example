<?php

namespace Database\Seeds\Development;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\UsersCourse;
use App\Models\Lesson;
use App\Models\LessonComments;
use App\Models\Marketplace;
use App\Models\MarketplaceStatuses;
use App\Models\UsersOrganisations;
use App\Models\Site;
use App\Models\Team;
use App\Models\UsersSite;
use App\Models\UsersTeam;

class LessonsCommentsSeed extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $lessons = Lesson::get();
        foreach ($lessons as $lesson) {
            $UsersCourse = UsersCourse::where('course_id', $lesson->course_id)->count();

            if ($UsersCourse) {
                $rand = random_int(1, 5);
                for ($i = 1 ; $i <= $rand ; $i++) {
                    factory(LessonComments::class, 1)->create([
                        'lesson_id' => $lesson->id,
                        'user_id' => UsersCourse::where('course_id', $lesson->course_id)->inRandomOrder()->first()->user_id
                    ]);
                }
            }
        }
    }
}
