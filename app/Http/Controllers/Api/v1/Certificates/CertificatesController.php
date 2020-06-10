<?php

namespace App\Http\Controllers\API\v1\Certificates;

use App\Http\Requests\API\v1\Certificates\CertificatesCreateEmailRequest;
use App\Http\Requests\API\v1\Certificates\CertificatesCreateRequest;
use App\Http\Controllers\Controller;
use App\Models\Certificates;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\Certificate;

class CertificatesController extends Controller
{

    /**
     * Create Certificate Request
     *
     * @param  CertificatesCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CertificatesCreateRequest $request)
    {
        $certificate = $this->createCertificate($request);

        return $this->_set_success($certificate->cert_name);
    }

    /**
     * Get List of User Certificates
     *
     * @return \Illuminate\Http\Response
     */
    public function getList()
    {
        $certificates = Certificates::where([
            'user_id' => Auth::user()->id,
            'org_id' => Auth::user()->last_seen_org_id
        ])->get();

        $result = [];

        if (count($certificates)) {
            foreach ($certificates as $cert) {
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
                    ->where('courses.id', (int)$cert->course_id)
                    ->groupBy('courses.id')
                    ->with(['lessons', 'author', 'lessons.media', 'lessons.progress' => function ($query) {
                        $query->where('user_id', Auth::user()->id);
                    }])
                    ->first();

                if ($course && $course->completion == 100) {
                    $result[] = $cert;
                }
            }
        }

        return $this->_set_success($result);
    }

    /**
     * Get Certificate by Email
     *
     * @param  CertificatesCreateEmailRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function createByEmail(CertificatesCreateEmailRequest $request)
    {
        $certificate = $this->createCertificate($request);

        Mail::to([$request->email])
            ->queue(new Certificate($certificate));

        return $this->_set_success($certificate);
    }

    /**
     * Create Certificate
     *
     * @param  CertificatesCreateEmailRequest|CertificatesCreateRequest $request
     *
     * @return Certificates $certificate
     */
    private function createCertificate($request)
    {
        if (!empty($request->user_id)) {
            $user = User::find($request->user_id);
        } else {
            $user = Auth::user();
        }

        $course = Course::find($request->course_id);
        $certificate = Certificates::firstOrNew([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'org_id' => Auth::user()->organisation->id
        ]);

        $certificate->issued_user_name = $user->first_name . ' ' . $user->last_name;
        $certificate->issued_course_name = $course->name;
        $certificate->issued_course_author_name = $course->author->first_name . ' ' . $course->author->last_name;
        $certificate->issued_course_count_lessons = $course->lessons->count();
        $certificate->issued_org_name = Auth::user()->organisation->name;
        $certificate->save();

        $cert_credentials = [
            $course->id,
            $user->id,
            $certificate->id,
            Date('myd', strtotime($certificate->updated_at)),
        ];

        $certificate->cert_name = join('-', $cert_credentials);
        $certificate->save();

        return $certificate;
    }
}
