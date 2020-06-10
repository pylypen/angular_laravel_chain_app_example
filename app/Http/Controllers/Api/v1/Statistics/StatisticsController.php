<?php

namespace App\Http\Controllers\Api\v1\Statistics;

use App\Http\Controllers\Controller;
use App\Http\Traits\ManageableTrait;
use App\Models\Course;
use App\Models\Marketplace;
use App\Models\UsersCourse;
use Illuminate\Support\Facades\DB;
use Auth;

class StatisticsController extends Controller
{
    use ManageableTrait;

    public function courses()
    {
        $teams = $this->getManageableTeams();
        $ret = [];

        if (count($teams)) {
            $courses = Course::select([
                DB::raw('DISTINCT courses.*')
            ])
                ->join('marketplace', function ($join) use ($teams) {
                    $join->on('marketplace.course_id', '=', 'courses.id');
                    $join->whereIn('marketplace.team_id', $teams);
                })
                ->get();
            
            if (count($courses)) {
                foreach ($courses as $k => $c) {
                    $ret[$k]['course'] = $c;

                    $ret[$k]['assigned'] = count(DB::select("
                        SELECT
                          DISTINCT u.id AS user_id
                        FROM courses c
                          JOIN marketplace m ON c.id = m.course_id AND m.marketplace_status_id = 2
                          JOIN users_courses uc ON m.id = uc.marketplace_id
                          JOIN users u ON u.id = uc.user_id AND u.deleted_at IS NULL
                        WHERE c.id = " . $c->id . " AND c.deleted_at IS NULL AND m.team_id IN (" . join(',', $teams) . ")
                   "));

                    // todo: account deleted lessons!
                    $ret[$k]['started'] = count(DB::select("
                      SELECT
                        u.id              AS user_id,
                        ROUND(AVG(lps.percent)) AS completion
                      FROM courses c
                        JOIN marketplace m ON c.id = m.course_id AND m.marketplace_status_id = 2
                        JOIN users_courses uc ON m.id = uc.marketplace_id
                        JOIN users u ON u.id = uc.user_id AND u.deleted_at IS NULL
                        JOIN lessons l ON l.course_id = uc.course_id AND l.deleted_at IS NULL
                        JOIN users_courses_progress ucp ON ucp.lesson_id = l.id AND ucp.user_id = u.id
                        JOIN lessons_progress_status lps ON lps.id = ucp.progress_status_id
                      WHERE c.id = " . $c->id . " AND c.deleted_at IS NULL AND m.team_id IN (" . join(',', $teams) . ")
                      GROUP BY u.id
                      HAVING completion > 0;
                      "));

                    // todo: account deleted lessons!
                    $ret[$k]['completed'] = count(DB::select("
                      SELECT
                        u.id              AS user_id,
                        ROUND(AVG(lps.percent)) AS completion
                      FROM courses c
                        JOIN marketplace m ON c.id = m.course_id AND m.marketplace_status_id = 2
                        JOIN users_courses uc ON m.id = uc.marketplace_id
                        JOIN users u ON u.id = uc.user_id AND u.deleted_at IS NULL
                        JOIN lessons l ON l.course_id = uc.course_id AND l.deleted_at IS NULL
                        JOIN users_courses_progress ucp ON ucp.lesson_id = l.id AND ucp.user_id = u.id
                        JOIN lessons_progress_status lps ON lps.id = ucp.progress_status_id
                      WHERE c.id = " . $c->id . " AND c.deleted_at IS NULL AND m.team_id IN (" . join(',', $teams) . ")
                      GROUP BY u.id
                      HAVING completion = 100;
                      "));

                    // RAW SQL ABOVE BECAUSE THIS IS NOT WORKING BUT GENERATED SQL IS OK!!!!!
                    /*   $ret[$k]['started'] = Course::select([
                           DB::raw('users_courses.user_id'),
                           DB::raw('ROUND(AVG(lessons_progress_status.percent)) AS completion'),
                       ])
                           ->join('marketplace', function ($join) use ($teams) {
                               $join->on('marketplace.course_id', '=', 'courses.id');
                               $join->whereIn('marketplace.team_id', $teams);
                           })
                           ->leftJoin('users_courses', function ($join) {
                               $join->on('users_courses.marketplace_id', '=', 'marketplace.id');
                           })
                           ->leftJoin('users_courses_progress', function ($join) {
                               $join->on('users_courses_progress.course_id', '=', 'users_courses.course_id');
                               $join->where('users_courses_progress.user_id', '=', 'users_courses.user_id');
                           })
                           ->leftJoin('lessons_progress_status', function ($join) {
                               $join->on('lessons_progress_status.id', '=', 'users_courses_progress.progress_status_id');
                           })
                           ->where(['courses.id' => $c->id])
                           ->groupBy('users_courses.user_id')
                           ->get()->makeHidden(['featured_background', 'thumbnail']);*/
                }
            }
        }

        return $this->_set_success($ret);

    }

}