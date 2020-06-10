<?php

namespace App\Http\Controllers\API\v1\Courses;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\API\v1\Lessons\LessonCommentCreateRequest;
use App\Http\Requests\API\v1\Lessons\LessonCommentUpdateRequest;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\UsersCourse;
use App\Models\LessonComments;
use App\Models\Course;
use Illuminate\Support\Facades\Mail;
use App\Mail\LessonCommentMention;

class LessonCommentsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  LessonCommentCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function create(LessonCommentCreateRequest $request)
    {
        $lesson = Lesson::find($request->lesson_id);

        $uc = UsersCourse::where([
                'user_id' => Auth::user()->id,
                'course_id' => $lesson->course->id]
        )->count();

        if ($uc && (int)$lesson->allow_comments) {
            $LessonComments = new LessonComments();
            $LessonComments->lesson_id = $request->lesson_id;
            $LessonComments->user_id = Auth::user()->id;
            $LessonComments->comment = $request->comment;
            $LessonComments->save();

            $this->checkUserNickname($LessonComments);

            $LessonComments->load(['user']);

            return $this->_set_success($LessonComments);
        }

        return $this->_set_error(['lesson' => [__('lesson_comment.store_comment_error')]], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param  string $subdomain
     * @param  int $lesson_id
     *
     * @return \Illuminate\Http\Response
     */
    public function list($subdomain, $lesson_id)
    {
        $lesson = Lesson::find((int)$lesson_id);
        $course = Course::where('id', $lesson->course_id)->with(['author'])->first();

        if ($lesson && (int)$lesson->allow_comments) {
            $lessonComment = LessonComments::where('lesson_id', $lesson->id)->get();
            $canSee = $course->author_id == Auth::user()->id;

            if (!$canSee) {
                $canSee = UsersCourse::where([
                    'course_id' => $course->id,
                    'user_id' => Auth::user()->id
                ])->count();
            }

            if ($lessonComment && $canSee) {
                $response = array();
                $response['comments'] = $lessonComment;
                $response['mention_list'] = User::whereIn('id',
                    UsersCourse::select(['user_id'])->where([
                        'course_id' => $course->id
                    ])->get()
                )->where('id', '!=', Auth::user()->id)
                    ->get();

                return $this->_set_success($response);
            }
        }

        return $this->_set_error(['lesson_comment' => [__('lesson.show_comment_error', ['id' => $lesson_id])]], 422);
    }

    /**
     * Update Lesson Comment
     *
     * @param  LessonCommentUpdateRequest $request
     * @param  string $subdomain
     *
     * @return \Illuminate\Http\Response
     */
    public function update(LessonCommentUpdateRequest $request, $subdomain)
    {
        $data = $request->only(['id', 'lesson_id', 'comment']);

        $lesson_comment = LessonComments::where([
            'id' => $data['id'],
            'lesson_id' => $data['lesson_id'],
            'user_id' => Auth::user()->id
        ])->first();

        if ($lesson_comment) {
            $lesson_comment->comment = $data['comment'];
            $lesson_comment->save();

            $comments = LessonComments::where('lesson_id', $data['lesson_id'])->get();

            $response['comments'] = $comments;
            return $this->_set_success($response);

        }

        return $this->_set_error(['lesson' => [__('lesson_comment.store_comment_error')]], 422);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function delete($subdomain, $id)
    {
        $lessonComment = LessonComments::find((int)$id);

        if ($lessonComment) {
            if ($lessonComment->user_id == Auth::user()->id) {
                $lesson_id = $lessonComment->lesson_id;
                $lessonComment->delete();

                $comments = LessonComments::where('lesson_id', $lesson_id)->get();

                $response['comments'] = $comments;
                return $this->_set_success($response);
            }
        }

        return $this->_set_error(['lesson_comment' => [__('lesson.destroy_comment_error')]], 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  LessonComments $lessonComment
     *
     * @return void
     */
    private function checkUserNickname(LessonComments $lessonComment)
    {
        $comment = explode('@', $lessonComment->comment);

        if (count($comment) > 1) {
            foreach ($comment as $key=>$item) {
                if ($key == 0) {
                    continue;
                }

                $nickname = explode(' ', $item)[0];
                $user = User::where('nickname', $nickname)->first();

                if ($user && $user->id != Auth::user()->id) {
                    $uc = UsersCourse::where([
                        'course_id' => $lessonComment->lesson->course->id,
                        'user_id' => $user->id
                    ])->count();

                    if ($uc) {
                        Mail::to($user->email)
                            ->queue(new LessonCommentMention($user, $lessonComment));
                    }

                }
            }
        }
    }
}
