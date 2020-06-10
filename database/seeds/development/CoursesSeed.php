<?php

namespace Database\Seeds\Development;

use App\Models\LessonComments;
use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\UsersCourse;
use App\Models\Lesson;
use App\Models\Marketplace;
use Faker\Generator as Faker;

class CoursesSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        Course::truncate();
        Lesson::truncate();
        LessonComments::truncate();
        UsersCourse::truncate();
        Marketplace::truncate();

        factory(Course::class, 500)->create()->each(function (Course $c) use ($faker) {

            factory(Lesson::class, random_int(1, 10))->create()->each(function (Lesson $l) use ($c, $faker) {
                $l->course_id = $c->id;
                $l->order = (Lesson::where('course_id', $c->id)->count()) + 1;
                $l->save();
            });

        });
    }
}
