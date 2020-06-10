<?php

namespace App\Http\Controllers\Api\v1\Courses;

use App\Http\Controllers\Controller;
use App\Models\LessonContentOrder;
use App\Http\Requests\API\v1\LessonContentOrder\LessonContentOrderRequest;
use App\Models\Media;
use App\Models\MediaTypes;
use Illuminate\Http\Request;

class LessonMediaOrderController extends Controller
{
    /**
     * Get Media Types List
     *
     * @param string $subdoamin
     * @param integer $lesson_id
     *
     * @return \Illuminate\Http\Response
     */
    public function media_types_list($subdoamin, $lesson_id)
    {
        $response = [
            'uses' => [],
            'library' => []
        ];

        $uses_id = array();
        $library_id = array();
        $uses_array = array();

        $media = LessonContentOrder::select('media_type_id')->where('lesson_id', $lesson_id)->get()->toArray();
        foreach ($media as $m) {
            $uses_id[] = $m['media_type_id'];
        }

        $media_types = MediaTypes::select('id')->get()->toArray();

        foreach ($media_types as $mt) {
            $library_id[] = $mt['id'];
        }

        $library_id = array_diff($library_id, $uses_id);

        if (!empty($uses_id)) {
            $response['uses'] = MediaTypes::whereIn('media_types.id', $uses_id)
            ->select('media_types.id', 'media_types.name', 'lesson_content_order.order')
                ->leftJoin('lesson_content_order', function ($join) use ($lesson_id) {
                    $join->on('lesson_content_order.media_type_id', '=', 'media_types.id');
                    $join->where('lesson_content_order.lesson_id', $lesson_id);
                })->orderBy('lesson_content_order.order')->get()->toArray();
        }

        if (!empty($library_id)) {
            $response['library'] = MediaTypes::select('id', 'name')->whereIn('id', $library_id)->get()->toArray();
        }

        return $this->_set_success($response);
    }

    /**
     * Save media order
     *
     * @param LessonContentOrderRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function save_media_order(LessonContentOrderRequest $request)
    {
        $data = $request->all();
        if (empty($data)) {
            return $this->_set_error(['order' => [__('order.save_error')]]);
        }
        $lesson_id = $data['lesson_id'];

        $delete = LessonContentOrder::select('id')->where('lesson_id', $lesson_id)->get()->toArray();
        if (!empty($delete)) {
            $deletedRows = LessonContentOrder::where('lesson_id', $lesson_id)->delete();
        }

        if (!empty($data)) {
            foreach ($data['uses'] as $key => $uses) {
                $model = new LessonContentOrder();
                $model->lesson_id = $lesson_id;
                $model->media_type_id = $uses['id'];
                $model->order = $key;
                $model->save();
            }
        }

        $order = LessonContentOrder::select('id')->where('lesson_id', $lesson_id)->get()->toArray();

        if ($order) {
            return $this->_set_success(['lesson_content_order' => 'Save successfully']);
        } else {
            return $this->_set_error(['lesson_content_order' => 'Error when saving lesson content order']);
        }

    }

}
