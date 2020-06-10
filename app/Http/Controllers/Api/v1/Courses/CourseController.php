<?php

namespace App\Http\Controllers\API\v1\Courses;

use Image;
use App\Http\Requests\API\v1\Courses\CoursesCreateRequest;
use App\Http\Requests\API\v1\Courses\CoursesUpdateRequest;
use App\Http\Requests\API\v1\Courses\CoursesUpdateLessonsOrderRequest;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Organisation;
use App\Models\Site;
use App\Models\Team;
use App\Models\UsersCourse;
use App\Models\CoursesContributors;
use App\Models\Files;
use App\Models\UsersOrganisations;
use App\Models\Marketplace;
use App\Models\UsersSite;
use App\Models\UsersTeam;
use App\Models\MarketplaceStatuses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ImageThumbnailProcess;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\ManageableTrait;
use App\Http\Traits\UploadFileTrait;

class CourseController extends Controller
{
    use ManageableTrait;

    use UploadFileTrait;

    const MIMES = [
        'image/png',
        'image/jpeg',
        'image/jpg'
    ];
    
    /**
     * Get List.
     *
     * @return \Illuminate\Http\Response
     */
    public function getList()
    {
        $user = Auth::user();

        $response = [
            'assigned' => [],
            'authoring' => [],
            'managing' => [],
            'team_assignment' => [],
            'site_assignment' => []
        ];
        $coursesId = [];
        $teamId = [];
        $siteId = [];
        $teamCoursesId = [];
        $siteCoursesId = [];

        // Get Team assigned courses

        $teams = UsersTeam::select('team_id')->where(['user_id' => $user->id])->get()->toArray();

        foreach ($teams as $t) {
            $teamId[] = $t['team_id'];
        }

        $teamCourses = UsersCourse::where(['user_id' => $user->id, 'organisation_id' => $user->last_seen_org_id, 'is_team_assigned' => 1])
            ->whereIn('team_id', $teamId)
            ->with(['marketplace'])->get();

        foreach ($teamCourses as $k => $tc) {
            if ($tc->marketplace->marketplace_status_id == 2) {
                $teamCoursesId[] = $tc->course_id;
            }
        }

        $teamCoursesId = array_unique($teamCoursesId);


        if (!empty($teamCoursesId)) {

            $teamCoursesId = join(', ', $teamCoursesId);
            $teamAssignment = Course::hydrate(DB::select('
            SELECT 
            `courses`.*,
            ROUND(AVG(`lessons_progress_status`.`percent`)) AS completion
            FROM `courses`
                     LEFT JOIN `lessons`
                       ON `lessons`.`course_id` = `courses`.`id` AND `lessons`.`deleted_at` IS NULL
                     LEFT JOIN `users_courses_progress`
                       ON `users_courses_progress`.`user_id` = ' . $user->id . ' AND `users_courses_progress`.`lesson_id` = lessons.id
                     LEFT JOIN `lessons_progress_status`
                       ON `lessons_progress_status`.`id` = `users_courses_progress`.`progress_status_id`
            WHERE `courses`.`id` IN (' . $teamCoursesId . ')
             AND `courses`.`deleted_at` IS NULL
            GROUP BY `courses`.`id`;
        '))->load('thumbnail')
                ->toArray();

            foreach ($teamAssignment as $k => $ta) {

                $teams = UsersCourse::select('team_id')->where(['user_id' => $user->id, 'organisation_id' => $user->last_seen_org_id, 'course_id' => $ta['id']])->get()->toArray();
                $teams_id = [];

                foreach ($teams as $t) {
                    $teams_id[] = $t['team_id'];
                }

                $teams_id = join(', ', $teams_id);

                $teamAssignment[$k]['teams'] = DB::select('
                    SELECT `teams`.*
                    FROM `teams`
                    WHERE `teams`.`id` IN (' . $teams_id . ')
                    AND `teams`.`deleted_at` IS NULL
            ');
            }
        }

        // Get Site assigned courses

        $sites = UsersSite::select('site_id')->where('user_id', $user->id)->get()->toArray();

        foreach ($sites as $s) {
            $siteId[] = $s['site_id'];
        }

        $siteCourses = UsersCourse::whereIn('site_id', $siteId)
            ->where(['user_id' => $user->id, 'organisation_id' => $user->last_seen_org_id])
            ->with(['marketplace'])->get();

        foreach ($siteCourses as $sc) {
            if ($sc->marketplace->marketplace_status_id == 2) {
                $siteCoursesId[] = $sc->course_id;
            }
        }

        $siteCoursesId = array_unique($siteCoursesId);

        if (!empty($siteCoursesId)) {


            $siteCoursesId = join(', ', $siteCoursesId);


            $siteAssignment = Course::hydrate(DB::select('
            SELECT 
            `courses`.*,
            ROUND(AVG(`lessons_progress_status`.`percent`)) AS completion
            FROM `courses`
                     LEFT JOIN `lessons`
                       ON `lessons`.`course_id` = `courses`.`id` AND `lessons`.`deleted_at` IS NULL
                     LEFT JOIN `users_courses_progress`
                       ON `users_courses_progress`.`user_id` = ' . $user->id . ' AND `users_courses_progress`.`lesson_id` = lessons.id
                     LEFT JOIN `lessons_progress_status`
                       ON `lessons_progress_status`.`id` = `users_courses_progress`.`progress_status_id`
            WHERE `courses`.`id` IN (' . $siteCoursesId . ')
                    AND `courses`.`deleted_at` IS NULL
            GROUP BY `courses`.`id`;
        '))->load('thumbnail')
                ->toArray();

            foreach ($siteAssignment as $k => $sa) {
                $sites = UsersCourse::select('site_id')->where(['user_id' => $user->id, 'organisation_id' => $user->last_seen_org_id, 'course_id' => $sa['id']])->get()->toArray();

                $sites_id = [];

                foreach ($sites as $s) {
                    $sites_id[] = $s['site_id'];
                }
                $sites_id = array_diff($sites_id, array(''));
                if (!empty($sites_id)) {

                    if (count(array_filter($sites_id)) > 1) {
                        $sites_id = join(', ', $sites_id);
                    } else {
                        $sites_id = join($sites_id);
                    }

                    $siteAssignment[$k]['sites'] = DB::select('
                    SELECT `sites`.* 
                    FROM `sites`
                    WHERE `sites`.`id` IN (' . $sites_id . ')
                    AND `sites`.`deleted_at` IS NULL
                    ');
                }

            }
        }

        // Get self-assigned and self-authored courses

        $courses = UsersCourse::where(['user_id' => $user->id, 'organisation_id' => $user->last_seen_org_id])
            ->with(['marketplace'])->get();

        foreach ($courses as $course) {
            $acceptedStatus = MarketplaceStatuses::where('status', 'accepted')->first()->id;
            if ($course->marketplace->marketplace_status_id == $acceptedStatus) {
                $coursesId[] = $course->course_id;
            }
        }

        $response['authoring'] = Course::where('author_id', $user->id)->get();

        $response['assigned'] = Course::whereIn('courses.id', $coursesId)
            ->select([
                'courses.*',
                DB::raw('ROUND(AVG(lessons_progress_status.percent)) as completion')
            ])
            ->join('lessons', function ($join) {
                $join->on('lessons.course_id', '=', 'courses.id');
                $join->where('lessons.deleted_at', null);
            })
            ->leftJoin('users_courses_progress', function ($join) use ($user) {
                $join->on('users_courses_progress.lesson_id', '=', 'lessons.id');
                $join->where('users_courses_progress.user_id', $user->id);
            })
            ->leftJoin('lessons_progress_status', 'lessons_progress_status.id', '=', 'users_courses_progress.progress_status_id')
            ->groupBy('courses.id')
            ->get();

        $response['managing'] = $this->managingList();

        if (!empty($teamAssignment)) {
            $response['team_assignment'] = $teamAssignment;
        }

        if (!empty($siteAssignment)) {
            $response['site_assignment'] = $siteAssignment;
        }

        return $this->_set_success($response);
    }

    public function getManagingList()
    {
        $list = $this->managingList();
        return $this->_set_success($list);
    }

    /**
     * Get Course
     *
     * @param string $subdoamin
     * @param integer $id
     *
     * @return \Illuminate\Http\Response
     */
    public function getCourse($subdoamin, $id)
    {
        $course = Course::select([
            'courses.*',
            DB::raw('ROUND(AVG(lessons_progress_status.percent)) as completion')
        ])
            ->leftJoin('lessons', function ($join) {
                $join->on('lessons.course_id', '=', 'courses.id');
                $join->where('lessons.deleted_at', null);
            })
            ->leftJoin('users_courses_progress', function ($join) {
                $join->on('users_courses_progress.lesson_id', '=', 'lessons.id');
                $join->where('users_courses_progress.user_id', Auth::user()->id);
            })
            ->leftJoin('lessons_progress_status', 'lessons_progress_status.id', '=', 'users_courses_progress.progress_status_id')
            ->where('courses.id', (int)$id)
            ->groupBy('courses.id')
            ->first();

        if (!$course) {
            return $this->_set_error(['course' => [__('course.see_error')]]);
        }

        $org_id = Auth::user()->last_seen_org_id;
        $canSee = $course->author->id == Auth::user()->id;
        $teams = $this->getManageableTeams();

        if (!$canSee && count($teams)) {
            $canSee = Marketplace::where([
                'organisation_id' => $org_id,
                'course_id' => $course->id
            ])
                ->whereIn('team_id', $teams)
                ->count();
        }

        if (!$canSee && UsersCourse::where(['course_id' => (int)$id, 'user_id' => Auth::user()->id])->count()) {
            $canSee = Marketplace::where([
                'organisation_id' => $org_id,
                'course_id' => $course->id,
                'marketplace_status_id' => MarketplaceStatuses::where('status', 'accepted')->first()->id
            ])->count();
        }

        if ($canSee && $course) {
            return $this->_set_success($course);
        }

        return $this->_set_error(['course' => [__('course.see_error')]]);
    }

    /**
     * Update Details Course
     *
     * @param CoursesUpdateRequest $request
     * @param string $subdomain
     * @param integer $id
     *
     * @return \Illuminate\Http\Response
     */
    public function updateDetails(CoursesUpdateRequest $request, $subdomain, $id)
    {
        $data = $request->only(['name', 'subtitle', 'description']);
        $course = Course::find((int)$id);
        $canEdit = $course->author_id == Auth::user()->id;

        if (!$canEdit && $course) {
            $canEdit = CoursesContributors::where(['course_id' => $course->id, 'user_id' => Auth::user()->id])->count();
        }

        if ($course && $canEdit) {
            $course->name = $data['name'];
            $course->subtitle = $data['subtitle'];
            $course->description = $data['description'];
            $course->save();


            // Update/Upload cover picture and logo
            $img_fb = $request->featured_background['src'];
            $img_thumbnail = $request->thumbnail['src'];

            if(substr_count($img_fb, 'data:image')){
                $base64_fb = explode(',', $img_fb)[1]; 
                // Get valid base64 from received string
                // [0] => "data:image/png;base64,
                // [1] => <valid base64>
                $image_fb = Image::make(base64_decode($base64_fb));
            }

            if(substr_count($img_thumbnail, 'data:image')){
                $base64_thumbnail = explode(',', $img_thumbnail)[1]; 
                // Get valid base64 from received string
                // [0] => "data:image/png;base64,
                // [1] => <valid base64>
                $image_thumbnail = Image::make(base64_decode($base64_thumbnail));
            }

            if (isset($image_thumbnail) && !is_string($image_thumbnail) && in_array($image_thumbnail->mime(), self::MIMES)) {
                $s3Path = env('AWS_S3_PROJECT_PATH', false) . "/courses/{$id}/thumbnails/";
                
                $img_name = $this->uploadFileBase64($img_thumbnail, $s3Path);

                $path = env('AWS_S3_PATH', false) . $s3Path . $img_name;
                
                if (!empty($course->thumbnail->id)) {
                    $course->thumbnail()->update(['src' => $path]);
                    $file = Files::find($course->thumbnail->id);
                    
                } else {
                    $file = Files::create([
                        'src' => $path,
                        'user_id' => Auth::user()->id
                    ]);
                    $course->thumbnail()->associate($file);
                    $course->save();
                }

                ImageThumbnailProcess::dispatch($file, $s3Path, 'background');
                
            }

            if (isset($image_fb) && !is_string($image_fb) && in_array($image_fb->mime(), self::MIMES)) {
                $s3Path = env('AWS_S3_PROJECT_PATH', false) . "/courses/{$id}/featured_background/";
                $img_name = $this->uploadFileBase64($img_fb, $s3Path);

                $path = env('AWS_S3_PATH', false) . $s3Path . $img_name;

                if (!empty($course->featured_background->id)) {
                    $course->featured_background()->update(['src' => $path]);

                    $file = Files::find($course->featured_background->id);
                } else {
                    $file = Files::create([
                        'src' => $path,
                        'user_id' => Auth::user()->id
                    ]);
                    $course->featured_background()->associate($file);
                    $course->save();
                }
                
                ImageThumbnailProcess::dispatch($file, $s3Path, 'cover');
            }

            return $this->_set_success(Course::find((int)$id));
        }

        return $this->_set_error(['course' => [__('course.update_error', ['id' => $id])]]);
    }

    /**
     * Update Details Course
     *
     * @param CoursesUpdateLessonsOrderRequest $request
     * @param string $subdomain
     * @param integer $id
     *
     * @return \Illuminate\Http\Response
     */
    public function updateLessonsOrder(CoursesUpdateLessonsOrderRequest $request, $subdomain, $id)
    {
        $course = Course::find((int)$id);
        $canEdit = $course->author_id == Auth::user()->id;

        if ($course && $canEdit) {
            $data = $request->only('order');

            foreach ($data['order'] as $lesson) {
                Lesson::where([
                    'id' => $lesson['lesson_id'],
                    'course_id' => (int)$id
                ])->update(['order' => $lesson['new_order']]);
            }

            $course = Course::where('id', (int)$id)->with(['lessons', 'author'])->first();

            return $this->_set_success($course);
        }

        return $this->_set_error(['course' => [__('course.update_error', ['id' => $id])]]);
    }

    /**
     * Create Course
     *
     * @param CoursesCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function createCourse(CoursesCreateRequest $request)
    {
        $course = new Course();
        $course->name = $request->name;
        $course->author_id = Auth::user()->id;
        $course->save();

        return $this->_set_success($course);
    }

    /**
     * Delete Course
     *
     * @param string $subdomain
     * @param integer $id
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteCourse($subdomain, $id)
    {
        $course = Course::find($id);
        if ($course && $course->author_id == Auth::user()->id) {
            foreach ($course->lessons as $lesson) {
                foreach ($lesson->media as $media) {
                    Files::find($media->file_id)->delete();
                }
            }

            $data_del = Course::find($id)->delete();
            if ($data_del) {
                return $this->_set_success([]);
            }
        }

        return $this->_set_error(['course' => [__('course.destroy_error')]]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function resetThumbnail(Request $request, $subdomain, $id)
    {
        $data_id = Course::find((int)$id);
        $canSee = $data_id->author_id == Auth::user()->id;

        if (!$canSee) {
            $canSee = CoursesContributors::where(['course_id' => (int)$data_id->id, 'user_id' => Auth::user()->id])->count();
        }

        if ($data_id && $canSee) {

            $thumb = $data_id->thumbnail()->first();
            if ($thumb) {
                $thumb->delete();
            }

            return $this->_set_success([]);
        } else {
            return $this->_set_error(['course' => [__('course.update_error', ['id' => $id])]]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function resetFeaturedBackground(Request $request, $subdomain, $id)
    {
        $data_id = Course::find((int)$id);
        $canSee = $data_id->author_id == Auth::user()->id;

        if (!$canSee) {
            $canSee = CoursesContributors::where(['course_id' => (int)$data_id->id, 'user_id' => Auth::user()->id])->count();
        }

        if ($data_id && $canSee) {

            $featured_bg = $data_id->featured_background()->first();
            if ($featured_bg) {
                $featured_bg->delete();
            }

            return $this->_set_success([]);
        } else {
            return $this->_set_error(['course' => [__('course.update_error', ['id' => $id])]]);
        }
    }

    /**
     * Get Publish Config
     *
     * @param string $subdomain
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function getPublishConfig($subdomain, $id)
    {
        $course = Course::where(['id' => $id, 'author_id' => Auth::user()->id])->first();

        if ($course) {
            $response = [];

            $organisation = Organisation::where([
                'id' => Auth::user()->last_seen_org_id
            ])->first();

            if ($organisation) {

                $response['organisation'] = $organisation;
                $response['sites'] = [];

                $user_organisation = UsersOrganisations::where([
                    'user_id' => Auth::user()->id,
                    'organisation_id' => Auth::user()->last_seen_org_id
                ])->first();

                if ((int)$user_organisation->is_admin) {
                    // user is organisation admin, pick everything
                    $response['organisation']['teams'] = Team::where([
                        'organisation_id' => Auth::user()->last_seen_org_id,
                        'site_id' => null
                    ])->with(['marketplace' => function ($query) use ($id) {
                        $query->select(['id', 'team_id', 'course_id', 'is_published', 'review_completed', 'review_message', 'marketplace_status_id']);
                        $query->where(['course_id' => $id]);
                    }])->get();

                    $response['sites'] = Site::where([
                        'organisation_id' => Auth::user()->last_seen_org_id
                    ])->with(['teams', 'teams.marketplace' => function ($query) use ($id) {
                        $query->select(['id', 'team_id', 'course_id', 'is_published', 'review_completed', 'review_message', 'marketplace_status_id']);
                        $query->where(['course_id' => $id]);
                    }])->get();


                } else {
                    // user is not organisation admin. Pick where user members in,
                    // what sites he can admin, etc
                    $response['organisation']['teams'] = Team::whereIn('id',
                        UsersTeam::select(['team_id'])->where([
                            'user_id' => Auth::user()->id
                        ])
                    )->where([
                        'site_id' => null,
                        'organisation_id' => Auth::user()->last_seen_org_id
                    ])->with(['marketplace' => function ($query) use ($id) {
                        $query->select(['id', 'team_id', 'course_id', 'is_published', 'review_completed', 'review_message', 'marketplace_status_id']);
                        $query->where(['course_id' => $id]);
                    }])->get();

                    $user_sites = UsersSite::where([
                        'user_id' => Auth::user()->id
                    ])->whereIn('site_id',
                        Site::select('id')->where([
                            'organisation_id' => Auth::user()->last_seen_org_id
                        ])->get()->makeHidden(['logo'])
                    )->get()->toArray();

                    foreach ($user_sites as $k => $us) {
                        if ((int)$us['is_admin']) {
                            // user is site admin, pick all site-related stuff
                            $response['sites'][$k] = Site::where('id', $us['site_id'])->first()->toArray();

                            $response['sites'][$k]['teams'] = Team::where([
                                'site_id' => $us['site_id'],
                                'organisation_id' => Auth::user()->last_seen_org_id
                            ])
                                ->with(['marketplace' => function ($query) use ($id) {
                                    $query->select(['id', 'team_id', 'course_id', 'is_published', 'review_completed', 'review_message', 'marketplace_status_id']);
                                    $query->where(['course_id' => $id]);
                                }])->get();

                        } else {
                            // user is not site admin, pick sites where guy is a member
                            $response['sites'][$k] = Site::where('id', $us['site_id'])->first()->toArray();
                            $response['sites'][$k]['teams'] = Team::where([
                                'site_id' => $us['site_id']
                            ])
                                ->whereIn('id',
                                    UsersTeam::select(['team_id'])->where([
                                        'user_id' => Auth::user()->id
                                    ])
                                )->with(['marketplace' => function ($query) use ($id) {
                                    $query->select(['id', 'team_id', 'course_id', 'is_published', 'review_completed', 'review_message', 'marketplace_status_id']);
                                    $query->where(['course_id' => $id]);
                                }])->get();
                        }
                        // drop site result if there is no teams
                        if (!count($response['sites'][$k]['teams'])) {
                            unset($response['sites'][$k]);
                        }
                    }

                }

                return $this->_set_success($response);
            }
        }

        return $this->_set_error(['course' => [__('course.see_error')]]);

    }

    /**
     * Get Course Grading
     *
     * @param string $subdomain
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function getGrading($subdomain, int $id)
    {
        $course = Course::with(['users'])->find($id);


        return $this->_set_success($course);
    }

    /**
     * Managing List
     *
     * @return Course
     */
    private function managingList()
    {
        $user = Auth::user();

        $courses = [];

        $user_org = UsersOrganisations::where([
            'user_id' => $user->id,
            'organisation_id' => $user->last_seen_org_id
        ])->first();

        if ((int)$user_org->is_admin) {
            // user is organisation admin, pick all related courses
            $courses = Course::whereIn('id',
                Marketplace::select('course_id')
                    ->where([
                        'organisation_id' => $user->last_seen_org_id,
                    ])->get()->makeHidden(['status'])
            )->with(['author'])->get();
        } else {
            $sites = UsersSite::select('site_id')->where([
                'user_id' => $user->id,
                'is_admin' => 1
            ])->get();

            if (count($sites)) {
                // user is an admin of some sites, pick sites related courses
                $courses = Course::whereIn('id',
                    Marketplace::select('course_id')
                        ->where([
                            'organisation_id' => $user->last_seen_org_id
                        ])
                        ->whereIn('site_id', $sites)->get()->makeHidden(['status'])
                )->with(['author'])->get();
            } else {
                $teams = UsersTeam::select('team_id')->where([
                    'user_id' => $user->id,
                    'is_admin' => 1
                ])->get();

                if (count($teams)) {
                    $courses = Course::whereIn('id',
                        Marketplace::select('course_id')
                            ->where([
                                'organisation_id' => $user->last_seen_org_id
                            ])
                            ->whereIn('team_id', $teams)->get()->makeHidden(['status'])
                    )->with(['author'])->get();
                } else {
                    $courses = [];
                }
            }
        }
        return $courses;
    }

}
