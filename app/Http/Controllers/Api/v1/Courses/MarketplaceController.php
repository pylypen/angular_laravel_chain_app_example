<?php

namespace App\Http\Controllers\API\v1\Courses;

use App\Http\Controllers\Controller;
use App\Http\Traits\ManageableTrait;
use App\Models\Course;
use App\Models\Mappers\Lesson;
use App\Models\Marketplace;
use App\Models\MarketplaceStatuses;
use App\Models\Team;
use App\Models\UsersCourse;
use App\Models\UsersCoursesProgress;
use App\Models\UsersOrganisations;
use App\Models\UsersSite;
use App\Models\UsersTeam;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\API\v1\Marketplace\MarketplaceReviewRequest;
use App\Http\Requests\API\v1\Marketplace\MarketplaceViewRequest;
use App\Http\Requests\API\v1\Marketplace\MarketplaceSubmitReviewRequest;
use App\Http\Requests\API\v1\Marketplace\MarketplaceWildcardAssignRequest;
use App\Http\Requests\API\v1\Marketplace\MarketplacePersonalAssignRequest;
use Illuminate\Support\Facades\DB;

class MarketplaceController extends Controller
{
    use ManageableTrait;

    /**
     * Show Hide
     *
     * @param MarketplaceViewRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function showHide(MarketplaceViewRequest $request)
    {
        $data = $request->only(['team_id', 'course_id', 'show']);

        $team = Team::where('id', $data['team_id'])->first();
        $course = Course::where('id', $data['course_id'])->first();
        $mkplace = Marketplace::where([
            'team_id' => $team->id,
            'course_id' => $course->id
        ])->first();

        if ($team && $course && $mkplace && $course->author_id == Auth::user()->id) {
            $mkplace->is_published = ($data['show']) ? 1 : 0;
            $mkplace->save();

            return $this->_set_success([]);
        }

        return $this->_set_error(['course_id' => [__('marketplace.error')]], 422);
    }

    /**
     * Review
     *
     * @param MarketplaceReviewRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function requestReview(MarketplaceReviewRequest $request)
    {
        $data = $request->only(['team_id', 'course_id']);

        $team = Team::where('id', $data['team_id'])->first();
        $course = Course::where('id', $data['course_id'])->first();

        $mkplace = Marketplace::firstOrNew([
            'team_id' => $team->id,
            'course_id' => $course->id
        ]);

        if ($team && $course && $mkplace && $course->author_id == Auth::user()->id) {
            $mkplace->organisation_id = $team->organisation_id;
            $mkplace->site_id = $team->site_id;
            $mkplace->marketplace_status_id = 1; // pending review
            $mkplace->reviewed_by = null;
            $mkplace->is_published = 1;
            $mkplace->review_completed = 0;
            $mkplace->review_message = null;

            $mkplace->save();

            return $this->_set_success([]);
        }

        return $this->_set_error(['course_id' => [__('marketplace.error')]], 422);
    }

    /**
     * Get Marketplace Approvals
     *
     * @param string $subdomain
     * @param int $course_id
     *
     * @return \Illuminate\Http\Response
     */
    public function getMarketplaceApprovals($subdomain, $course_id)
    {

        $teams = $this->getManageableTeams();

        if (count($teams)) {

            $response = [
                'marketplace' => [],
                'statuses' => []
            ];

            $response['marketplace'] = Marketplace::where([
                'organisation_id' => Auth::user()->last_seen_org_id,
                'course_id' => $course_id
            ])->whereIn('team_id', $teams)
                ->with(['site', 'team', 'reviewedBy'])
                ->orderBy('site_id', 'ASC')->get();

            $response['statuses'] = MarketplaceStatuses::get();

            return $this->_set_success($response);
        }

        return $this->_set_error(['course_id' => [__('marketplace.error')]], 422);

    }

