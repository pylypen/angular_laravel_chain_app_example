<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organisation;
use App\Models\Marketplace;
use App\Models\UsersCoursesProgress;
use App\Models\UsersOrganisations;
use App\Models\User;
use App\Models\Course;
use App\Models\UsersCourse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\WeeklyReportOrgAdmin;

class WeeklyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:weekly-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orgs = Organisation::get();

        foreach ($orgs as $org) {
            /***************************
            /* Courses Created Data
            /***************************/
            $courseUsers = [];
            $marketplace = Marketplace::whereRaw(
                '`created_at` <= NOW() AND `created_at` >= NOW() - INTERVAL 7 DAY'
            )->where('organisation_id', $org->id)->groupBy('course_id');
            
            $courseCount = $marketplace->count();
            $courses = $marketplace->get();

            foreach ($courses as $course) {
                @$courseUsers[] = $course->course->author_id;
            }

            $courseUsers = count(array_unique($courseUsers));

            /***************************
            /* Users Joined Data
            /***************************/

            $createdUsers = UsersOrganisations::whereRaw(
                '`created_at` <= NOW() AND `created_at` >= NOW() - INTERVAL 7 DAY'
            )->where('organisation_id', $org->id)->count();

            $joinedUsers = User::whereRaw(
                '`users`.`created_at` <= NOW() AND `users`.`created_at` >= NOW() - INTERVAL 7 DAY 
                AND `users`.`updated_at` != `users`.`created_at` and `users`.`confirm_code` is null'
            )->join('users_organisations', function ($join) use ($org) {
                $join->on('users_organisations.user_id', '=', 'users.id');
                $join->where('users_organisations.organisation_id', $org->id);
            })->count();

            /***************************
            /* Completed Courses Data
            /***************************/

            $usersCompletedProgress = [];
            $coursesCompleted = [];
            $usersLessonsProgress = UsersCoursesProgress::whereRaw(
                '`updated_at` <= NOW() AND `updated_at` >= NOW() - INTERVAL 7 DAY'
            )->groupBy('user_id')->get();

            foreach ($usersLessonsProgress as $progress) {
                $courseProgress = Course::whereIn('courses.id',
                    UsersCourse::select('course_id')
                        ->where([
                            'user_id' => $progress->user_id,
                            'organisation_id' => $org->id
                        ])->get()
                )->select([
                    'courses.*',
                    DB::raw('ROUND(AVG(lessons_progress_status.percent)) as completion')
                ])
                    ->leftJoin('users_courses_progress', function ($join) use ($progress) {
                        $join->on('users_courses_progress.course_id', '=', 'courses.id');
                        $join->where('users_courses_progress.user_id', $progress->user_id);
                        $join->whereRaw('`users_courses_progress`.`updated_at` <= NOW() AND `users_courses_progress`.`updated_at` >= NOW() - INTERVAL 7 DAY');
                    })
                    ->leftJoin('lessons_progress_status', 'lessons_progress_status.id', '=', 'users_courses_progress.progress_status_id')
                    ->groupBy('courses.id')
                    ->get()->toArray();

                foreach ($courseProgress as $cp) {
                    if ($cp['completion'] == 100) {
                        @$usersCompletedProgress[] = $progress->user_id;
                        @$coursesCompleted[] = $cp['id'];
                    }
                }
            }
            $usersCompletedProgress = count(array_unique($usersCompletedProgress));
            $coursesCompleted = count(array_unique($coursesCompleted));

            $data = [
                'orgName' => $org->name,
                'courseCount' => $courseCount,
                'courseUsers' => $courseUsers,
                'createdUsers' => $createdUsers,
                'joinedUsers' => $joinedUsers,
                'coursesCompleted' => $coursesCompleted,
                'usersCompletedProgress' => $usersCompletedProgress
            ];
            $emails = [];

            if (env('APP_ENV', false) == 'development') {
                $emails = [
                    'pylypen@gmail.com'
                ];
            } else {
                foreach ($org->usersOrganisations()->where('is_admin', 1)->get() as $ou) {
                    if (!empty($ou->user->email)) {
                        @$emails[] = $ou->user->email;
                    }
                }
            }

            Mail::to($emails)
                ->queue(new WeeklyReportOrgAdmin($data));

        }
    }
}
