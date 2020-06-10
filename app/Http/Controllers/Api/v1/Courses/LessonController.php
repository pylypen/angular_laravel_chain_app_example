<?php

namespace App\Http\Controllers\API\v1\Courses;

use App\Helpers\YoutubeHelper;
use App\Http\Requests\API\v1\Lessons\LessonsCreateRequest;
use App\Http\Requests\API\v1\Lessons\LessonsUpdateRequest;
use App\Http\Requests\API\v1\Lessons\LessonsUploadMediaRequest;
use App\Http\Requests\API\v1\Lessons\LessonUpdateProgressRequest;
use App\Http\Requests\API\v1\Lessons\LessonAddYoutubeMediaRequest;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonsProgressStatus;
use App\Models\MediaExtensions;
use App\Models\UsersCoursesProgress;
use App\Models\UsersOrganisations;
use App\Models\Marketplace;
use App\Models\UsersSite;
use App\Models\UsersTeam;
use App\Models\UsersCourse;
use App\Models\Files;
use App\Models\Media;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Traits\ArchiveTrait;
use App\Http\Traits\ArticulateTrait;

class LessonController extends Controller
{
    use ArchiveTrait;
    use ArticulateTrait;

    /**
     * Create Lesson.
     *
     * @param LessonsCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function createLesson(LessonsCreateRequest $request)
    {
        $data = $request->only(['course_id', 'name']);
        $course = Course::find((int)$data['course_id']);
        $canSee = $course->author_id == Auth::user()->id;
        $lesson = Lesson::where(['course_id' => $data['course_id'], 'name' => $data['name']])->first();

        if ($lesson) {
            return $this->_set_error(['lesson' => [__('lesson.exists_error')]]);
        }

        if (!$canSee) {
            return $this->_set_error(['lesson' => [__('lesson.store_error')]]);
        }

        $order = Lesson::where(['course_id' => $data['course_id']])->orderBy('order', 'DESC')->first();

        $lesson = new Lesson();
        $lesson->name = $data['name'];
        $lesson->course_id = $data['course_id'];
        $lesson->order = ($order) ? (int)$order->order + 1 : 1;
        $lesson->save();

        /* check if course is already assigned somewhere */
        $uc = UsersCourse::where([
            'course_id' => $data['course_id']
        ])->get();

        if (count($uc)) {
            foreach ($uc as $assignment) {

                $check = UsersCoursesProgress::where([
                    'user_id' => $assignment->user_id,
                    'course_id' => $assignment->course_id,
                    'lesson_id' => $lesson->id
                ])->get();

                if (!count($check)) {
                    UsersCoursesProgress::create([
                        'user_id' => $assignment->user_id,
                        'course_id' => $assignment->course_id,
                        'lesson_id' => $lesson->id,
                        'progress_status_id' => 1
                    ]);
                }
            }
        }