    /**
     * Get Marketplace Assigns
     *
     * @param string $subdomain
     * @param int $course_id
     *
     * @return \Illuminate\Http\Response
     */
    public function getMarketplaceAssigns($subdomain, $course_id)
    {
        $teams = $this->getManageableTeams();

        if (count($teams)) {

            $marketplace = Marketplace::where([
                'organisation_id' => Auth::user()->last_seen_org_id,
                'marketplace_status_id' => 2,
                'course_id' => $course_id
            ])->whereIn('team_id', $teams)
                ->with(['site', 'reviewedBy', 'usersCourse', 'team'])
                ->orderBy('site_id', 'ASC')->get()->toArray();

            if (!empty($marketplace)) {
                foreach ($marketplace as $k => $m) {

                    $progress = DB::select('
                       SELECT ROUND(AVG(`lessons_progress_status`.`percent`)) AS course_progress
                        FROM `users_courses`
                          JOIN `lessons` ON `lessons`.`course_id` = `users_courses`.`course_id` AND `lessons`.`deleted_at` IS NULL
                          LEFT JOIN `users_courses_progress`
                            ON `users_courses_progress`.`user_id` = `users_courses`.`user_id` AND
                               `users_courses_progress`.`lesson_id` = `lessons`.`id`
                          LEFT JOIN `lessons_progress_status`
                            ON `lessons_progress_status`.`id` = `users_courses_progress`.`progress_status_id`
                        WHERE
                          `users_courses`.`course_id` = ' . $course_id . '
                          AND `users_courses`.`marketplace_id` = ' . $m['id'] . '
                          AND `team_id` = ' . $m['team']['id'] . '
                        GROUP BY `team_id`;  
                    ');

                    if (!empty($progress)) {
                        $marketplace[$k]['team']['course_progress'] = $progress[0]->course_progress;
                    } else {
                        $marketplace[$k]['team']['course_progress'] = 0;
                    }


                    $users = UsersTeam::select('user_id')->where('team_id', $m['team']['id'])->get();
                    $usersTeam = [];
                    foreach ($users as $user) {
                        $usersTeam[] = $user->user_id;
                    }
                    $usersTeam = join(', ', $usersTeam);

                    $marketplace[$k]['team']['users'] = DB::select('
                    SELECT
                      `users`.*,
                      ROUND(AVG(`lessons_progress_status`.`percent`)) AS course_progress
                    FROM `users`
                    LEFT JOIN `users_courses` 
                      ON `users_courses`.`user_id` = `users`.`id` AND `users_courses`.`marketplace_id` = ' . $m['id'] . '
                    LEFT JOIN `lessons` 
                      ON `lessons`.`course_id` = `users_courses`.`course_id` AND `lessons`.`deleted_at` IS NULL
                    LEFT JOIN `users_courses_progress`
                      ON `users_courses_progress`.`user_id` = `users`.`id` AND `users_courses_progress`.`lesson_id` = lessons.id
                    LEFT JOIN `lessons_progress_status` 
                      ON `lessons_progress_status`.`id` = `users_courses_progress`.`progress_status_id`
                    WHERE `users`.`id` IN (' . $usersTeam . ') 
                            AND `users`.`deleted_at` IS NULL
                    GROUP BY `users`.`id`;
                    ');

                    // RAW SQL ABOVE BECAUSE THIS IS NOT WORKING BUT GENERATED SQL IS OK!!!!!
                    /*    $marketplace[$k]['team']['users'] = User::select(['users.*',
                            DB::raw('ROUND(AVG(lessons_progress_status.percent)) as course_progress')
                        ])
                            ->leftjoin('users_courses', function ($join) use ($m) {
                                $join->on('users_courses.user_id', '=', 'users.id');
                                $join->where('users_courses.marketplace_id', $m['id']);
                            })
                            ->leftjoin('lessons', function ($join) {
                                $join->on('lessons.course_id', '=', 'users_courses.course_id');
                                $join->where('lessons.deleted_at', null);
                            })
                            ->leftJoin('users_courses_progress', function ($join) {
                                $join->on('users_courses_progress.user_id', '=', 'users.id');
                                $join->where('users_courses_progress.lesson_id', 'lessons.id');
                            })
                            ->leftJoin('lessons_progress_status', function ($join) {
                                $join->on('lessons_progress_status.id', '=', 'users_courses_progress.progress_status_id');
                            })
                            ->whereIn('users.id', UsersTeam::select('user_id')->where('team_id', $m['team']['id'])->get())
                            ->groupBy('users.id')->get();*/
                }
            }

            return $this->_set_success($marketplace);

        }

        return $this->_set_error(['course_id' => [__('marketplace.error')]], 422);
    }

    /**
     * Get Stats Details
     *
     * @param string $subdomain
     * @param int $course_id
     *
     * @return \Illuminate\Http\Response
     */
    public function getStatsDetails($subdomain, $course_id)
    {
        $details = [];
        $teams = $this->getManageableTeams();

        if (count($teams)) {

            $marketplace = Marketplace::select('id')->where([
                'organisation_id' => Auth::user()->last_seen_org_id,
                'marketplace_status_id' => 2,
                'is_published' => 1,
                'course_id' => $course_id
            ])->whereIn('team_id', $teams)
                ->with(['usersCourse'])
                ->get()->toArray();

            if (!empty($marketplace)) {

                $usersTeam = [];
                $marketplace_id = [];

                foreach ($marketplace as $k => $m) {
                    $marketplace_id[] = $m['id'];

                    foreach ($m['users_course'] as $user) {
                        $usersTeam[] = $user['user_id'];
                    }
                }

                $usersTeam = array_unique($usersTeam);
                $usersTeam = join(', ', $usersTeam);
                $marketplace_id = join(', ', $marketplace_id);

                $details = DB::select('
                     SELECT
                       `users`.*,
                       ROUND(AVG(`lessons_progress_status`.`percent`)) AS course_progress,
                       GROUP_CONCAT(DISTINCT `teams`.`name`) as teams
                     FROM `users`
                     LEFT JOIN `users_courses`
                       ON `users_courses`.`user_id` = `users`.`id` AND `users_courses`.`marketplace_id` IN (' . $marketplace_id . ')
                     LEFT JOIN `lessons`
                       ON `lessons`.`course_id` = `users_courses`.`course_id` AND `lessons`.`deleted_at` IS NULL
                     LEFT JOIN `users_courses_progress`
                       ON `users_courses_progress`.`user_id` = `users`.`id` AND `users_courses_progress`.`lesson_id` = lessons.id
                     LEFT JOIN `lessons_progress_status`
                       ON `lessons_progress_status`.`id` = `users_courses_progress`.`progress_status_id`
                     LEFT JOIN `teams`
                       ON `teams`.`id` = `users_courses`.`team_id` and `teams`.`deleted_at` is NULL
                     WHERE `users`.`id` IN (' . $usersTeam . ')
                             AND `users`.`deleted_at` IS NULL
                     GROUP BY `users`.`id`;
                     ');
            }
        }
        return $this->_set_success($details);
    }


    /**
     * Submit Review
     *
     * @param MarketplaceSubmitReviewRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function submitReview(MarketplaceSubmitReviewRequest $request)
    {
        $data = $request->only(['marketplace']);

        foreach ($data['marketplace'] as $item) {
            $marketplace = Marketplace::where('id', $item['id'])->first();

            $isAdmin = UsersOrganisations::where([
                'organisation_id' => $marketplace->organisation_id,
                'user_id' => Auth::user()->id,
                'is_admin' => 1
            ])->count();


            if (!$isAdmin && $marketplace->site_id) {
                $isAdmin = UsersSite::where([
                    'site_id' => $marketplace->site_id,
                    'user_id' => Auth::user()->id,
                    'is_admin' => 1
                ])->count();
            }

            if (!$isAdmin && $marketplace->team_id) {
                $isAdmin = UsersTeam::where([
                    'team_id' => $marketplace->team_id,
                    'user_id' => Auth::user()->id,
                    'is_admin' => 1
                ])->count();
            }

            if ($marketplace && $isAdmin) {
                $marketplace->marketplace_status_id = $item['marketplace_status_id'];
                $marketplace->reviewed_by = Auth::user()->id;
                $marketplace->review_completed = 1;
                $marketplace->review_message = ($item['marketplace_status_id'] != 2) ? $item['review_message'] : '';
                $marketplace->save();
            }
        }

        return $this->_set_success([]);
    }

    /**
     * Wildcard Assign
     *
     * @param MarketplaceWildcardAssignRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function wildcardAssign(MarketplaceWildcardAssignRequest $request)
    {
        $data = $request->only(['marketplace_id', 'is_obligatory', 'assign']);

        $marketplace = Marketplace::where('id', $data['marketplace_id'])->first();

        if ($marketplace) {
            $manageableTeams = $this->getManageableTeams();
            if (in_array($marketplace->team_id, $manageableTeams)) {

                if ((int)$data['assign']) {

                    $marketplace->is_wildcard_assigned = 1;
                    $marketplace->is_wildcard_obligatory = (int)$data['is_obligatory'];
                    $marketplace->save();

                    $users_team = UsersTeam::where('team_id', $marketplace->team_id)->get();
                    foreach ($users_team as $ut) {

                        $uc = UsersCourse::firstOrNew([
                            'marketplace_id' => $marketplace->id,
                            'user_id' => $ut->user_id
                        ]);
                        $uc->organisation_id = $marketplace->organisation_id;
                        $uc->site_id = $marketplace->site_id;
                        $uc->team_id = $marketplace->team_id;
                        $uc->user_id = $ut->user_id;
                        $uc->marketplace_id = $marketplace->id;
                        $uc->course_id = $marketplace->course_id;
                        $uc->assigned_by_id = Auth::user()->id;
                        $uc->is_team_assigned = 1;
                        $uc->is_obligatory = (int)$data['is_obligatory'];
                        $uc->save();

                        $lessons = Lesson::where([
                            'course_id' => $marketplace->course_id
                        ])->get();

                        foreach ($lessons as $lesson) {
                            $ucp = UsersCoursesProgress::firstOrNew([
                                'user_id' => $ut->user_id,
                                'course_id' => $marketplace->course_id,
                                'lesson_id' => $lesson->id
                            ]);

                            if (!$ucp->progress_status_id) {
                                $ucp->progress_status_id = 1;
                            }

                            $ucp->save();
                        }
                    }
                } else {
                    $marketplace->is_wildcard_assigned = 0;
                    $marketplace->is_wildcard_obligatory = 0;
                    $marketplace->save();

                    UsersCourse::where([
                        'marketplace_id' => $marketplace->id,
                    ])->update([
                        'is_team_assigned' => 0
                    ]);
                }

                return $this->_set_success([]);
            }
        }

        return $this->_set_error(['course_id' => [__('marketplace.error')]], 422);
    }

    /**
     * Personal Assign
     *
     * @param MarketplacePersonalAssignRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function personalAssign(MarketplacePersonalAssignRequest $request)
    {
        $data = $request->only(['marketplace_id', 'users']);

        $marketplace = Marketplace::where('id', $data['marketplace_id'])->first();

        if ($marketplace && !$marketplace->is_wildcard_assigned) {
            $manageableTeams = $this->getManageableTeams();
            if (in_array($marketplace->team_id, $manageableTeams)) {
                $keeping = [];
                foreach ($data['users'] as $user_id) {

                    $uc = UsersCourse::firstOrNew([
                        'marketplace_id' => $marketplace->id,
                        'user_id' => $user_id
                    ]);

                    $uc->organisation_id = $marketplace->organisation_id;
                    $uc->site_id = $marketplace->site_id;
                    $uc->team_id = $marketplace->team_id;
                    $uc->user_id = $user_id;
                    $uc->marketplace_id = $marketplace->id;
                    $uc->course_id = $marketplace->course_id;
                    $uc->assigned_by_id = ($uc->assigned_by_id) ? $uc->assigned_by_id : Auth::user()->id;
                    $uc->is_team_assigned = 0;
                    $uc->is_obligatory = 0;
                    $uc->save();

                    $lessons = Lesson::where([
                        'course_id' => $marketplace->course_id
                    ])->get();

                    foreach ($lessons as $lesson) {
                        $ucp = UsersCoursesProgress::firstOrNew([
                            'user_id' => $user_id,
                            'course_id' => $marketplace->course_id,
                            'lesson_id' => $lesson->id
                        ]);

                        if (!$ucp->progress_status_id) {
                            $ucp->progress_status_id = 1;
                        }

                        $ucp->save();
                    }

                    $keeping[] = $uc->id;
                }

                $to_be_deleted = UsersCourse::where([
                    'marketplace_id' => $marketplace->id,
                    'team_id' => $marketplace->team_id
                ])->whereNotIn('id', $keeping)->get();

                foreach ($to_be_deleted as $to_delete) {
                    //check if user has course assigned via else teams
                    $check = UsersCourse::where([
                        'user_id' => $to_delete->user_id,
                        'course_id' => $to_delete->course_id
                    ])->where('id', '!=', $to_delete->id)->count();

                    if (!$check) {
                        UsersCoursesProgress::where([
                            'user_id' => $to_delete->user_id,
                            'course_id' => $to_delete->course_id
                        ])->delete();
                    }
                    $to_delete->delete();
                }

                return $this->_set_success([]);
            }
        }

        return $this->_set_error(['course_id' => [__('marketplace.error')]], 422);
    }
}
