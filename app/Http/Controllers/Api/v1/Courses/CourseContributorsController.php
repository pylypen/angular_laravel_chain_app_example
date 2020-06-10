<?php

namespace App\Http\Controllers\API\v1\Courses;

use App\Http\Requests\API\v1\Courses\CoursesContributorsCreateRequest;
use App\Http\Requests\API\v1\Courses\CoursesContributorsUpdateRequest;
use App\Http\Controllers\Controller;
use App\Models\CoursesContributors;

class CourseContributorsController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		return $this->_set_success( CoursesContributors::with( [ 'course', 'user' ] )->get() );
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  CoursesContributorsCreateRequest $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function store( CoursesContributorsCreateRequest $request ) {
		$data                          = $request->only( 'course_id', 'user_id' );
		$course_contributor            = new CoursesContributors();
		$course_contributor->user_id   = $data['user_id'];
		$course_contributor->course_id = $data['course_id'];
		$course_contributor->save();
		$data = CoursesContributors::with( [ 'course', 'user' ] )->find( $course_contributor->id );
		if ( $course_contributor ) {
			return $this->_set_success( $data );
		} else {
			return $this->_set_error( [ 'course_contributor' => [__('course_contributor.store_error')] ] );
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string $subdomain
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show( $subdomain, $id ) {
		$data = CoursesContributors::with( [ 'course', 'user' ] )->find( $id );
		if ( $data ) {
			return $this->_set_success( $data );
		} else {
			return $this->_set_error( [ 'course_contributor' =>  [__('course_contributor.show_error', ['id' => $id])] ] );
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  CoursesContributorsUpdateRequest $request
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update( CoursesContributorsUpdateRequest $request, $id ) {
		$data_request       = $request->only( 'course_id', 'user_id' );
		$data_id            = CoursesContributors::find( $id );
		$data_id->course_id = $data_request['course_id'];
		$data_id->user_id   = $data_request['user_id'];
		$data_id->save();
		$data = CoursesContributors::with( [ 'course', 'user' ] )->find( $data_id->id );
		if ( $data_id ) {
			return $this->_set_success( $data );
		} else {
			return $this->_set_error( [ 'course_contributor' => [__('course_contributor.update_error')] ] );
		}


	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy( $id ) {

		$data_id = CoursesContributors::find( $id );
		if ( $data_id ) {
			$data_del = CoursesContributors::find( $id )->delete();
			if ( $data_del ) {
				return $this->_set_success( [ 'course_contributor' => [__('course_contributor.destroy')] ] );
			}
		} else {
			return $this->_set_error( [ 'course_contributor' => [__('course_contributor.destroy_error')] ] );
		}
	}
}