        return $this->_set_success($lesson);
    }

    /**
     * Get Lesson.
     *
     * @param  string $subdomain
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function getLesson($subdomain, $id)
    {
        $lesson = Lesson::where('id', (int)$id)->with(['media', 'progress' => function ($query) {
            $query->where('user_id', Auth::user()->id);
        }])->first();
        $course = Course::where('id', $lesson->course_id)->with(['lessons', 'author'])->first();

        if ($lesson && $course) {
            $org_id = Auth::user()->last_seen_org_id;
            $canSee = $course->author_id == Auth::user()->id;
            $inMarketplace = Marketplace::where([
                'course_id' => $course->id,
                'marketplace_status_id' => 2,
                'organisation_id' => $org_id
            ])->first();

            if ($inMarketplace) {
                if (!$canSee) {
                    $canSee = UsersCourse::where(['course_id' => $course->id, 'user_id' => Auth::user()->id])->count();
                }

                if (!$canSee) {
                    $canSee = UsersOrganisations::where([
                        'organisation_id' => $org_id,
                        'user_id' => Auth::user()->id,
                        'is_admin' => 1
                    ])->count();
                }

                if (!$canSee && $inMarketplace->site_id) {
                    $canSee = UsersSite::where([
                        'site_id' => $inMarketplace->site_id,
                        'user_id' => Auth::user()->id,
                        'is_admin' => 1
                    ])->count();
                }

                if (!$canSee && $inMarketplace->team_id) {
                    $canSee = UsersTeam::where([
                        'team_id' => $inMarketplace->team_id,
                        'user_id' => Auth::user()->id,
                        'is_admin' => 1
                    ])->count();
                }
            }

            if ($canSee) {
                return $this->_set_success($lesson);
            }
        }

        return $this->_set_error(['lesson' => [__('lesson.show_error', ['id' => $id])]]);
    }

    /**
     * Update Lesson Details
     *
     * @param LessonsUpdateRequest $request
     * @param  string $subdomain
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function updateDetails(LessonsUpdateRequest $request, $subdomain, $id)
    {
        $data = $request->only(['name', 'description', 'allow_comments']);

        $lesson = Lesson::where('id', $id)->first();
        $canEdit = $lesson->course->author_id == Auth::user()->id;

        if ($lesson && $canEdit) {
            $lesson->name = $data['name'];
            $lesson->description = $data['description'];
            $lesson->allow_comments = (int)$data['allow_comments'];
            $lesson->save();

            $response = Course::where('id', $lesson->course_id)->with(['lessons', 'author'])->first();
            return $this->_set_success($response);
        }

        return $this->_set_error([__('lesson.update_error')], 422);
    }

    /**
     * Delete Lesson
     *
     * @param  string $subdomain
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteLesson($subdomain, $id)
    {
        $lesson = Lesson::where('id', $id)->first();
        $canEdit = $lesson->course->author_id == Auth::user()->id;

        if ($lesson && $canEdit) {
            $course_id = $lesson->course_id;

            if ($lesson->media()->count()) {
                foreach ($lesson->media as $media) {
                    Files::find($media->file_id)->delete();
                }
                $lesson->media()->delete();
            }

            $lesson->delete();

            $response = Course::where('id', $course_id)->with(['lessons', 'author'])->first();

            foreach ($response->lessons as $index => $lesson) {
                $lesson->order = $index + 1;
                $lesson->save();
            }


            return $this->_set_success($response);
        }

        return $this->_set_error(['lesson' => ['Lesson ' . $id . ' was not found']], 422);
    }

    /**
     * Update Lesson Details
     *
     * @param LessonsUploadMediaRequest $request
     * @param  string $subdomain
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadMedia(LessonsUploadMediaRequest $request, $subdomain, $id)
    {
        $lesson = Lesson::where('id', $id)->first();
        $canEdit = $lesson->course->author_id == Auth::user()->id;

        if ($lesson && $canEdit) {
            $ext = MediaExtensions::where('media_extension', $request->file->getClientOriginalExtension())->first();

            if ($ext) {
                $originalName = $request->file->getClientOriginalName();

                switch ($request->file->getClientOriginalExtension()) {
                    case 'zip':
                        $pathZip = $request->file->getPathName();
                        $pathExtract = '/tmp/' . uniqid() . DIRECTORY_SEPARATOR;

                        if ($this->extractZip($pathExtract, $pathZip)) {
                            $articulate = $this->listArticulateByDir($pathExtract);

                            if ($articulate) {
                                $clearName = $this->cleanArticulateName($articulate['name']);
                                foreach ($articulate['list'] as $file) {

                                    $aPath = explode(DIRECTORY_SEPARATOR, $file);
                                    $aPathName = $aPath[count($aPath) - 1];
                                    $aPath[count($aPath) - 1] = '';
                                    $aPath = implode(DIRECTORY_SEPARATOR, $aPath);
                                    $aPath = explode($articulate['separator'], $aPath);

                                    if (count($aPath) > 1) {
                                        Storage::disk('s3')->putFileAs(
                                            env('AWS_S3_PROJECT_PATH', false) . "/lesson/{$id}/articulates/{$clearName}/{$aPath[1]}",
                                            new File($file),
                                            $aPathName,
                                            ['visibility' => 'public']
                                        );
                                    }
                                }

                                $repeatedMedia = Media::where([
                                    'lesson_id' => $lesson->id,
                                    'name' => $articulate['name']
                                ])->count();

                                if (!$repeatedMedia) {
                                    $path = env('AWS_S3_PATH', false)
                                        . env('AWS_S3_PROJECT_PATH', false)
                                        . "/lesson/{$id}/articulates/{$clearName}/{$this->articulateIndexFile}";

                                    $file = Files::create([
                                        'src' => $path,
                                        'user_id' => Auth::user()->id
                                    ]);

                                    Media::create([
                                        'lesson_id' => $lesson->id,
                                        'file_id' => $file->id,
                                        'media_extension_id' => $ext->id,
                                        'name' => $articulate['name']
                                    ]);
                                }
                            } else {
                                Log::warning('Articulate Validation Error.', [
                                    'Exception' => __('lesson.articulate_ext'),
                                    'user_id' => Auth::user()->id
                                ]);
                                return $this->_set_error(['file' => [__('lesson.articulate_ext')]], 422);
                            }
                        }
                        self::delExtractedArchive($pathExtract);
                        break;
                    default:
                        $originalName = $this->cleanArticulateName($originalName);

                        $path = env('AWS_S3_PATH', false) . $request->file('file')
                                ->storePubliclyAs(env('AWS_S3_PROJECT_PATH', false) . "/lesson/{$id}/media", $originalName, 's3');

                        $repeatedMedia = Media::where([
                            'lesson_id' => $lesson->id,
                            'name' => $originalName
                        ])->count();

                        if (!$repeatedMedia) {
                            $file = Files::create([
                                'src' => $path,
                                'user_id' => Auth::user()->id
                            ]);

                            Media::create([
                                'lesson_id' => $lesson->id,
                                'file_id' => $file->id,
                                'media_extension_id' => $ext->id,
                                'name' => $originalName
                            ]);
                        }
                        break;
                }

                UsersCoursesProgress::where(['lesson_id' => $lesson->id])
                    ->update(['progress_status_id' => 1]);

                return $this->_set_success([]);
            }

            return $this->_set_error(['file' => [__('lesson.ext_error')]], 422);
        }

        return $this->_set_error(['file' => [__('lesson.update_error')]], 422);
    }

    /**
     * Add Youtube Media
     *
     * @param LessonAddYoutubeMediaRequest $request
     * @param  string $subdomain
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function addYoutubeMedia(LessonAddYoutubeMediaRequest $request, $subdomain, $id)
    {
        $lesson = Lesson::where('id', $id)->first();
        $canEdit = $lesson->course->author_id == Auth::user()->id;

        $data = $request->only(['src']);

        $src = YoutubeHelper::getYoutubeUrl() . YoutubeHelper::getVideoId($data['src']);

        if ($lesson && $canEdit) {
            $file = Files::firstOrNew([
                'user_id' => Auth::user()->id,
                'src' => $src
            ]);
            $file->save();

            $me = MediaExtensions::where('media_extension', 'youtube')->first();

            $media = Media::firstOrNew([
                'lesson_id' => $lesson->id,
                'file_id' => $file->id,
                'media_extension_id' => $me->id
            ]);

            $media->name = YoutubeHelper::getYoutubeTitle($data['src']);
            $media->save();

            $list = Media::where([
                'lesson_id' => $lesson->id,
                'media_extension_id' => $me->id
            ])->get();

            UsersCoursesProgress::where(['lesson_id' => $lesson->id])
                ->update(['progress_status_id' => 1]);

            return $this->_set_success($list);
        }

        return $this->_set_error(['src' => [__('lesson.update_error')]], 422);
    }

    /**
     * Delete Youtube Media
     *
     * @param  string $subdomain
     * @param  int $media_id
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteYoutubeMedia($subdomain, $media_id)
    {
        $media = Media::where('id', $media_id)->first();
        if ($media) {
            $lesson = Lesson::where('id', $media->lesson_id)->first();
            $canEdit = $lesson->course->author_id == Auth::user()->id;

            if ($lesson && $canEdit) {

                $file_id = $media->file_id;

                $media->delete();

                $check = Media::where(['file_id' => $file_id])->get();
                if (empty($check)) {
                    Files::where('id', $file_id)->delete();
                }

                $me = MediaExtensions::where('media_extension', 'youtube')->first();

                $list = Media::where([
                    'lesson_id' => $lesson->id,
                    'media_extension_id' => $me->id
                ])->get();

                return $this->_set_success($list);
            }
        }

        return $this->_set_error(['src' => [__('lesson.update_error')]], 422);

    }

    /**
     * Delete Lesson Media
     *
     * @param  string $subdomain
     * @param  int $media_id
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteMedia($subdomain, $media_id)
    {
        $media = Media::where('id', $media_id)->first();

        if ($media) {
            $lesson = Lesson::where('id', $media->lesson_id)->first();
            $canEdit = $lesson->course->author_id == Auth::user()->id;

            if ($lesson && $canEdit) {
                switch ($media->media_extension->media_extension) {
                    case 'zip':
                        $file_id = $media->file_id;
                        $file = Files::find($file_id);

                        if ($file) {
                            $fileSrc = $file->src;
                            $dirName = explode("/lesson/" . $lesson->id . "/articulates/", $fileSrc);

                            if (count($dirName) > 1) {
                                $dirName = explode("/", $dirName[1]);
                                $path = env('AWS_S3_PROJECT_PATH', false) . "/lesson/{$lesson->id}/articulates/{$dirName[0]}";

                                Storage::disk('s3')->deleteDirectory($path);
                                $media->delete();

                                $check = Media::where(['file_id' => $file_id])->count();

                                if (empty($check)) {
                                    $file->delete();
                                }
                            }
                        }

                        break;
                    default:
                        Storage::disk('s3')->delete(
                            env('AWS_S3_PROJECT_PATH', false)
                            . "/lesson/" . $lesson->id . "/media/" . $media->name
                        );

                        $file_id = $media->file_id;

                        $media->delete();

                        $check = Media::where(['file_id' => $file_id])->count();

                        if (empty($check)) {
                            Files::where('id', $file_id)->delete();
                        }

                        break;
                }

                return $this->_set_success([]);
            }
        }

        return $this->_set_error(['src' => [__('lesson.update_error')]], 422);
    }

    /**
     * Update Lesson Progress
     *
     * @param LessonUpdateProgressRequest $request
     * @param  string $subdomain
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function updateLessonProgress(LessonUpdateProgressRequest $request, $subdomain, $id)
    {
        $data = $request->only(['progress_status_id']);

        $lesson = Lesson::where('id', $id)->first();

        if ($lesson) {

            $assignment = UsersCourse::where([
                'user_id' => Auth::user()->id,
                'course_id' => $lesson->course_id
            ])->count();

            if ($assignment) {

                UsersCoursesProgress::where([
                    'user_id' => Auth::user()->id,
                    'course_id' => $lesson->course_id,
                    'lesson_id' => $lesson->id
                ])->delete();

                $lesson_progress = new UsersCoursesProgress();
                $lesson_progress->user_id = Auth::user()->id;
                $lesson_progress->course_id = $lesson->course_id;
                $lesson_progress->lesson_id = $lesson->id;
                $lesson_progress->progress_status_id = $data['progress_status_id'];
                $lesson_progress->save();

                $status = LessonsProgressStatus::where('id', $data['progress_status_id'])->first();

                return $this->_set_success($status);
            }
        }

        return $this->_set_error(['lesson' => [__('lesson.update_error')]], 422);
    }
}
