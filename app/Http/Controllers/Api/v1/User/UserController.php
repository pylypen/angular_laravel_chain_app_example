<?php

namespace App\Http\Controllers\Api\v1\Users;

use App\Http\Traits\ManageableTrait;
use App\Models\Course;
use App\Models\Marketplace;
use App\Models\MarketplaceStatuses;
use App\Models\UsersCourse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\API\v1\Users\UserCreateRequest;
use App\Http\Requests\API\v1\Users\UserUpdateRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    use ManageableTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->_set_success(User::with([
            'courses',
            'usersAdmins',
            'usersAssignerCourses',
            'usersCourses',
            'coursesContributors',
            'usersSites',
            'usersTeams'
        ])->get());
    }


    /**
     * Before Store a newly created resource in storage.
     *
     * @param UserCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function beforeStore(UserCreateRequest $request)
    {
        return $this->_set_success($request);
    }

    /**
     * Display the specified resource.
     *
     * @param string $subdomain
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($subdomain, $id)
    {
        $data = User::with([
            'courses',
            'usersAdmins',
            'usersAssignerCourses',
            'usersCourses',
            'coursesContributors',
            'usersSites',
            'usersTeams'
        ])->find($id);
        if ($data) {
            return $this->_set_success($data);
        } else {
            return $this->_set_error(['user' => [__('user.show_error', ['id' => $id])]]);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param UserUpdateRequest $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UserUpdateRequest $request, $id)
    {
        $data = $request->only('first_name', 'last_name', 'title', 'email', 'password', 'is_internal', 'remember_token');
        $data_id = User::with([
            'courses',
            'usersAdmins',
            'usersAssignerCourses',
            'usersCourses',
            'coursesContributors',
            'usersSites',
            'usersTeams'
        ])->find($id);
        $data['password'] = Hash::make($data['password']);
        foreach ($data as $key => $param) {
            $data_id[$key] = $param;
        }
        $data_id->save();
        $data = User::with([
            'courses',
            'usersAdmins',
            'usersAssignerCourses',
            'usersCourses',
            'coursesContributors',
            'usersSites',
            'usersTeams'
        ])->find($data_id->id);
        if ($data_id) {
            return $this->_set_success($data);
        } else {
            return $this->_set_error(['user' => [__('user.update_error')]]);

        }
    }

    /**
     * Get Courses Snapshot
     *
     * @param string $subdomain
     * @param integer $user_id
     *
     * @return \Illuminate\Http\Response
     */

    public function getCoursesSnapshot($subdomain, $user_id)
    {
        $coursesId = [];
        $user = Auth::user();

        $courses = UsersCourse::where(['user_id' => $user_id, 'organisation_id' => $user->last_seen_org_id])
            ->with(['marketplace'])->get();

        foreach ($courses as $course) {
            $acceptedStatus = MarketplaceStatuses::where('status', 'accepted')->first()->id;
            if ($course->marketplace->marketplace_status_id == $acceptedStatus) {
                $coursesId[] = $course->course_id;
            }
        }

        $response = Course::whereIn('courses.id', $coursesId)
            ->select([
                'courses.*',
                DB::raw('ROUND(AVG(lessons_progress_status.percent)) as completion')
            ])
            ->join('lessons', function ($join) {
                $join->on('lessons.course_id', '=', 'courses.id');
                $join->where('lessons.deleted_at', null);
            })
            ->leftJoin('users_courses_progress', function ($join) use ($user_id) {
                $join->on('users_courses_progress.lesson_id', '=', 'lessons.id');
                $join->where('users_courses_progress.user_id', $user_id);
            })
            ->leftJoin('lessons_progress_status', 'lessons_progress_status.id', '=', 'users_courses_progress.progress_status_id')
            ->groupBy('courses.id')
            ->get();

        return $this->_set_success($response);

    }

    /**
     * Users Snapshot in csv file
     *
     * @param string $subdomain
     * @param integer $course_id
     *
     * @return \Illuminate\Http\Response
     */

    public function UsersSnapshotCsv($subdomain, $course_id)
    {
        $users = [];
        $teams = $this->getManageableTeams();

        if (count($teams)) {

            $marketplace = Marketplace::select('id')->where([
                'organisation_id' => Auth::user()->last_seen_org_id,
                'marketplace_status_id' => 2,
                'is_published' => 1,
                'course_id' => $course_id
            ])->whereIn('team_id', $teams)->get()->toArray();

            if (!empty($marketplace)) {

                $usersTeam = [];
                $marketplace_id = [];

                foreach ($marketplace as $k => $m) {

                    $marketplace_id[] = $m['id'];
                    $users = UsersCourse::select('user_id')->where('marketplace_id', $m['id'])->get()->toArray();

                    foreach ($users as $user) {
                        $usersTeam[] = $user['user_id'];
                    }

                }
                $usersTeam = array_unique($usersTeam);
                $usersTeam = join(', ', $usersTeam);
                $marketplace_id = join(', ', $marketplace_id);

                $users = User::hydrate(DB::select('
                     SELECT
                       `users`.`contact_email`,
                            `users`.`first_name`,
                            `users`.`last_name`,
                            `users`.`nickname`,
                            `users`.`contact_email`,
                            `courses`.`name` AS course_name,
                       ROUND(AVG(`lessons_progress_status`.`percent`)) AS course_progress
                     FROM `users`
                     LEFT JOIN `users_courses`
                       ON `users_courses`.`user_id` = `users`.`id` AND `users_courses`.`marketplace_id` IN (' . $marketplace_id . ')
                     LEFT JOIN `lessons`
                       ON `lessons`.`course_id` = `users_courses`.`course_id` AND `lessons`.`deleted_at` IS NULL
                     LEFT JOIN `users_courses_progress`
                       ON `users_courses_progress`.`user_id` = `users`.`id` AND `users_courses_progress`.`lesson_id` = lessons.id
                     LEFT JOIN `lessons_progress_status`
                       ON `lessons_progress_status`.`id` = `users_courses_progress`.`progress_status_id`
                       LEFT JOIN `courses`
                       ON `courses`.`id` = `users_courses`.`course_id`
                     WHERE `users`.`id` IN (' . $usersTeam . ')
                             AND `users`.`deleted_at` IS NULL
                     GROUP BY `users`.`id`;
                     '));

                $users = json_decode(json_encode($users), true);
            }
        }

        $csv =  Excel::create('users', function ($excel) use ($users) {
            $excel->sheet('mySheet', function ($sheet) use ($users) {
                $sheet->fromArray($users);
            });
        })->download('csv');

        return $this->_set_success($csv);
    }
}
